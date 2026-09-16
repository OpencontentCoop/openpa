# Sitemap paginata (sitemap index) con logging query — Implementation Plan

> **Stato (check 2026-09-16):** piano **non ancora implementato**. Né `master` né il branch `feature/improve-sitemap` (esistente in locale e su `origin`, ma non mersato) contengono il `<sitemapindex>` paginato o il logging descritti sotto — il branch contiene solo il commit base `60511f0` citato in Spec, nessuna modifica successiva. L'unica parte del lavoro sitemap effettivamente completata dopo la stesura di questo piano è la nota "fuori scope" più in basso (riferimento `Sitemap:` in `robots.txt`), mersata su master il 2026-09-09 (PR #21, commit `a081ac7`).
>
> Il piano collegato sull'indice Postgres è in **`ocinstaller/docs/superpowers/plans/2026-09-01-index-path-string-autoinstallante.md`** — "Indice `path_string` auto-installante" (anch'esso non ancora committato/pushato). Riscontro empirico che lo ha motivato: `EXPLAIN ANALYZE` su un tenant Boat reale mostrava il planner Postgres ignorare il btree esistente su `ezcontentobject_tree.path_string` per condizioni `LIKE 'prefix%'` (le stesse usate dalle query di questa sitemap), instradandosi da una condizione poco selettiva e toccando l'equivalente di ~69.000 letture di buffer per sole 239 righe restituite — perché l'indice btree di default non è utilizzabile per il pattern matching senza l'opclass `varchar_pattern_ops`. Il fix è uno script idempotente e non bloccante (`CREATE INDEX CONCURRENTLY ... path_string varchar_pattern_ops`) da eseguire su ogni tenant Boat (automatico, nell'entrypoint) e SaaS (lancio manuale batch).

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** eliminare il troncamento silenzioso della sitemap sui siti con molte pagine, introducendo un `<sitemapindex>` con sotto-sitemap paginate al posto di un `LIMIT` fisso, e aggiungere logging strutturato per monitorare durata/volumi delle query in produzione.

**Architecture:** `modules/sitemap.xml/sitemap.php` resta l'unico script — nessun nuovo modulo eZ. La paginazione è gestita via query string (`?page=N`, coerente con i parametri `?source=`/`?debug=` già esistenti nello stesso script). Se il conteggio dei contenuti sta in una sola pagina, il comportamento resta identico a oggi (`<urlset>` unico, nessuna indirection). Solo quando serve più di una pagina, `sitemap.xml` senza `page` emette un `<sitemapindex>` che punta a `sitemap.xml?page=1..N`.

**Tech Stack:** PHP procedurale eZ Publish Legacy, PostgreSQL (query dirette via `eZDB::instance()->arrayQuery()`), `DOMDocument` per l'XML, `eZLog`/`eZDebug` per il logging.

**Spec:** discussione in sessione (nessun documento separato) — riassunta nei Global Constraints sotto. Query e struttura dati di partenza: `modules/sitemap.xml/sitemap.php` (commit `60511f0`).

## Global Constraints

- **Nessun test automatico esiste per questo modulo** (script procedurale eseguito dentro il kernel eZ, nessun harness PHPUnit per i moduli). La verifica di ogni task è manuale, via `curl` contro lo stack Docker locale (`sito-comunale-dev`), non TDD in senso stretto — coerente con l'assenza di test nel resto del repo `openpa`.
- Il fix dell'indice Postgres (`varchar_pattern_ops` su `path_string`) è un piano **separato** (`ocinstaller/docs/superpowers/plans/2026-09-01-index-path-string-autoinstallante.md`, vedi nota di stato in testa al documento) ed è un prerequisito di *efficienza*, non di *correttezza*: questo piano funziona correttamente anche prima che l'indice sia stato applicato ovunque, semplicemente le query restano lente sui siti grandi finché l'indice non arriva.
- Non toccare la sintassi `DISTINCT ON` (PostgreSQL-only) né introdurre compatibilità MySQL: il codice esistente già assume PostgreSQL (vedi commento a riga 190 del file originale).
- Non rinominare la chiave ini `SitemapSettings/ContentNodesLimit` — cambia solo semantica (da "tetto massimo" a "dimensione di pagina"), per non rompere eventuali override già impostati per singoli tenant.
- Non introdurre file/moduli nuovi per instradare le pagine: usare `$_GET['page']` sullo script esistente.

---

