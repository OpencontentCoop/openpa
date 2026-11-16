<?php
/**
 * Test E2E: workflow event_reindexrelations + cron index_pending.php
 *
 * Caso reale (ticket Freshdesk #32262, tenant PAT): il flusso notturno rinomina una struttura
 * (organization) ma i servizi (public_service) che la citano in holds_role_in_time restano nell'indice
 * Solr / nell'API opendata con il vecchio nome.
 *
 * Il test collega TEMPORANEAMENTE l'evento al workflow "Post pubblicazione" (lo rimuove alla fine) e verifica:
 *  1. un publish senza cambio di nome non accoda nulla
 *  2. la rinomina accoda i soli servizi che citano la struttura, una riga per oggetto
 *  3. prima del cron l'indice Solr ha ancora il vecchio nome (il bug), dopo il cron il nuovo
 *  4. nessun duplicato in coda (rinomina ripetuta) e nessuna ricorsione (il cron non accoda nulla)
 *  5. tetto MaxRelatedObjects, filtro per classe, attributi tracciati (Attributes[]), scadenza delle richieste vecchie
 *  6. se Solr non accetta gli oggetti le righe restano in coda e il lotto si ferma
 *  7. righe nel vecchio formato (piu' id con "-") consumate a pezzi; id inesistenti scartati
 *  8. misura del peso: vecchio approccio (carica gli oggetti) vs nuovo (soli id)
 *
 * Run:
 *   docker exec sito-comunale-dev-app-1 sh -c \
 *     'OUT=$(php /var/www/html/extension/openpa/tests/test_reindexrelations.php 2>&1); echo "$OUT"'
 */

$ezRoot = '/var/www/html';
chdir($ezRoot);
require_once $ezRoot . '/autoload.php';

$script = eZScript::instance([
    'description'    => 'OpenPA reindexrelations test',
    'use-session'    => false,
    'use-modules'    => true,
    'use-extensions' => true,
]);
$script->startup();
$script->initialize();

$PASSED = 0;
$FAILED = 0;

function assert_true(bool $condition, string $label): void
{
    global $PASSED, $FAILED;
    if ($condition) { echo "\033[32m[PASS]\033[0m $label\n"; $PASSED++; }
    else            { echo "\033[31m[FAIL]\033[0m $label\n"; $FAILED++; }
}

$db = eZDB::instance();
$KEY = OpenPABase::PENDING_ACTION_INDEX_OBJECTS;
$createdObjectIds = [];
$eventIds = [];
$extraLinks = [];
$ini = eZINI::instance('openpa.ini');

function cleanup(array $objectIds, array $eventIds, array $links): void
{
    $db = eZDB::instance();
    echo "\nCleanup: rimuovo " . count($objectIds) . " oggetti, " . count($eventIds) . " eventi workflow, " . count($links) . " link di prova...\n";
    foreach ($eventIds as $id) {
        $db->query("DELETE FROM ezworkflow_event WHERE id = " . (int)$id);
    }
    foreach ($links as $link) {
        $db->query(
            "DELETE FROM ezcontentobject_link WHERE from_contentobject_id = " . (int)$link[0] .
            " AND to_contentobject_id = " . (int)$link[1] . " AND relation_type = 1 AND contentclassattribute_id = 0"
        );
    }
    foreach ($objectIds as $id) {
        $object = eZContentObject::fetch($id);
        if ($object instanceof eZContentObject) { $object->purge(); }
    }
    if (!empty($objectIds)) {
        $db->query("DELETE FROM ezpending_actions WHERE action = '" . OpenPABase::PENDING_ACTION_INDEX_OBJECTS . "' AND param IN ('" . implode("','", array_map('intval', $objectIds)) . "')");
    }
    echo "Rimossi.\n";
}
register_shutdown_function(function () use (&$createdObjectIds, &$eventIds, &$extraLinks) { cleanup($createdObjectIds, $eventIds, $extraLinks); });

// ── Pre-condizioni ──────────────────────────────────────────────────────────

foreach (['organization', 'public_service'] as $identifier) {
    if (!eZContentClass::fetchByIdentifier($identifier)) {
        echo "\033[33m[SKIP]\033[0m Content type $identifier non trovato\n";
        $script->shutdown(0);
        exit(0);
    }
}
$solr = eZSearch::getEngine();
if (!($solr instanceof eZSolr)) {
    echo "\033[33m[SKIP]\033[0m Il motore di ricerca non e' eZSolr\n";
    $script->shutdown(0);
    exit(0);
}
$wf = $db->arrayQuery("SELECT id FROM ezworkflow WHERE name = 'Post pubblicazione' AND version = 0");
if (empty($wf)) {
    echo "\033[33m[SKIP]\033[0m Workflow 'Post pubblicazione' non trovato\n";
    $script->shutdown(0);
    exit(0);
}
$workflowId = (int)$wf[0]['id'];

