<?php

/**
 * Sitemap del sito: sempre un <sitemapindex> con sitemap figlie per sezione, paginate.
 *
 *   /sitemap.xml                            indice
 *   /sitemap.xml?section=pagine             home, voci di menu, Argomenti
 *   /sitemap.xml?section=trasparenza        Amministrazione trasparente
 *   /sitemap.xml?section=faq                Domande frequenti
 *   /sitemap.xml?section=contenuti&page=N   tutti gli altri contenuti pubblici
 *
 * Nessun URL vietato da robots.txt: i sottoalberi vietati sono esclusi in SQL e ogni <loc>
 * passa da OpenPACrawlPolicy::isAllowed().
 */
class OpenPASitemapBuilder
{
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    const SECTION_PAGES = 'pagine';

    const SECTION_TRASPARENZA = 'trasparenza';

    const SECTION_FAQ = 'faq';

    const SECTION_CONTENTS = 'contenuti';

    const MAX_PAGE_SIZE = 40000;

    private $policy;

    private $db;

    private $sections;

    private $menuTrees;

    private $menuNodeIds;

    private $menuModified;

    private $stats = [];

    public function __construct(OpenPACrawlPolicy $policy)
    {
        $this->policy = $policy;
        $this->db = eZDB::instance();
    }

    public static function isEnabled()
    {
        foreach (['SitemapSettings', 'SiteMapSettings'] as $group) {
            if (OpenPAINI::variable($group, 'ShowSitemap', 'enabled') === 'disabled') {
                return false;
            }
        }
        return true;
    }

    /**
     * auto (default): sitemap completa solo se il DB ha l'indice per i LIKE su path_string.
     */
    public static function isFullSitemapEnabled()
    {
        $value = OpenPAINI::variable('SitemapSettings', 'FullSiteMap', 'auto');
        if ($value === 'enabled') {
            return true;
        }
        if ($value === 'disabled') {
            return false;
        }
        return self::hasPathStringPatternIndex();
    }