## Task 1: Sitemap index e paginazione della query di contenuto

**Files:**
- Modify: `openpa/modules/sitemap.xml/sitemap.php` (intero contenuto del blocco `if (OpenPAINI::variable('SiteMapSettings', 'ShowSitemap', ...))`, righe 8-256 della versione attuale)

**Interfaces:**
- Produce: `collectSitemapSubtrees($menuItem, &$subtrees): void` — sostituisce il side-effect di raccolta subtree che oggi vive dentro `createSiteMapNode` (occorre per calcolare `$subtrees` **prima** di sapere se la sitemap va paginata, senza aver già scritto nodi nel DOM).
- Produce: `createSiteMapNode($menuItem, DOMElement $root, $modifiedSubtreeByNodeIdList): void` — torna alla firma pre-luglio-2026 (senza `&$subtrees`), usata solo quando si emettono i nodi di menu (pagina 1 o sitemap non paginata).
- Produce: `buildSitemapIndexDocument(array $pageUrls): DOMDocument` — nuova funzione, costruisce il `<sitemapindex>`.

- [ ] **Step 1: Riscrivere `sitemap.php`**

Sostituire l'intero contenuto del file con:

```php
<?php

$requestedPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : null;

$dom = new DOMDocument('1.0', 'UTF-8');
$root = $dom->createElement('urlset');
$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$root = $dom->appendChild($root);

$totalPages = 0;
$servePage = null;
$totalContentRows = 0;
$queryContentMs = 0.0;
$queryAliasMs = 0.0;
$contentRowsReturned = 0;

if (OpenPAINI::variable('SiteMapSettings', 'ShowSitemap', 'enabled') === 'enabled') {
    $nodeIdList = OpenPAINI::variable('TopMenu', 'NodiCustomMenu', []);

    $topics = eZContentObject::fetchByRemoteID('topics');
    if ($topics instanceof eZContentObject) {
        $nodeIdList[] = $topics->mainNodeID();
    }

    $trasparenza = eZContentObject::fetchByRemoteID(
        OpenPAINI::variable('SitemapSettings', 'TrasaprenzaRemoteId', '5399ef12f98766b90f1804e5d52afd75')
    );
    if ($trasparenza instanceof eZContentObject) {
        $nodeIdList[] = $trasparenza->mainNodeID();
    }

    $tree = [
        [
            'item' => [
                'url' => '',
                'internal' => true,
                'changefreq' => 'hourly',
            ],
            'children' => [],
            'has_children' => false,
        ],
    ];

    foreach ($nodeIdList as $nodeId) {
        $tree[] = OpenPAMenuTool::getTreeMenu([
            'root_node_id' => (int)$nodeId,
            'user_hash' => false,
            'scope' => 'side_menu',
        ]);
    }

    if (eZUser::currentUser()->hasAccessTo('setup') && isset($_GET['source'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($tree);
        eZExecution::cleanExit();
    }

    function collectNodes($menuItem, &$nodes)
    {
        if (isset($menuItem['item']['node_id'])) {
            $nodes[] = (int)$menuItem['item']['node_id'];
        }
        if (isset($menuItem['has_children']) && $menuItem['has_children']) {
            foreach ($menuItem['children'] as $childMenuItem) {
                collectNodes($childMenuItem, $nodes);
            }
        }
    }

    function collectSitemapSubtrees($menuItem, &$subtrees)
    {
        if (isset($menuItem['item']['node_id'])) {
            $subtrees[] = explode('-', $menuItem['item']['node_id'])[0];
        }
        if (isset($menuItem['has_children']) && $menuItem['has_children']) {
            foreach ($menuItem['children'] as $childMenuItem) {
                collectSitemapSubtrees($childMenuItem, $subtrees);
            }
        }
    }

    function createSiteMapNode($menuItem, DOMElement $root, $modifiedSubtreeByNodeIdList)
    {
        $node = $root->ownerDocument->createElement('url');
        $node = $root->appendChild($node);
        $subNode = $root->ownerDocument->createElement('loc');
        $subNode = $node->appendChild($subNode);
        $isInternal = $menuItem['item']['internal'];
        $locationUrl = $menuItem['item']['url'];
        if ($isInternal) {
            $locationUrl = str_replace(' ', urlencode(' '), $locationUrl);
            $locationUrl = '/' . $locationUrl;
            eZURI::transformURI($locationUrl, false, 'full');
        }
        $url = $root->ownerDocument->createTextNode(trim($locationUrl));
        $subNode->appendChild($url);

        if (isset($menuItem['item']['node_id']) && isset($modifiedSubtreeByNodeIdList[$menuItem['item']['node_id']])) {
            $lastModified = date('c', $modifiedSubtreeByNodeIdList[$menuItem['item']['node_id']]);
            $subNode = $root->ownerDocument->createElement('lastmod');
            $subNode = $node->appendChild($subNode);
            $date = $root->ownerDocument->createTextNode($lastModified);
            $subNode->appendChild($date);
        }

        if (isset($menuItem['item']['changefreq'])) {
            $subNode = $root->ownerDocument->createElement('changefreq');
            $subNode = $node->appendChild($subNode);
            $changefreq = $root->ownerDocument->createTextNode($menuItem['item']['changefreq']);
            $subNode->appendChild($changefreq);
        }

        if (isset($menuItem['has_children']) && $menuItem['has_children']) {
            foreach ($menuItem['children'] as $childMenuItem) {
                createSiteMapNode($childMenuItem, $root, $modifiedSubtreeByNodeIdList);
            }
        }
    }

    function buildSitemapIndexDocument(array $pageUrls)
    {
        $indexDom = new DOMDocument('1.0', 'UTF-8');
        $indexRoot = $indexDom->createElement('sitemapindex');
        $indexRoot->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $indexRoot = $indexDom->appendChild($indexRoot);

        $now = date('c');
        foreach ($pageUrls as $pageUrl) {
            $sitemapNode = $indexDom->createElement('sitemap');
            $sitemapNode = $indexRoot->appendChild($sitemapNode);

            $locNode = $indexDom->createElement('loc');
            $locNode = $sitemapNode->appendChild($locNode);
            $locNode->appendChild($indexDom->createTextNode($pageUrl));

            $lastmodNode = $indexDom->createElement('lastmod');
            $lastmodNode = $sitemapNode->appendChild($lastmodNode);
            $lastmodNode->appendChild($indexDom->createTextNode($now));
        }

        return $indexDom;
    }

    $nodes = [];
    foreach ($tree as $menuItem) {
        collectNodes($menuItem, $nodes);
    }

    $modifiedSubtreeByNodeIdList = [];
    $db = eZDB::instance();
    if (count($nodes)) {
        $inString = $db->generateSQLINStatement($nodes, 'ezcontentobject_tree.node_id', false, false, 'int');
        $query = "SELECT node_id, modified_subnode FROM ezcontentobject_tree WHERE $inString";
        $rows = $db->arrayQuery($query);
        $keys = array_column($rows, 'node_id');
        $values = array_column($rows, 'modified_subnode');
        $modifiedSubtreeByNodeIdList = array_combine($keys, $values);
    }

    $subtrees = [];
    foreach ($tree as $menuItem) {
        collectSitemapSubtrees($menuItem, $subtrees);
    }
    $subtrees = array_unique($subtrees);

    $pageSize = (int)OpenPAINI::variable('SitemapSettings', 'ContentNodesLimit', 5000);
    $fromWhere = null;

    // Determina il conteggio totale dei contenuti reali (per decidere se e come paginare)
    // prima di costruire qualunque nodo DOM.
    if (!empty($subtrees)) {
        $inString = $db->generateSQLINStatement($subtrees, 'node_id', false, false, 'int');
        $rootRows = $db->arrayQuery(
            "SELECT path_string FROM ezcontentobject_tree WHERE $inString"
        );

        $pathConditions = [];
        foreach ($rootRows as $rootRow) {
            $escaped = $db->escapeString($rootRow['path_string']);
            $pathConditions[] = "t.path_string LIKE '{$escaped}%'";
        }

        if (!empty($pathConditions)) {
            $whereClause = implode(' OR ', $pathConditions);
            $excludeClause = !empty($nodes)
                ? 'AND ' . $db->generateSQLINStatement($nodes, 't.node_id', true, false, 'int')
                : '';

            $allowedClasses = array_map(
                function ($v) use ($db) { return "'" . $db->escapeString(trim($v)) . "'"; },
                OpenPAINI::variable('SitemapSettings', 'AllowedClassIdentifiers', [])
            );
            $classJoin = '';
            $classFilter = '';
            if (!empty($allowedClasses)) {
                $classJoin = 'INNER JOIN ezcontentclass cc ON cc.id = o.contentclass_id';
                $classFilter = 'AND ' . $db->generateSQLINStatement($allowedClasses, 'cc.identifier', false, false, false);
            }

            $fromWhere = "
                FROM ezcontentobject_tree t
                INNER JOIN ezcontentobject o ON o.id = t.contentobject_id AND o.status = 1
                INNER JOIN ezcobj_state_link osl ON osl.contentobject_id = o.id
                INNER JOIN ezcobj_state os
                    ON os.id = osl.contentobject_state_id AND os.identifier = 'public'
                INNER JOIN ezcobj_state_group osg
                    ON osg.id = os.group_id AND osg.identifier = 'privacy'
                $classJoin
                WHERE ($whereClause)
                  AND t.is_hidden = 0
                  AND t.is_invisible = 0
                  AND o.section_id = 1
                  $excludeClause
                  $classFilter
            ";

            $countRow = $db->arrayQuery("SELECT count(*) AS total $fromWhere");
            $totalContentRows = (int)($countRow[0]['total'] ?? 0);
            $totalPages = $totalContentRows > 0 ? (int)ceil($totalContentRows / $pageSize) : 0;
        }
    }

    // Se serve più di una pagina e nessuna pagina specifica è stata richiesta,
    // rispondi con l'indice invece che con una singola urlset.
    if ($totalPages > 1 && $requestedPage === null) {
        $baseSitemapUrl = '/sitemap.xml';
        eZURI::transformURI($baseSitemapUrl, false, 'full');
        $pageUrls = [];
        for ($p = 1; $p <= $totalPages; $p++) {
            $pageUrls[] = $baseSitemapUrl . '?page=' . $p;
        }
        $dom = buildSitemapIndexDocument($pageUrls);
        $root = null; // non più usato: da qui in poi si serializza $dom, non $root
    } else {
        $servePage = ($totalPages <= 1) ? 1 : $requestedPage;

        if ($servePage === 1) {
            foreach ($tree as $menuItem) {
                createSiteMapNode($menuItem, $root, $modifiedSubtreeByNodeIdList);
            }
        }

        if ($servePage !== null && $fromWhere !== null && $totalContentRows > 0) {
            $offset = ($servePage - 1) * $pageSize;

            $contentStart = microtime(true);
            $contentRows = $db->arrayQuery("
                SELECT t.node_id, t.path_string, o.modified
                $fromWhere
                ORDER BY t.node_id
                LIMIT $pageSize OFFSET $offset
            ");
            $queryContentMs = round((microtime(true) - $contentStart) * 1000, 2);
            $contentRowsReturned = count($contentRows);

            $nodePathMap = [];
            $allAliasNodeIds = [];
            foreach ($contentRows as $row) {
                $parts = array_values(array_filter(explode('/', $row['path_string'])));
                array_shift($parts); // strip system root (node 1)
                $parts = array_map('intval', $parts);
                $nodePathMap[(int)$row['node_id']] = $parts;
                foreach ($parts as $nid) {
                    $allAliasNodeIds[$nid] = true;
                }
            }

            $segmentMap = [];
            $aliasStart = microtime(true);
            if (!empty($allAliasNodeIds)) {
                $actionList = implode(', ', array_map(
                    function ($id) use ($db) {
                        return "'" . $db->escapeString("eznode:$id") . "'";
                    },
                    array_keys($allAliasNodeIds)
                ));
                $langFilter = trim(eZContentLanguage::languagesSQLFilter('ezurlalias_ml', 'lang_mask'));
                $langOrderParts = ['action'];
                foreach (eZContentLanguage::prioritizedLanguages() as $lang) {
                    $langId = (int)$lang->attribute('id');
                    $langOrderParts[] = "(lang_mask & $langId) DESC";
                }
                $langOrder = implode(', ', $langOrderParts);
                $aliasRows = $db->arrayQuery("
                    SELECT DISTINCT ON (action) action, text, parent
                    FROM ezurlalias_ml
                    WHERE ($langFilter)
                      AND action IN ($actionList)
                      AND is_original = 1
                      AND is_alias = 0
                    ORDER BY $langOrder
                ");
                foreach ($aliasRows as $r) {
                    $nid = (int)substr($r['action'], 7); // len('eznode:') = 7
                    $segmentMap[$nid] = ['text' => $r['text'], 'parent' => (int)$r['parent']];
                }
            }
            $queryAliasMs = round((microtime(true) - $aliasStart) * 1000, 2);

            foreach ($contentRows as $row) {
                $nodeId = (int)$row['node_id'];
                $segments = [];
                foreach ($nodePathMap[$nodeId] ?? [] as $nid) {
                    if (!isset($segmentMap[$nid])) {
                        continue;
                    }
                    if ($segmentMap[$nid]['parent'] === 0) {
                        $segments = []; // mount point: reset like fetchPathByActionList does
                    }
                    $segments[] = $segmentMap[$nid]['text'];
                }

                if (empty($segments)) {
                    continue;
                }

                $locationUrl = '/' . implode('/', $segments);
                eZURI::transformURI($locationUrl, false, 'full');

                $urlNode = $dom->createElement('url');
                $urlNode = $root->appendChild($urlNode);

                $locNode = $dom->createElement('loc');
                $locNode = $urlNode->appendChild($locNode);
                $locNode->appendChild($dom->createTextNode($locationUrl));

                if (!empty($row['modified'])) {
                    $lastmodNode = $dom->createElement('lastmod');
                    $lastmodNode = $urlNode->appendChild($lastmodNode);
                    $lastmodNode->appendChild($dom->createTextNode(date('c', (int)$row['modified'])));
                }
            }
        }
    }
}

if (eZUser::currentUser()->hasAccessTo('setup') && isset($_GET['debug'])) {
    $urls = [];
    foreach ($dom->getElementsByTagName('loc') as $loc) {
        $urls[] = $loc->nodeValue;
    }
    echo '<pre>';print_r($urls);echo '</pre>';
    eZDisplayDebug();
} else {
    $ttl = (int)OpenPAINI::variable('SitemapSettings', 'CacheTTL', 3600);
    header('Content-Type: text/xml; charset=utf-8');
    header('Cache-Control: public, s-maxage=' . $ttl . ', max-age=' . $ttl);
    header('Vary: Accept-Encoding');
    echo $dom->saveXML();
}

eZExecution::cleanExit();
```