$adminUser = eZUser::fetchByName('admin');
assert_true($adminUser instanceof eZUser, "Precondizione: utente 'admin' esiste");
$ownerId = $adminUser->attribute('contentobject_id');
eZUser::setCurrentlyLoggedInUser($adminUser, $ownerId, eZUser::NO_SESSION_REGENERATE);

$parentOf = function (string $classIdentifier) use ($db): int {
    $rows = $db->arrayQuery(
        "SELECT DISTINCT n.parent_node_id FROM ezcontentobject_tree n " .
        "JOIN ezcontentobject o ON o.id = n.contentobject_id " .
        "JOIN ezcontentclass c ON c.id = o.contentclass_id " .
        "WHERE c.identifier = '" . $db->escapeString($classIdentifier) . "' LIMIT 1"
    );
    return !empty($rows) ? (int)$rows[0]['parent_node_id'] : 2;
};
$orgParent = $parentOf('organization');
$svcParent = $parentOf('public_service');
$uniqueSuffix = date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 6);

// ── Helper ───────────────────────────────────────────────────────────────────

function createObj(string $class, int $parent, int $owner, array $attributes, array &$created): eZContentObject
{
    $object = eZContentFunctions::createAndPublishObject([
        'creator_id'       => $owner,
        'class_identifier' => $class,
        'parent_node_id'   => $parent,
        'attributes'       => $attributes,
    ]);
    if (!$object instanceof eZContentObject) { throw new Exception("Creazione $class fallita"); }
    $created[] = (int)$object->attribute('id');

    return $object;
}

function renameObj(eZContentObject $object, string $name): void
{
    // organization prende il nome da legal_name
    eZContentFunctions::updateAndPublishObject($object, ['attributes' => ['legal_name' => $name]]);
}

function editObj(eZContentObject $object, array $attributes): void
{
    eZContentFunctions::updateAndPublishObject($object, ['attributes' => $attributes]);
}

function pendingIds(): array
{
    $rows = eZDB::instance()->arrayQuery("SELECT param FROM ezpending_actions WHERE action = '" . OpenPABase::PENDING_ACTION_INDEX_OBJECTS . "' ORDER BY id");
    return array_map(function ($r) { return $r['param']; }, $rows);
}

function pendingCount(): int
{
    $rows = eZDB::instance()->arrayQuery("SELECT COUNT(*) AS c FROM ezpending_actions WHERE action = '" . OpenPABase::PENDING_ACTION_INDEX_OBJECTS . "'");
    return (int)$rows[0]['c'];
}

function clearPending(): void
{
    eZDB::instance()->query("DELETE FROM ezpending_actions WHERE action = '" . OpenPABase::PENDING_ACTION_INDEX_OBJECTS . "'");
}

/** Nome della struttura come lo vede l'API opendata dal documento Solr del servizio. */
function solrEmbeddedOrgName(int $serviceId, int $orgId)
{
    $gateway = new \Opencontent\Opendata\Api\Gateway\SolrStorage();
    for ($try = 0; $try < 10; $try++) {
        try {
            $content = $gateway->loadContent($serviceId);
            $field = $content->data['ita-IT']['holds_role_in_time'];
            $relations = (is_array($field) && isset($field['content'])) ? $field['content'] : $field;
            foreach ((array)$relations as $rel) {
                if ((int)$rel['id'] === $orgId) { return $rel['name']['ita-IT']; }
            }
            return null;
        } catch (\Throwable $e) {
            usleep(500000);
        }
    }
    return null;
}

function runCron(): void
{
    global $script;
    $cli = eZCLI::instance();
    $isQuiet = true;
    include '/var/www/html/extension/openpa/cronjobs/index_pending.php';
}

// ── Collega l'evento al workflow (rimosso nel cleanup) ───────────────────────

$event = eZWorkflowEvent::create($workflowId, 'event_reindexrelations');
$event->setAttribute('placement', 99);
$event->setAttribute('version', 0); // create() lo crea come bozza (versione 1): il workflow pubblicato usa la versione 0
$event->store();
$eventIds[] = (int)$event->attribute('id');
clearPending();
$baseline = pendingCount();

