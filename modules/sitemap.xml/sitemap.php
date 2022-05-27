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

    foreach ($tree as $menuItem) {
        createSiteMapNode($menuItem, $root, $modifiedSubtreeByNodeIdList);
    }
}

if (eZUser::currentUser()->hasAccessTo('setup') && isset($_GET['debug'])) {
    eZDisplayDebug();
} else {
    header('Content-Type: text/xml; charset=utf-8');
    echo $dom->saveXML();
}

eZExecution::cleanExit();