- [ ] **Step 2: Avviare lo stack locale (se non già attivo)**

```bash
cd ../../sito-comunale-dev   # dalla root di openpa
docker compose up -d
docker compose logs -f app   # attendere "ready to handle connections" o equivalente, poi Ctrl+C
```

`../openpa` è già montato in bind mount in `docker-compose.override.yml` — le modifiche al file sono visibili immediatamente nel container, nessun rebuild necessario.

- [ ] **Step 3: Verifica comportamento invariato sul contenuto di fixture (poche pagine, niente paginazione)**

```bash
curl -s http://opencity.localtest.me/sitemap.xml | grep -o '<urlset\|<sitemapindex'
```

Atteso: `<urlset` (non `<sitemapindex`, perché il contenuto di fixture ha poche pagine — meno del `pageSize` di default 5000).

- [ ] **Step 4: Forzare la paginazione per verificarla davvero**

Aggiungere temporaneamente in `docker-compose.override.yml`, sotto `services.app.environment`:

```yaml
      EZINI_openpa__SitemapSettings__ContentNodesLimit: '2'
```

```bash
docker compose up -d app   # ricrea il container con la nuova env
curl -s http://opencity.localtest.me/sitemap.xml | grep -o '<sitemapindex\|<loc>[^<]*</loc>'
```