// ── Setup: 1 struttura, 3 servizi che la citano, 1 servizio che non la cita ──

$oldName = 'Test Struttura Alfa ' . $uniqueSuffix;
$org = createObj('organization', $orgParent, $ownerId, ['legal_name' => $oldName], $createdObjectIds);
$orgId = (int)$org->attribute('id');
$services = [];
for ($i = 1; $i <= 3; $i++) {
    $services[$i] = createObj('public_service', $svcParent, $ownerId, ['name' => "Test Servizio $i $uniqueSuffix", 'holds_role_in_time' => (string)$orgId], $createdObjectIds);
}
$unrelated = createObj('public_service', $svcParent, $ownerId, ['name' => "Test Servizio libero $uniqueSuffix"], $createdObjectIds);
$svcIds = array_map(function ($s) { return (int)$s->attribute('id'); }, $services);

$linkRows = $db->arrayQuery(
    "SELECT COUNT(DISTINCT l.from_contentobject_id) AS c FROM ezcontentobject_link l " .
    "INNER JOIN ezcontentobject o ON o.id = l.from_contentobject_id AND o.current_version = l.from_contentobject_version " .
    "WHERE l.to_contentobject_id = $orgId AND l.from_contentobject_id IN (" . implode(',', $svcIds) . ")"
);
assert_true((int)$linkRows[0]['c'] === 3, "Setup: i 3 servizi citano la struttura nella tabella dei link (" . (int)$linkRows[0]['c'] . ")");
assert_true(pendingCount() === $baseline, 'Setup: creare oggetti nuovi non accoda nulla (versione 1)');
assert_true(solrEmbeddedOrgName($svcIds[1], $orgId) === $oldName, "Setup: l'indice Solr del servizio contiene il nome della struttura");

// ── 1. Publish senza cambio di nome ──────────────────────────────────────────

renameObj(eZContentObject::fetch($orgId), $oldName);
assert_true(pendingCount() === $baseline, '1. Publish senza cambio di nome: niente in coda');

// ── 2-3. Rinomina ────────────────────────────────────────────────────────────

$newName = 'Test Struttura Beta ' . $uniqueSuffix;
renameObj(eZContentObject::fetch($orgId), $newName);
$pending = pendingIds();
sort($pending);
$expected = array_map('strval', $svcIds);
sort($expected);
assert_true($pending == $expected, '2. Rinomina: in coda esattamente i 3 servizi collegati, una riga ciascuno (' . implode(',', $pending) . ')');
assert_true(!in_array((string)$unrelated->attribute('id'), $pending, true), '2. Il servizio non collegato non e\' in coda');
assert_true(solrEmbeddedOrgName($svcIds[1], $orgId) === $oldName, '3. PRIMA del cron: Solr ha ancora il vecchio nome (e\' il bug del ticket)');

// ── 4. Dedup: seconda rinomina prima del cron ────────────────────────────────

$newName2 = 'Test Struttura Gamma ' . $uniqueSuffix;
renameObj(eZContentObject::fetch($orgId), $newName2);
assert_true(pendingCount() === 3, '4. Seconda rinomina prima del cron: ancora 3 righe, nessun duplicato');

// ── 5. Cron: reindex e nessuna ricorsione ────────────────────────────────────

runCron();
assert_true(pendingCount() === 0, '5. Dopo il cron la coda e\' vuota (nessuna ricorsione: il cron non accoda nulla)');
foreach ($svcIds as $n => $sid) {
    assert_true(solrEmbeddedOrgName($sid, $orgId) === $newName2, "5. Dopo il cron: Solr del servizio #$sid ha il nome nuovo");
}

// ── 6. Tetto MaxRelatedObjects ───────────────────────────────────────────────

$truncated = false;
$ids = OpenPAReindexRelations::reverseRelatedIDs($orgId, 2, $truncated);
assert_true(count($ids) === 2 && $truncated === true, '6. reverseRelatedIDs con limite 2 su 3 correlati: 2 id e flag troncato');
$ids = OpenPAReindexRelations::reverseRelatedIDs($orgId, 3, $truncated);
assert_true(count($ids) === 3 && $truncated === false, '6. reverseRelatedIDs con limite uguale ai correlati: non troncato');
$ini->setVariable('ReindexRelations', 'MaxRelatedObjects', '2');
renameObj(eZContentObject::fetch($orgId), 'Test Struttura Delta ' . $uniqueSuffix);
assert_true(pendingCount() === 2, '6. Con MaxRelatedObjects=2 la rinomina accoda solo 2 oggetti');
$ini->setVariable('ReindexRelations', 'MaxRelatedObjects', '2000');
clearPending();

