<?php
/**
 * Test E2E: OpenPAWhoCan non deve andare in fatal su un oggetto senza nodo principale
 * (archiviato / nel cestino) quando una policy a limitazione Node viene valutata.
 *
 * Regressione osservata su un tenant di produzione (ticket Freshdesk #32045): due redattori
 * avevano, tra le altre, una policy content/read con Node + StateGroup + Subtree insieme a una
 * policy content/read per Section. Per un ente cestinato ma ancora referenziato da un ruolo,
 * la policy Node veniva valutata prima di quella per Section e OpenPAWhoCan::checkAccess()
 * eseguiva mainNode()->attribute('node_id') su null:
 *   "Call to a member function attribute() on null" (HTTP 500 sulle schede persona).
 *
 * Atteso: un oggetto senza nodo principale non combacia con nessuna limitazione Node (come un
 * nodo diverso) e la valutazione prosegue con le altre policy.
 *
 * Utente U1 = ruolo R1 (Node + StateGroup + Subtree) + ruolo R2 (Section dell'oggetto):
 *   - ente archiviato -> leggibile (lo autorizza R2, come nel caso reale)
 *   - ente vivo nel Node di R1 -> leggibile
 * Utente U2 = solo R1:
 *   - ente archiviato -> NON leggibile, senza fatal
 *   - ente vivo nel Node di R1 -> leggibile (comportamento invariato)
 *   - ente vivo FUORI dal Node di R1 -> NON leggibile (comportamento invariato)
 *
 * Run:
 *   docker exec sito-comunale-dev-app-1 sh -c \
 *     'OUT=$(php /var/www/html/extension/openpa/tests/test_openpawhocan_object_without_main_node.php 2>&1); echo "$OUT"'
 */

$ezRoot = '/var/www/html';
chdir($ezRoot);
require_once $ezRoot . '/autoload.php';