    public static function hasPathStringPatternIndex()
    {
        $db = eZDB::instance();
        if ($db->databaseName() !== 'postgresql') {
            return false;
        }
        $rows = $db->arrayQuery(
            "SELECT pg_get_indexdef(i.indexrelid) AS indexdef
             FROM pg_index i
             INNER JOIN pg_class c ON c.oid = i.indrelid
             WHERE c.relname = 'ezcontentobject_tree' AND i.indisvalid"
        );
        foreach ($rows as $row) {
            $definition = strtolower($row['indexdef']);
            if (strpos($definition, 'path_string') !== false
                && (strpos($definition, 'varchar_pattern_ops') !== false || strpos($definition, 'text_pattern_ops') !== false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Il siteaccess corrente e' backend/debug: la sitemap non va servita.
     */
    public static function isPrivateSiteaccess()
    {
        $current = eZSiteAccess::current();
        $name = strtolower(isset($current['name']) ? $current['name'] : '');
        $privateUris = (array)eZINI::instance('openpa.ini')->variable('CrawlPolicy', 'PrivateUriList');
        foreach ($privateUris as $uri) {
            $uri = strtolower(trim($uri, '/ '));
            if ($uri !== '' && ($name === $uri || substr($name, -strlen('_' . $uri)) === '_' . $uri)) {
                return true;
            }
        }
        return false;
    }

    public static function getPageSize()
    {
        $size = (int)OpenPAINI::variable('SitemapSettings', 'ContentNodesLimit', 10000);
        return max(1, min(self::MAX_PAGE_SIZE, $size));
    }

    public static function getCacheTTL()
    {
        return max(0, (int)OpenPAINI::variable('SitemapSettings', 'CacheTTL', 3600));
    }

    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @return string[]
     */
    public static function getSectionNames()
    {
        return [self::SECTION_PAGES, self::SECTION_TRASPARENZA, self::SECTION_FAQ, self::SECTION_CONTENTS];
    }

    /**
     * Albero dei menu usato per la sitemap (per ?source).
     * @return array
     */
    public function getMenuTrees()
    {
        $this->loadStructure();
        return $this->menuTrees;
    }

    public function getIndexXml()
    {
        return $this->cached('index', function () {
            return $this->buildIndexXml();
        });
    }

    /**
     * @param string $section
     * @param int $page da 1
     * @return string|null null se sezione o pagina non esistono
     */
    public function getSectionXml($section, $page)
    {
        if (!in_array($section, self::getSectionNames(), true) || $page < 1) {
            return null;
        }
        $result = $this->cached('section-' . $section . '-' . $page, function () use ($section, $page) {
            $xml = $this->buildSectionXml($section, $page);
            return $xml === null ? '' : $xml;
        });
        return $result === '' ? null : $result;
    }

    private function buildIndexXml()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->appendChild($dom->createElementNS(self::SITEMAP_NS, 'sitemapindex'));

        foreach (self::getSectionNames() as $section) {
            foreach ($this->getSectionPages($section) as $page => $info) {
                $loc = '/sitemap.xml?section=' . rawurlencode($section);
                if ($section === self::SECTION_CONTENTS || $page > 1) {
                    $loc .= '&page=' . $page;
                }
                $sitemap = $root->appendChild($dom->createElementNS(self::SITEMAP_NS, 'sitemap'));
                $sitemap->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc'))
                    ->appendChild($dom->createTextNode($this->policy->absoluteUrl($loc)));
                if ($info['lastmod'] > 0) {
                    $sitemap->appendChild($dom->createElementNS(self::SITEMAP_NS, 'lastmod'))
                        ->appendChild($dom->createTextNode(date('c', $info['lastmod'])));
                }
            }
        }

        return $dom->saveXML();
    }

    /**
     * Pagine di una sezione con il lastmod massimo di ciascuna.
     * @param string $section
     * @return array<int, array{first_node: int|null, count: int, lastmod: int}>
     */
    public function getSectionPages($section)
    {
        $this->loadStructure();
        $menuItems = $this->getSectionMenuItems($section);
        $menuLastmod = 0;
        foreach ($menuItems as $item) {
            $menuLastmod = max($menuLastmod, $item['lastmod']);
        }

        $pages = [];
        $query = $this->getSectionContentQuery($section);
        if ($query !== null) {
            $pageSize = self::getPageSize();
            $start = microtime(true);
            $rows = $this->db->arrayQuery(
                "SELECT page, MIN(node_id) AS first_node, COUNT(*) AS total, MAX(modified) AS lastmod
                 FROM (
                     SELECT t.node_id, o.modified,
                            (ROW_NUMBER() OVER (ORDER BY t.node_id) - 1) / $pageSize AS page
                     {$query}
                 ) numbered
                 GROUP BY page
                 ORDER BY page"
            );
            $this->logQuery('pages:' . $section, $start, count($rows));
            foreach ($rows as $row) {
                $pages[(int)$row['page'] + 1] = [
                    'first_node' => (int)$row['first_node'],
                    'count' => (int)$row['total'],
                    'lastmod' => (int)$row['lastmod'],
                ];
            }
        }

        if (!empty($menuItems)) {
            if (!isset($pages[1])) {
                $pages[1] = ['first_node' => null, 'count' => 0, 'lastmod' => 0];
            }
            $pages[1]['lastmod'] = max($pages[1]['lastmod'], $menuLastmod);
            $pages[1]['count'] += count($menuItems);
        }
        ksort($pages);

        return $pages;
    }

    private function buildSectionXml($section, $page)
    {
        $pages = $this->getSectionPages($section);
        if (!isset($pages[$page])) {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->appendChild($dom->createElementNS(self::SITEMAP_NS, 'urlset'));
        $count = 0;

        if ($page === 1) {
            foreach ($this->getSectionMenuItems($section) as $item) {
                $count += $this->appendUrl($dom, $root, $item['path'], $item['lastmod']);
            }
        }

        $query = $this->getSectionContentQuery($section);
        if ($query !== null && $pages[$page]['first_node'] !== null) {
            $range = 't.node_id >= ' . (int)$pages[$page]['first_node'];
            if (isset($pages[$page + 1]) && $pages[$page + 1]['first_node'] !== null) {
                $range .= ' AND t.node_id < ' . (int)$pages[$page + 1]['first_node'];
            }
            $limit = self::getPageSize();
            $start = microtime(true);
            $rows = $this->db->arrayQuery(
                "SELECT t.node_id, t.path_string, o.modified {$query} AND $range ORDER BY t.node_id LIMIT $limit"
            );
            $this->logQuery('content:' . $section . ':' . $page, $start, count($rows));

            foreach ($this->resolveContentPaths($rows) as $item) {
                $count += $this->appendUrl($dom, $root, $item['path'], $item['lastmod']);
            }
        }

        $this->stats['urls'] = $count;
        return $dom->saveXML();
    }

    /**
     * @return int 1 se l'URL e' stato aggiunto
     */
    private function appendUrl(DOMDocument $dom, DOMNode $root, $path, $lastmod)
    {
        $path = OpenPACrawlPolicy::encodePath('/' . ltrim($path, '/'));
        eZURI::transformURI($path, false, 'relative', false);
        if (!$this->policy->isAllowed($path)) {
            $this->stats['skipped_by_robots'][] = $path;
            return 0;
        }
        $url = $root->appendChild($dom->createElementNS(self::SITEMAP_NS, 'url'));
        $url->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc'))
            ->appendChild($dom->createTextNode($this->policy->absoluteUrl($path)));
        if ($lastmod > 0) {
            $url->appendChild($dom->createElementNS(self::SITEMAP_NS, 'lastmod'))
                ->appendChild($dom->createTextNode(date('c', (int)$lastmod)));
        }
        return 1;
    }

    private function loadStructure()
    {
        if ($this->sections !== null) {
            return;
        }

        $menuRootIds = array_map('intval', (array)OpenPAINI::variable('TopMenu', 'NodiCustomMenu', []));
        $topics = eZContentObject::fetchByRemoteID('topics');
        if ($topics instanceof eZContentObject && $topics->mainNodeID()) {
            $menuRootIds[] = (int)$topics->mainNodeID();
        }
        $menuRootIds = array_values(array_unique(array_filter($menuRootIds)));

        $trasparenzaNode = null;
        $trasparenza = eZContentObject::fetchByRemoteID(
            OpenPAINI::variable('SitemapSettings', 'TrasaprenzaRemoteId', '5399ef12f98766b90f1804e5d52afd75')
        );
        if ($trasparenza instanceof eZContentObject) {
            $trasparenzaNode = $trasparenza->mainNode();
        }

        $faqNode = null;
        $faq = eZContentObject::fetchByRemoteID('faq_system');
        if ($faq instanceof eZContentObject) {
            $faqNode = $faq->mainNode();
        }

        $this->menuTrees = [self::SECTION_PAGES => [], self::SECTION_TRASPARENZA => []];
        foreach ($menuRootIds as $nodeId) {
            $this->menuTrees[self::SECTION_PAGES][] = $this->getTreeMenu($nodeId);
        }
        if ($trasparenzaNode instanceof eZContentObjectTreeNode) {
            $this->menuTrees[self::SECTION_TRASPARENZA][] = $this->getTreeMenu((int)$trasparenzaNode->attribute('node_id'));
        }

        $this->menuNodeIds = [];
        foreach ($this->menuTrees as $trees) {
            foreach ($trees as $tree) {
                self::collectMenuNodeIds($tree, $this->menuNodeIds);
            }
        }
        $this->menuNodeIds = array_values(array_unique($this->menuNodeIds));
        $this->menuModified = $this->fetchModifiedSubnode($this->menuNodeIds);

        $menuRootPaths = $this->fetchPathStrings($menuRootIds);
        $trasparenzaPath = $trasparenzaNode instanceof eZContentObjectTreeNode ? $trasparenzaNode->attribute('path_string') : null;
        $faqPath = $faqNode instanceof eZContentObjectTreeNode ? $faqNode->attribute('path_string') : null;

        $this->sections = [
            self::SECTION_PAGES => [
                'roots' => [],
                'exclude' => [],
            ],
            self::SECTION_TRASPARENZA => [
                'roots' => $trasparenzaPath ? [$trasparenzaPath] : [],
                'exclude' => [],
            ],
            self::SECTION_FAQ => [
                'roots' => $faqPath ? [$faqPath] : [],
                'exclude' => [],
                // senza sitemap completa: solo la pagina principale delle FAQ
                'root_node_only' => $faqNode instanceof eZContentObjectTreeNode ? (int)$faqNode->attribute('node_id') : null,
            ],
            self::SECTION_CONTENTS => [
                'roots' => $menuRootPaths,
                'exclude' => array_values(array_filter([$trasparenzaPath, $faqPath])),
            ],
        ];
    }

    private function getTreeMenu($nodeId)
    {
        return OpenPAMenuTool::getTreeMenu([
            'root_node_id' => (int)$nodeId,
            'user_hash' => false,
            'scope' => 'side_menu',
        ]);
    }

    private static function collectMenuNodeIds($menuItem, &$nodeIds)
    {
        if (isset($menuItem['item']['node_id'])) {
            $nodeIds[] = (int)$menuItem['item']['node_id'];
        }
        if (!empty($menuItem['has_children']) && !empty($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                self::collectMenuNodeIds($child, $nodeIds);
            }
        }
    }

    /**
     * Voci di menu di una sezione: solo link interni (la sitemap non puo' contenere altri host).
     * @return array<int, array{path: string, lastmod: int}>
     */
    private function getSectionMenuItems($section)
    {
        $this->loadStructure();
        $items = [];
        if ($section === self::SECTION_PAGES) {
            $homeLastmod = 0;
            foreach ($this->menuModified as $modified) {
                $homeLastmod = max($homeLastmod, $modified);
            }
            $items['/'] = ['path' => '/', 'lastmod' => $homeLastmod];
        }
        if (isset($this->menuTrees[$section])) {
            foreach ($this->menuTrees[$section] as $tree) {
                $this->collectMenuItems($tree, $items);
            }
        }
        if ($section === self::SECTION_PAGES) {
            // le voci di Amministrazione trasparente stanno solo nella loro sezione
            $trasparenzaItems = [];
            foreach ($this->menuTrees[self::SECTION_TRASPARENZA] as $tree) {
                $this->collectMenuItems($tree, $trasparenzaItems);
            }
            $items = array_diff_key($items, $trasparenzaItems);
        }
        return array_values($items);
    }

    private function collectMenuItems($menuItem, &$items)
    {
        if (isset($menuItem['item']['url']) && !empty($menuItem['item']['internal'])) {
            $path = '/' . ltrim(trim($menuItem['item']['url']), '/');
            $nodeId = isset($menuItem['item']['node_id']) ? (int)$menuItem['item']['node_id'] : 0;
            if (!isset($items[$path])) {
                $items[$path] = [
                    'path' => $path,
                    'lastmod' => isset($this->menuModified[$nodeId]) ? $this->menuModified[$nodeId] : 0,
                ];
            }
        }
        if (!empty($menuItem['has_children']) && !empty($menuItem['children'])) {
            foreach ($menuItem['children'] as $child) {
                $this->collectMenuItems($child, $items);
            }
        }
    }

    /**
     * @param int[] $nodeIds
     * @return array<int, int> node_id => modified_subnode
     */
    private function fetchModifiedSubnode(array $nodeIds)
    {
        if (empty($nodeIds)) {
            return [];
        }
        $in = $this->db->generateSQLINStatement($nodeIds, 'node_id', false, false, 'int');
        $result = [];
        foreach ($this->db->arrayQuery("SELECT node_id, modified_subnode FROM ezcontentobject_tree WHERE $in") as $row) {
            $result[(int)$row['node_id']] = (int)$row['modified_subnode'];
        }
        return $result;
    }

    /**
     * @param int[] $nodeIds
     * @return string[]
     */
    private function fetchPathStrings(array $nodeIds)
    {
        if (empty($nodeIds)) {
            return [];
        }
        $in = $this->db->generateSQLINStatement($nodeIds, 'node_id', false, false, 'int');
        return array_column($this->db->arrayQuery("SELECT path_string FROM ezcontentobject_tree WHERE $in"), 'path_string');
    }

    /**
     * FROM/WHERE della query dei contenuti di una sezione, null se la sezione non ha contenuti da query.
     * @return string|null
     */
    private function getSectionContentQuery($section)
    {
        $this->loadStructure();
        $definition = $this->sections[$section];
        if (empty($definition['roots'])) {
            return null;
        }
        $isFull = self::isFullSitemapEnabled();
        $rootNodeOnly = isset($definition['root_node_only']) ? $definition['root_node_only'] : null;
        if (!$isFull && $rootNodeOnly === null) {
            return null;
        }

        $db = $this->db;
        $like = function ($pathString) use ($db) {
            return "t.path_string LIKE '" . $db->escapeString($pathString) . "%'";
        };

        $where = [];
        if (!$isFull) {
            $where[] = 't.node_id = ' . (int)$rootNodeOnly;
        } else {
            $where[] = '(' . implode(' OR ', array_map($like, $definition['roots'])) . ')';
        }
        if (!empty($definition['exclude'])) {
            $where[] = 'NOT (' . implode(' OR ', array_map($like, $definition['exclude'])) . ')';
        }

        // sottoalberi vietati da robots.txt, salvo quelli consentiti esplicitamente (es. FAQ in Media)
        $excluded = $this->policy->getExcludedSubtreePathStrings();
        if (!empty($excluded)) {
            $condition = '(' . implode(' OR ', array_map($like, $excluded)) . ')';
            $included = $this->policy->getIncludedSubtreePathStrings();
            if (!empty($included)) {
                $condition = '(' . $condition . ' AND NOT (' . implode(' OR ', array_map($like, $included)) . '))';
            }
            $where[] = 'NOT ' . $condition;
        }

        if (!empty($this->menuNodeIds)) {
            $where[] = $db->generateSQLINStatement($this->menuNodeIds, 't.node_id', true, false, 'int');
        }

        $joins = [];
        $sectionIdentifiers = array_values(array_filter(array_map('trim', (array)OpenPAINI::variable('SitemapSettings', 'SectionIdentifiers', ['standard']))));
        if (empty($sectionIdentifiers)) {
            $sectionIdentifiers = ['standard'];
        }
        $joins[] = 'INNER JOIN ezsection s ON s.id = o.section_id AND ' . $db->generateSQLINStatement(
            array_map(function ($v) use ($db) {
                return "'" . $db->escapeString($v) . "'";
            }, $sectionIdentifiers),
            's.identifier', false, false, false
        );

        $publicStateId = $this->getPrivacyPublicStateId();
        if ($publicStateId !== null) {
            $joins[] = 'INNER JOIN ezcobj_state_link osl ON osl.contentobject_id = o.id AND osl.contentobject_state_id = ' . (int)$publicStateId;
        }

        $allowedClasses = array_values(array_filter(array_map('trim', (array)OpenPAINI::variable('SitemapSettings', 'AllowedClassIdentifiers', []))));
        if (!empty($allowedClasses)) {
            $joins[] = 'INNER JOIN ezcontentclass cc ON cc.id = o.contentclass_id AND cc.version = 0 AND ' . $db->generateSQLINStatement(
                array_map(function ($v) use ($db) {
                    return "'" . $db->escapeString($v) . "'";
                }, $allowedClasses),
                'cc.identifier', false, false, false
            );
        }

        return "FROM ezcontentobject_tree t
                INNER JOIN ezcontentobject o ON o.id = t.contentobject_id AND o.status = 1
                " . implode("\n                ", $joins) . "
                WHERE t.node_id = t.main_node_id
                  AND t.is_hidden = 0
                  AND t.is_invisible = 0
                  AND " . implode("\n                  AND ", $where);
    }

    /**
     * Id dello stato privacy/public; null se il gruppo non esiste (nessun filtro).
     * @return int|null
     */
    private function getPrivacyPublicStateId()
    {
        $group = eZContentObjectStateGroup::fetchByIdentifier('privacy');
        if (!$group instanceof eZContentObjectStateGroup) {
            return null;
        }
        $state = $group->stateByIdentifier('public');
        // gruppo presente ma senza stato public: nessun contenuto e' pubblico per definizione
        return $state instanceof eZContentObjectState ? (int)$state->attribute('id') : -1;
    }

    /**
     * Converte le righe dei contenuti in path (URL alias) con una sola query sugli alias.
     * @return array<int, array{path: string, lastmod: int}>
     */
    private function resolveContentPaths(array $rows)
    {
        $nodePathMap = [];
        $aliasNodeIds = [];
        foreach ($rows as $row) {
            $parts = array_values(array_filter(explode('/', $row['path_string'])));
            array_shift($parts); // nodo 1, radice di sistema
            $parts = array_map('intval', $parts);
            $nodePathMap[(int)$row['node_id']] = $parts;
            foreach ($parts as $nodeId) {
                $aliasNodeIds[$nodeId] = true;
            }
        }
        if (empty($aliasNodeIds)) {
            return [];
        }

        $db = $this->db;
        $actionList = implode(', ', array_map(function ($id) use ($db) {
            return "'" . $db->escapeString("eznode:$id") . "'";
        }, array_keys($aliasNodeIds)));
        $langFilter = trim(eZContentLanguage::languagesSQLFilter('ezurlalias_ml', 'lang_mask'));
        $langOrder = ['action'];
        foreach (eZContentLanguage::prioritizedLanguages() as $language) {
            $langOrder[] = '(lang_mask & ' . (int)$language->attribute('id') . ') DESC';
        }

        $start = microtime(true);
        $aliasRows = $db->arrayQuery(
            "SELECT DISTINCT ON (action) action, text, parent
             FROM ezurlalias_ml
             WHERE ($langFilter)
               AND action IN ($actionList)
               AND is_original = 1
               AND is_alias = 0
             ORDER BY " . implode(', ', $langOrder)
        );
        $this->logQuery('aliases', $start, count($aliasRows));

        $segmentMap = [];
        foreach ($aliasRows as $aliasRow) {
            $nodeId = (int)substr($aliasRow['action'], 7); // strlen('eznode:')
            $segmentMap[$nodeId] = ['text' => $aliasRow['text'], 'parent' => (int)$aliasRow['parent']];
        }

        $result = [];
        foreach ($rows as $row) {
            $segments = [];
            foreach ($nodePathMap[(int)$row['node_id']] as $nodeId) {
                if (!isset($segmentMap[$nodeId])) {
                    continue;
                }
                if ($segmentMap[$nodeId]['parent'] === 0) {
                    $segments = []; // mount point, come fetchPathByActionList
                }
                $segments[] = $segmentMap[$nodeId]['text'];
            }
            if (empty($segments)) {
                continue;
            }
            $result[] = ['path' => '/' . implode('/', $segments), 'lastmod' => (int)$row['modified']];
        }
        return $result;
    }

    private function logQuery($label, $start, $rows)
    {
        $ms = round((microtime(true) - $start) * 1000, 1);
        $this->stats['queries'][] = "$label {$ms}ms {$rows} righe";
        eZLog::write(
            sprintf('host=%s query=%s ms=%s rows=%d', $this->policy->getHost(), $label, $ms, $rows),
            'sitemap.log'
        );
    }

    /**
     * Cache lato server per host/siteaccess/chiave con TTL CacheTTL.
     * @param string $key
     * @param callable $generate
     * @return string
     */
    private function cached($key, $generate)
    {
        $ttl = self::getCacheTTL();
        if ($ttl === 0) {
            return $generate();
        }
        $current = eZSiteAccess::current();
        $siteaccess = isset($current['name']) ? $current['name'] : 'default';
        $hash = md5($this->policy->getScheme() . '://' . $this->policy->getHost() . '|' . $key);
        $path = eZDir::path([eZSys::cacheDirectory(), 'openpa_sitemap', $siteaccess, $hash . '.xml']);

        // binarydata: il file contiene la stringa cosi' com'e', sia in lettura sia in generazione
        $content = eZClusterFileHandler::instance($path)->processCache(
            function ($file, $mtime, $args) {
                $data = @file_get_contents($file);
                return is_string($data) ? $data : new eZClusterFileFailure(1, 'sitemap cache non leggibile');
            },
            function ($file, $args) use ($generate) {
                return ['binarydata' => (string)$generate(), 'scope' => 'openpa_sitemap'];
            },
            $ttl
        );

        return is_string($content) ? $content : $generate();
    }
}