Atteso: `<sitemapindex` e almeno due `<loc>` del tipo `http://opencity.localtest.me/sitemap.xml?page=1`, `?page=2`, ...

```bash
curl -s "http://opencity.localtest.me/sitemap.xml?page=1" > /tmp/page1.xml
curl -s "http://opencity.localtest.me/sitemap.xml?page=2" > /tmp/page2.xml
grep -o '<loc>[^<]*</loc>' /tmp/page1.xml | sort > /tmp/urls1.txt
grep -o '<loc>[^<]*</loc>' /tmp/page2.xml | sort > /tmp/urls2.txt
comm -12 /tmp/urls1.txt /tmp/urls2.txt   # deve essere VUOTO: nessun URL duplicato tra pagine
```

Atteso: nessuna riga in output da `comm -12` (nessuna sovrapposizione tra pagina 1 e pagina 2).

- [ ] **Step 5: Rimuovere l'override temporaneo**

Rimuovere la riga `EZINI_openpa__SitemapSettings__ContentNodesLimit: '2'` da `docker-compose.override.yml` (file gitignored, non finisce comunque in un commit, ma va tolto per non falsare test successivi) e ricreare il container:

```bash
docker compose up -d app
```

- [ ] **Step 6: Commit**

```bash
cd ../openpa
git add modules/sitemap.xml/sitemap.php
git commit -m "Introduce sitemap index paginata per evitare troncamento silenzioso oltre ContentNodesLimit"
```