// ── 7. Filtro per classe ─────────────────────────────────────────────────────

$ini->setVariable('ReindexRelations', 'Classes', ['public_service']);
renameObj(eZContentObject::fetch($orgId), 'Test Struttura Epsilon ' . $uniqueSuffix);
assert_true(pendingCount() === 0, '7. Con Classes=public_service la rinomina di una organization non accoda nulla');
$ini->setVariable('ReindexRelations', 'Classes', ['organization']);
renameObj(eZContentObject::fetch($orgId), 'Test Struttura Zeta ' . $uniqueSuffix);
assert_true(pendingCount() === 3, '7. Con Classes=organization la rinomina accoda i servizi');
$ini->setVariable('ReindexRelations', 'Classes', []);
clearPending();

// ── 7b. Attributi tracciati (Attributes[]) ───────────────────────────────────

editObj(eZContentObject::fetch($orgId), ['alt_name' => 'Sigla 1 ' . $uniqueSuffix]);
assert_true(pendingCount() === 0, '7b. Attributes[] vuoto: la modifica di un attributo diverso dal nome non accoda nulla');
$ini->setVariable('ReindexRelations', 'Attributes', ['alt_name', 'abstract']);
editObj(eZContentObject::fetch($orgId), ['identifier' => 'ID-' . $uniqueSuffix]);
assert_true(pendingCount() === 0, '7b. Attributo NON elencato (identifier): non accoda nulla');
editObj(eZContentObject::fetch($orgId), ['alt_name' => 'Sigla 2 ' . $uniqueSuffix]);
assert_true(pendingCount() === 3, '7b. Attributo elencato (alt_name, testo) modificato: accoda i 3 servizi (' . pendingCount() . ')');
clearPending();
editObj(eZContentObject::fetch($orgId), ['alt_name' => 'Sigla 2 ' . $uniqueSuffix]);
assert_true(pendingCount() === 0, '7b. Attributo elencato ripubblicato con lo stesso valore: non accoda nulla');
editObj(eZContentObject::fetch($orgId), ['abstract' => 'Descrizione di prova ' . $uniqueSuffix]);
assert_true(pendingCount() === 3, '7b. Attributo elencato di tipo XML (abstract) modificato: accoda i 3 servizi (' . pendingCount() . ')');
clearPending();
$ini->setVariable('ReindexRelations', 'Attributes', ['attributo_inesistente']);
editObj(eZContentObject::fetch($orgId), ['alt_name' => 'Sigla 3 ' . $uniqueSuffix]);
assert_true(pendingCount() === 0, '7b. Identificatore inesistente nella classe: ignorato, nessun errore');
$ini->setVariable('ReindexRelations', 'Attributes', []);
clearPending();

// ── 8. Solr non accetta gli oggetti: righe in coda, lotto interrotto ─────────

OpenPAReindexRelations::queue(array_merge($svcIds, [(int)$unrelated->attribute('id')]));
$failing = function ($id) { return false; };
$stats = OpenPAReindexRelations::processPending($failing, 100, 7);
assert_true($stats['failed'] === OpenPAReindexRelations::MAX_CONSECUTIVE_FAILURES && $stats['stopped'] === true, '8. Con Solr in errore il lotto si ferma dopo ' . OpenPAReindexRelations::MAX_CONSECUTIVE_FAILURES . ' fallimenti di fila');
assert_true(pendingCount() === 4, '8. Le righe fallite restano TUTTE in coda (nessuna perdita)');
$working = function ($id) { return true; };
$stats = OpenPAReindexRelations::processPending($working, 100, 7);
assert_true($stats['indexed'] === 4 && pendingCount() === 0, '8. Ripristinato Solr, il giro successivo svuota la coda');

// ── 9. Righe vecchio formato, budget, id inesistenti, scadenza ───────────────