$script = eZScript::instance([
    'description'    => 'OpenPAWhoCan object without main node test',
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

$createdObjectIds = [];
$createdRoleIds = [];
$createdSectionIds = [];
function cleanup(array $ids, array $roleIds, array $sectionIds): void
{
    echo "\nCleanup: rimuovo " . count($ids) . " oggetti, " . count($roleIds) . " ruoli e " . count($sectionIds) . " sezioni di test...\n";
    foreach ($roleIds as $roleId) {
        $role = eZRole::fetch($roleId);
        if ($role instanceof eZRole) { $role->removeThis(); }
    }
    foreach ($ids as $id) {
        $object = eZContentObject::fetch($id);
        if ($object instanceof eZContentObject) {
            // purge() rimuove anche gli oggetti archiviati (nel cestino), che remove() non tocca
            $object->purge();
        }
    }
    foreach ($sectionIds as $sectionId) {
        $section = eZSection::fetch($sectionId);
        if ($section instanceof eZSection) { $section->removeThis(); }
    }
    eZRole::expireCache();
    echo "Rimossi.\n";
}
register_shutdown_function(function () use (&$createdObjectIds, &$createdRoleIds, &$createdSectionIds) { cleanup($createdObjectIds, $createdRoleIds, $createdSectionIds); });

// ── Pre-condizioni ──────────────────────────────────────────────────────────

foreach (['organization', 'user'] as $identifier) {
    if (!eZContentClass::fetchByIdentifier($identifier)) {
        echo "\033[33m[SKIP]\033[0m Content type $identifier non trovato\n";
        $script->shutdown(0);
        exit(0);
    }
}

$adminUser = eZUser::fetchByName('admin');
assert_true($adminUser instanceof eZUser, "Precondizione: utente 'admin' esiste");
$ownerId = $adminUser->attribute('contentobject_id');
eZUser::setCurrentlyLoggedInUser($adminUser, $ownerId, eZUser::NO_SESSION_REGENERATE);

$db = eZDB::instance();
$parentOf = function (string $classIdentifier) use ($db): int {
    $rows = $db->arrayQuery(
        "SELECT DISTINCT n.parent_node_id FROM ezcontentobject_tree n " .
        "JOIN ezcontentobject o ON o.id = n.contentobject_id " .
        "JOIN ezcontentclass c ON c.id = o.contentclass_id " .
        "WHERE c.identifier = '" . $db->escapeString($classIdentifier) . "' LIMIT 1"
    );
    return !empty($rows) ? (int)$rows[0]['parent_node_id'] : 2;
};
$orgParentNodeId  = $parentOf('organization');
$userParentNodeId = $parentOf('user');

$uniqueSuffix = date('Ymd-His') . '-' . substr(md5(uniqid()), 0, 6);

// Sezione dedicata: le policy ereditate dal gruppo dell'utente (es. Anonymous per Section)
// non autorizzano gli enti di test, quindi decidono solo R1 ed R2.
$testSection = new eZSection(['name' => 'Test whocan ' . $uniqueSuffix, 'identifier' => 'test-whocan-' . substr(md5($uniqueSuffix), 0, 8), 'locale' => '']);
$testSection->store();
$testSectionId = (int)$testSection->attribute('id');
$createdSectionIds[] = $testSectionId;

// ── Helper ───────────────────────────────────────────────────────────────────

function createObject(string $classIdentifier, int $parentNodeId, int $ownerId, int $sectionId, array $attributes, array &$createdObjectIds): eZContentObject
{
    $class = eZContentClass::fetchByIdentifier($classIdentifier);
    $object = $class->instantiate($ownerId, $sectionId, false, 'ita-IT');
    eZNodeAssignment::create([
        'contentobject_id'      => $object->attribute('id'),
        'contentobject_version' => 1,
        'parent_node'           => $parentNodeId,
        'is_main'               => 1,
        'sort_field'            => eZContentObjectTreeNode::SORT_FIELD_PUBLISHED,
        'sort_order'            => eZContentObjectTreeNode::SORT_ORDER_DESC,
    ])->store();
    foreach ($object->version(1)->contentObjectAttributes('ita-IT') as $attr) {
        $id = $attr->contentClassAttributeIdentifier();
        if (isset($attributes[$id])) {
            $attr->fromString($attributes[$id]);
            $attr->store();
        }
    }
    $createdObjectIds[] = $object->attribute('id');
    eZOperationHandler::execute('content', 'publish', ['object_id' => $object->attribute('id'), 'version' => 1]);

    return eZContentObject::fetch($object->attribute('id'));
}

function createUser(int $parentNodeId, int $ownerId, int $sectionId, string $login, array &$createdObjectIds): eZUser
{
    $object = createObject('user', $parentNodeId, $ownerId, $sectionId, [], $createdObjectIds);
    $id = (int)$object->attribute('id');
    if (!eZUser::fetch($id) instanceof eZUser) {
        $user = eZUser::create($id);
        $user->setAttribute('login', $login);
        $user->setAttribute('email', $login . '@example.invalid');
        $user->setAttribute('password_hash', eZUser::createHash($login, 'Test-' . $login . '!', eZUser::site(), eZUser::hashType()));
        $user->setAttribute('password_hash_type', eZUser::hashType());
        $user->store();
    }

    return eZUser::fetch($id);
}

/** Valuta content/read con OpenPAWhoCan; ritorna true/false oppure la stringa dell'errore se va in fatal. */
function canRead(eZContentObject $object, eZUser $user)
{
    try {
        $whoCan = new OpenPAWhoCan(eZContentObject::fetch($object->attribute('id')), 'read', $user);
        return $whoCan->run() === true;
    } catch (\Throwable $e) {
        return 'FATAL: ' . $e->getMessage();
    }
}

// ── Setup: tre organization nella sezione di test ────────────────────────────
// A = viva, nel Node di R1; C = viva, FUORI dal Node di R1; B = finita nel cestino.

$orgLive    = createObject('organization', $orgParentNodeId, $ownerId, $testSectionId, ['name' => 'Test Org Viva ' . $uniqueSuffix], $createdObjectIds);
$orgOutside = createObject('organization', $orgParentNodeId, $ownerId, $testSectionId, ['name' => 'Test Org Fuori ' . $uniqueSuffix], $createdObjectIds);
$orgGone    = createObject('organization', $orgParentNodeId, $ownerId, $testSectionId, ['name' => 'Test Org Archiviata ' . $uniqueSuffix], $createdObjectIds);
foreach ([$orgLive, $orgOutside, $orgGone] as $org) {
    eZContentObjectTreeNode::assignSectionToSubTree((int)$org->attribute('main_node_id'), $testSectionId);
}
$orgLiveNodeId = (int)$orgLive->attribute('main_node_id');
eZContentObjectTreeNode::removeSubtrees([(int)$orgGone->attribute('main_node_id')], true);
$orgGone = eZContentObject::fetch((int)$orgGone->attribute('id'));
assert_true(!$orgGone->attribute('main_node') instanceof eZContentObjectTreeNode,
    "Precondizione: l'organization archiviata non ha un nodo principale");
assert_true((int)eZContentObject::fetch((int)$orgLive->attribute('id'))->attribute('section_id') === $testSectionId
    && (int)eZContentObject::fetch((int)$orgGone->attribute('id'))->attribute('section_id') === $testSectionId,
    'Precondizione: le organization di test stanno nella sezione dedicata');

// ── Ruoli, con la stessa forma di quelli reali ───────────────────────────────
// R1 ("Editor Utenti"): content/read con Node + StateGroup + Subtree INSIEME, creato per primo
// cosi' viene valutato per primo. R2 ("Editor Base"): content/read per Section.

$r1Limitations = ['Node' => [$orgLiveNodeId]];
$lockGroup = eZContentObjectStateGroup::fetchByIdentifier('opencity_lock');
if ($lockGroup instanceof eZContentObjectStateGroup) {
    $lockStateIds = array_map(function ($s) { return (int)$s->attribute('id'); }, $lockGroup->attribute('states'));
    $objectLockStates = array_values(array_intersect($lockStateIds, array_map('intval', $orgGone->attribute('state_id_array'))));
    if (!empty($objectLockStates)) {
        $r1Limitations['StateGroup_opencity_lock'] = $objectLockStates;
    }
}
$r1Limitations['Subtree'] = ['/1/5/12/'];
echo "(info) limitazioni di R1: " . implode(', ', array_keys($r1Limitations)) . "\n";

$role1 = eZRole::create('test-whocan-node-' . $uniqueSuffix);
$role1->store();
$role1->appendPolicy('content', 'read', $r1Limitations);
$createdRoleIds[] = $role1->attribute('id');

$role2 = eZRole::create('test-whocan-section-' . $uniqueSuffix);
$role2->store();
$role2->appendPolicy('content', 'read', ['Section' => [$testSectionId]]);
$createdRoleIds[] = $role2->attribute('id');

$user1 = createUser($userParentNodeId, $ownerId, $testSectionId, 'test-whocan-u1-' . substr(md5($uniqueSuffix), 0, 6), $createdObjectIds);
$user2 = createUser($userParentNodeId, $ownerId, $testSectionId, 'test-whocan-u2-' . substr(md5($uniqueSuffix), 6, 6), $createdObjectIds);
$role1->assignToUser((int)$user1->attribute('contentobject_id'));
$role2->assignToUser((int)$user1->attribute('contentobject_id'));
$role1->assignToUser((int)$user2->attribute('contentobject_id'));
eZRole::expireCache();
eZUser::cleanupCache();
$user1 = eZUser::fetch((int)$user1->attribute('contentobject_id'));
$user2 = eZUser::fetch((int)$user2->attribute('contentobject_id'));

// ── U1: R1 + R2 (il caso reale) ──────────────────────────────────────────────

echo "\n=== U1: ruolo Node+StateGroup+Subtree e ruolo Section ===\n";
$gone = canRead($orgGone, $user1);
assert_true(!is_string($gone), "U1, ente archiviato: nessun fatal" . (is_string($gone) ? " ($gone)" : ''));
assert_true($gone === true, "U1, ente archiviato: leggibile (lo autorizza la policy per Section)");
assert_true(canRead($orgLive, $user1) === true, "U1, ente vivo nel Node: leggibile");

// ── U2: solo R1 ──────────────────────────────────────────────────────────────

echo "\n=== U2: solo ruolo Node+StateGroup+Subtree ===\n";
$gone = canRead($orgGone, $user2);
assert_true(!is_string($gone), "U2, ente archiviato: nessun fatal" . (is_string($gone) ? " ($gone)" : ''));
assert_true($gone === false, "U2, ente archiviato: NON leggibile (nessuna policy lo autorizza)");
assert_true(canRead($orgLive, $user2) === true, "U2, ente vivo nel Node: leggibile (invariato)");
assert_true(canRead($orgOutside, $user2) === false, "U2, ente vivo FUORI dal Node: NON leggibile (invariato)");

echo "\n";
echo "Risultato: $PASSED passati / " . ($PASSED + $FAILED) . " totali\n";
$script->shutdown($FAILED > 0 ? 1 : 0);