---

## Task 2: Logging strutturato di durata e volumi query

**Files:**
- Modify: `openpa/modules/sitemap.xml/sitemap.php` (blocco finale, dopo la costruzione del contenuto, prima della serializzazione)

**Interfaces:**
- Consumi da Task 1: variabili già presenti nello scope dello script — `$totalPages`, `$servePage`, `$totalContentRows`, `$contentRowsReturned`, `$queryContentMs`, `$queryAliasMs`, `$subtrees`.
- Nessuna nuova funzione: si usa `eZLog::write($message, $filename)`, già un pattern consolidato nel repo (vedi `openpa/bin/php/fix_contact_matrix.php:45`).

- [ ] **Step 1: Aggiungere il logging**

Subito prima del blocco finale `if (eZUser::currentUser()->hasAccessTo('setup') && isset($_GET['debug']))` in `sitemap.php`, aggiungere:

```php
if (OpenPAINI::variable('SiteMapSettings', 'ShowSitemap', 'enabled') === 'enabled') {
    $logLine = sprintf(
        'siteaccess=%s page=%s total_pages=%d subtrees=%d content_total=%d content_returned=%d query_content_ms=%.2f query_alias_ms=%.2f',
        OpenPABase::getCurrentSiteaccessIdentifier(),
        $servePage !== null ? $servePage : 'index',
        $totalPages,
        count($subtrees),
        $totalContentRows,
        $contentRowsReturned,
        $queryContentMs,
        $queryAliasMs
    );
    eZLog::write($logLine, 'sitemap.log');

    $slowThresholdMs = (int)OpenPAINI::variable('SitemapSettings', 'SlowQueryThresholdMs', 2000);
    if ($queryContentMs > $slowThresholdMs || $queryAliasMs > $slowThresholdMs) {
        eZDebug::writeWarning($logLine, 'sitemap.xml query lenta');
    }
}
```