$db->query("INSERT INTO ezpending_actions (action, created, param) VALUES ('$KEY', " . time() . ", '" . implode('-', $svcIds) . "')");
$seen = [];
$collect = function ($id) use (&$seen) { $seen[] = $id; return true; };
$stats = OpenPAReindexRelations::processPending($collect, 2, 7);
$rest = pendingIds();
assert_true($stats['indexed'] === 2 && count($rest) === 1 && substr_count($rest[0], '-') === 0, '9. Riga vecchio formato con budget 2: ne elabora 2 e lascia l\'ultimo id nella riga');
OpenPAReindexRelations::processPending($collect, 2, 7);
assert_true(pendingCount() === 0 && count($seen) === 3, '9. Il resto della riga vecchio formato viene consumato al giro dopo');

OpenPAReindexRelations::queue([99999991, 99999992]);
runCron();
assert_true(pendingCount() === 0, '9. Id inesistenti: scartati dal cron (non restano in coda per sempre)');

$db->query("INSERT INTO ezpending_actions (action, created, param) VALUES ('$KEY', " . (time() - 9 * 86400) . ", '" . $svcIds[1] . "')");
$called = 0;
$counter = function ($id) use (&$called) { $called++; return true; };
$stats = OpenPAReindexRelations::processPending($counter, 100, 7);
assert_true($stats['expired'] === 1 && $called === 0 && pendingCount() === 0, '9. Richiesta piu\' vecchia di 7 giorni: scartata senza reindicizzare');

// ── 10. Peso: vecchio approccio (carica oggetti) vs nuovo (soli id) ──────────

$candidates = $db->arrayQuery(
    "SELECT id, current_version FROM ezcontentobject WHERE status = 1 AND id <> $orgId ORDER BY id",
    ['offset' => 0, 'limit' => 800]
);
foreach ($candidates as $c) {
    $db->query(
        "INSERT INTO ezcontentobject_link (from_contentobject_id, from_contentobject_version, to_contentobject_id, contentclassattribute_id, relation_type) VALUES (" .
        (int)$c['id'] . ", " . (int)$c['current_version'] . ", $orgId, 0, 1)"
    );
    $extraLinks[] = [(int)$c['id'], $orgId];
}
$n = count($candidates);
echo "\n  Misura con $n oggetti che citano la struttura:\n";

eZContentObject::clearCache();
$orgObject = eZContentObject::fetch($orgId);
$mem0 = memory_get_usage();
$t0 = microtime(true);
$old = $orgObject->reverseRelatedObjectList(false, false, 0, false, ['AllRelations' => true]);
$tOld = (microtime(true) - $t0) * 1000;
$memOld = (memory_get_peak_usage() - $mem0) / 1048576;
$oldIds = [];
foreach ($old as $o) { $oldIds[(int)$o->attribute('id')] = true; }
$oldRows = count($old);
$oldCount = count($oldIds);
unset($old);

$mem1 = memory_get_usage();
$t1 = microtime(true);
$newIds = OpenPAReindexRelations::reverseRelatedIDs($orgId, 2000);
$tNew = (microtime(true) - $t1) * 1000;
$t2 = microtime(true);
$queued = OpenPAReindexRelations::queue($newIds);
$tQueue = (microtime(true) - $t2) * 1000;
$t3 = microtime(true);
OpenPAReindexRelations::queue($newIds);
$tQueueAgain = (microtime(true) - $t3) * 1000;

echo sprintf("  vecchio (oggetti completi): %d righe (%d oggetti distinti), %.0f ms, ~%.1f MB\n", $oldRows, $oldCount, $tOld, $memOld);
echo sprintf("  nuovo (soli id):            %d id, %.0f ms\n", count($newIds), $tNew);
echo sprintf("  accodamento %d righe: %.0f ms; ri-accodamento (tutti duplicati): %.0f ms\n", $queued, $tQueue, $tQueueAgain);
assert_true(count(array_diff(array_keys($oldIds), $newIds)) === 0, '10. Il nuovo metodo trova tutti gli oggetti trovati dal vecchio (' . $oldCount . ' distinti)');
assert_true($tNew < $tOld, '10. La query di soli id e\' piu\' veloce del caricamento degli oggetti');
assert_true(pendingCount() === $queued && $queued === count($newIds), '10. Dedup anche su grandi numeri: ' . $queued . ' righe, ri-accodare non ne aggiunge');
clearPending();

// ── Riepilogo ────────────────────────────────────────────────────────────────

echo "\n";
echo "Risultato: \033[32m$PASSED passati\033[0m";
if ($FAILED > 0) { echo ", \033[31m$FAILED falliti\033[0m"; }
echo "\n";
$script->shutdown($FAILED > 0 ? 1 : 0);
