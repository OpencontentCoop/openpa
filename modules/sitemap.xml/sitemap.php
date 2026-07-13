<?php

$dom = new DOMDocument('1.0', 'UTF-8');
$root = $dom->createElement('urlset');
$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
$root = $dom->appendChild($root);

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

    function createSiteMapNode($menuItem, DOMElement $root, $modifiedSubtreeByNodeIdList, &$subtrees)
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

        if (isset($menuItem['item']['node_id'])) {
            $subtrees[] = explode('-', $menuItem['item']['node_id'])[0];
            if (isset($modifiedSubtreeByNodeIdList[$menuItem['item']['node_id']])) {
                $lastModified = date('c', $modifiedSubtreeByNodeIdList[$menuItem['item']['node_id']]);
                $subNode = $root->ownerDocument->createElement('lastmod');
                $subNode = $node->appendChild($subNode);
                $date = $root->ownerDocument->createTextNode($lastModified);
                $subNode->appendChild($date);
            }
        }

        if (isset($menuItem['item']['changefreq'])) {
            $subNode = $root->ownerDocument->createElement('changefreq');
            $subNode = $node->appendChild($subNode);
            $changefreq = $root->ownerDocument->createTextNode($menuItem['item']['changefreq']);
            $subNode->appendChild($changefreq);
        }

        if (isset($menuItem['has_children']) && $menuItem['has_children']) {
            foreach ($menuItem['children'] as $childMenuItem) {
                createSiteMapNode($childMenuItem, $root, $modifiedSubtreeByNodeIdList, $subtrees);
            }
        }
    }

    $nodes = [];
    foreach ($tree as $menuItem) {
        collectNodes($menuItem, $nodes);
    }

    $modifiedSubtreeByNodeIdList = [];
    if (count($nodes)) {
        $db = eZDB::instance();
        $inString = $db->generateSQLINStatement($nodes, 'ezcontentobject_tree.node_id', false, false, 'int');
        $query = "SELECT node_id, modified_subnode FROM ezcontentobject_tree WHERE $inString";
        $rows = $db->arrayQuery($query);
        $keys = array_column($rows, 'node_id');
        $values = array_column($rows, 'modified_subnode');
        $modifiedSubtreeByNodeIdList = array_combine($keys, $values);
    }

    $subtrees = [];
    foreach ($tree as $menuItem) {
        createSiteMapNode($menuItem, $root, $modifiedSubtreeByNodeIdList, $subtrees);
    }

    $subtrees = array_unique($subtrees);

    // Query actual content pages within the collected subtrees.
    // Uses path_string LIKE to leverage the existing DB index (2 queries total).
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
            $limit = (int)OpenPAINI::variable('SitemapSettings', 'ContentNodesLimit', 5000);
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

            $contentRows = $db->arrayQuery("
                SELECT t.node_id, t.path_string, o.modified
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
                ORDER BY t.node_id
                LIMIT $limit
            ");

            // Build per-node path arrays and collect all unique node IDs for a single alias lookup.
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

            // Fetch all URL alias segments in one query (DISTINCT ON is PostgreSQL).
            $segmentMap = [];
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