Nota: `$subtrees` può non essere definita se `ShowSitemap` è `disabled` — ma questo blocco è già dentro/dopo la stessa condizione, quindi è sempre definita quando eseguito (verificare comunque allo Step 3 sotto, sito con `ShowSitemap: disabled`).

- [ ] **Step 2: Verifica sullo stack locale**

```bash
docker compose exec app cat /var/www/html/var/log/sitemap.log | tail -5
```

Atteso: una riga per ogni richiesta a `/sitemap.xml`, con tutti i campi `siteaccess=... page=... total_pages=... subtrees=... content_total=... content_returned=... query_content_ms=... query_alias_ms=...` valorizzati (non vuoti, non `NAN`).

- [ ] **Step 3: Verifica che il logging non rompa il caso `ShowSitemap: disabled`**

```bash
docker compose exec app bash -c "echo 'EZINI_openpa__SiteMapSettings__ShowSitemap=disabled' "
```

(oppure aggiungere temporaneamente `EZINI_openpa__SiteMapSettings__ShowSitemap: 'disabled'` in `docker-compose.override.yml`, ricreare il container)

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://opencity.localtest.me/sitemap.xml
```

Atteso: `200` (nessun errore PHP/fatal — la sitemap vuota viene comunque servita, senza tentare di loggare variabili non definite). Rimuovere l'override e ricreare il container al termine.

- [ ] **Step 4: Commit**

```bash
git add modules/sitemap.xml/sitemap.php
git commit -m "Aggiunge logging strutturato durata/volumi query sitemap.xml"
```

---

## Nota — fuori scope di questo piano

Il riferimento `Sitemap: <url>` in `robots.txt` (proposto in sessione) resta un task separato e indipendente, non incluso qui: tocca `modules/robots.txt/robots.php`, non ha dipendenze da questo piano e può essere fatto prima, dopo o in parallelo.

**Aggiornamento 2026-09-16:** questo task è stato implementato e mersato su master il 2026-09-09 (PR #21 "feature/add-sitemap-to-robots-txt", commit `a081ac7` "Aggiunge il riferimento alla sitemap nel robots.txt", merge `27468b9`).
