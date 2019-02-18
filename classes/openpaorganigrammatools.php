<?php


class OpenPAOrganigrammaTools
{
    private static $_instance;

    protected $nodeList = array();

    /**
     * @var OpenPAOrganigrammaItem
     */
    protected $tree;

    /**
     * @var OpenPAOrganigrammaSubItemCollection
     */
    protected $subItemCollection;

    public static $preventRepetitions = array();

    public static $serviziNonAttiviIdList;

    public static function instance()
    {
        if (self::$_instance === null) {
            $language = eZLocale::currentLocaleCode();
            $cacheFilePath = eZSys::cacheDirectory() . "/openpa/organigramma/$language.php";
            $cacheFile = eZClusterFileHandler::instance($cacheFilePath);

            $tree = $cacheFile->processCache(
                array('OpenPAOrganigrammaTools', 'cacheRetrieve'),
                array('OpenPAOrganigrammaTools', 'cacheGenerate'),
                null,
                self::cacheExpiry()
            );

            self::$_instance = new OpenPAOrganigrammaTools();
            self::$_instance->tree = $tree;
        }

        return self::$_instance;
    }

    public static function cacheExpiry()
    {
        return null; //@todo
    }

    public static function clearCache()
    {
        $language = eZLocale::currentLocaleCode();
        $cacheFilePath = eZSys::cacheDirectory() . "/openpa/organigramma/$language.php";
        $cacheFile = eZClusterFileHandler::instance($cacheFilePath);
        if ($cacheFile->exists()){
            $cacheFile->delete();
            $cacheFile->purge();
        }
    }

    public static function cacheGenerate()
    {
        $tools = new OpenPAOrganigrammaTools();
        $data = $tools->run();

        return array(
            'content' => $data,
            'scope' => 'organigramma-cache'
        );
    }

    public static function cacheRetrieve($file, $mtime)
    {
        $data = include( $file );

        return $data;
    }

    protected function __construct()
    {
        $this->nodeList = array(
            'CustomNodes' => OpenPAINI::variable('Nodi', 'OrganigrammaCustomNodes', array()),
            'ServiziIndipendenti' => OpenPAINI::variable('Nodi', 'ServiziIndipendenti', 0),
            'Aree' => OpenPAINI::variable('Nodi', 'Aree', 0),
        );
    }

    public function setNodeList($nodeList = array())
    {
        $this->nodeList = $nodeList;
    }

    public function run()
    {
        self::$preventRepetitions = array();
        $this->tree = OpenPAOrganigrammaItem::instanceFromNode(
            OpenPaFunctionCollection::fetchHome(),
            array('build' => false));
        $this->tree->name = eZINI::instance()->variable('SiteSettings', 'SiteName');
        $this->subItemCollection = new OpenPAOrganigrammaSubItemCollection();

        foreach ($this->nodeList as $identifier => $nodeId) {
            $this->runOnNode($identifier, $nodeId);
        }

        $this->tree->appendSubItemCollection($this->subItemCollection);

        return $this->tree;
    }

    protected function extractSubtreeById($id)
    {
        if (!$this->tree instanceof OpenPAOrganigrammaItem) {
            $this->run();
        }

        if (in_array($id, $this->tree->itemIdList)) {
            $container = clone $this->tree;
            $subItems = new OpenPAOrganigrammaSubItemCollection();
            foreach ($this->tree->items as $item) {
                $this->findRecursive($id, $item, $subItems);
            }
            if ($subItems->hasContent()) {
                $container->appendSubItemCollection($subItems);
            }

            return $container;
        }

        return null;

    }

    public function tree($id = null)
    {
        if (!$id) {
            return $this->tree;
        } else {
            return $this->extractSubtreeById($id);
        }
    }

    protected function findRecursive(
        $id,
        OpenPAOrganigrammaSubItemCollection $current,
        OpenPAOrganigrammaSubItemCollection $container
    ) {
        if (in_array($id, $current->itemIdList())) {
            foreach ($current->items as $item) {
                if (in_array($id, $item->itemIdList)) {
                    $clone = clone $item;
                    $subItems = new OpenPAOrganigrammaSubItemCollection();
                    foreach ($item->items as $newCurrent) {
                        $this->findRecursive($id, $newCurrent, $subItems);
                    }
                    if ($subItems->hasContent()) {
                        $clone->appendSubItemCollection($subItems);
                    } else {
                        $clone = $item;
                    }
                    $container->append($clone);
                }
            }
        }

        return $container;
    }

    protected function runOnNode($identifier, $nodeId)
    {
        if (!empty( $nodeId )) {
            switch ($identifier) {

                case 'CustomNodes':
                    /** @var eZContentObjectTreeNode[] $nodes */
                    $nodes = eZContentObjectTreeNode::fetch($nodeId);
                    foreach ($nodes as $node) {
                        $this->appendRootItem($node, array('build' => false));
                    }
                    break;

                case 'ServiziIndipendenti':
                    /** @var eZContentObjectTreeNode[] $nodes */
                    $nodes = eZContentObjectTreeNode::subTreeByNodeID(array(
                        array(
                            'ClassFilterType' => 'include',
                            'ClassFilterArray' => array('servizio'),
                            'SortBy' => array('priority', true)
                        )
                    ), $nodeId);
                    foreach ($nodes as $node) {
                        $this->appendRootItem(
                            $node->object()->mainNode()
                        );
                    }
                    break;

                case 'Aree':
                    /** @var eZContentObjectTreeNode[] $nodes */
                    $nodes = eZContentObjectTreeNode::subTreeByNodeID(
                        array(
                            'ClassFilterType' => 'include',
                            'ClassFilterArray' => array('servizio', 'area'),
                            'SortBy' => array('priority', true)
                        ),
                        $nodeId
                    );
                    foreach ($nodes as $node) {
                        $item = $this->appendRootItem(
                            $node->object()->mainNode()
                        );

                        $subItems = new OpenPAOrganigrammaSubItemCollection();
                        $subItems->identifier = 'servizio_area';
                        $attributeId = eZContentObjectTreeNode::classAttributeIDByIdentifier('servizio/area');
                        /** @var eZContentObject[] $serviziPerArea */
                        $serviziPerArea = $node->object()->reverseRelatedObjectList(
                            false,
                            $attributeId,
                            false,
                            array('SortBy' => array('name', true))
                        );
                        foreach ($serviziPerArea as $servizio) {
                            if ($servizio->attribute('id') != $item->id) {
                                /** @var eZContentObjectTreeNode $servizioNode */
                                $servizioNode = $servizio->mainNode();
                                $subItems->append(OpenPAOrganigrammaItem::instanceFromNode($servizioNode));
                            }
                        }
                        if (!empty( $subItems )) {
                            $item->appendSubItemCollection($subItems);
                        }
                    }
                    break;
            }
        }
    }

    protected function appendRootItem(eZContentObjectTreeNode $node, $settings = array())
    {
        $item = OpenPAOrganigrammaItem::instanceFromNode($node, $settings);
        $this->subItemCollection->append($item);

        return $item;
    }

    public static function getServiziNonAttiviIdList()
    {
        if (self::$serviziNonAttiviIdList === null){
            self::$serviziNonAttiviIdList = array();
            $serviziNonAttiviRootNodeId = OpenPAINI::variable('Nodi', 'ServiziNonAttivi', 0);
            if ($serviziNonAttiviRootNodeId > 0){
                $serviziNonAttiviRootNode = eZContentObjectTreeNode::fetch($serviziNonAttiviRootNodeId);
                if ($serviziNonAttiviRootNode instanceof eZContentObjectTreeNode){
                    /** @var eZContentObjectTreeNode[] $children */
                    $children = $serviziNonAttiviRootNode->children();
                    foreach ($children as $child){
                        self::$serviziNonAttiviIdList[] = $child->attribute('contentobject_id');
                    }
                }
            }

        }

        return self::$serviziNonAttiviIdList;
    }
}

class OpenPAOrganigrammaItem
{
    public $id;

    public $node_id;

    public $name;

    public $url_alias;

    public $class_identifier;

    public $itemIdList = array();

    /**
     * @var OpenPAOrganigrammaSubItemCollection[]
     */
    public $items = array();

    protected $settings = array();

    public function __clone()
    {
        $this->items = array();
    }

    public static function instanceFromNode(eZContentObjectTreeNode $node, array $settings = array())
    {
        $item = new OpenPAOrganigrammaItem();
        $item->setSettings($settings);
        $item->id = (int)$node->attribute('contentobject_id');
        $item->class_identifier = $node->attribute('class_identifier');
        $item->node_id = (int)$node->attribute('node_id');
        $item->name = $node->attribute('name');
        $item->url_alias = $node->attribute('url_alias');
        $item->collectIdList($item->id);
        $item->build();

        return $item;
    }

    public static function instanceFromArray(array $result, array $settings = array())
    {

        $data = array(
            'id' => $result['metadata']['id'],
            'node_id' => $result['metadata']['mainNodeId'],
            'name' => $result['metadata']['name'][eZLocale::currentLocaleCode()],
            'class_identifier' => $result['metadata']['classIdentifier'],
            'url_alias' => '/content/view/full/' . $result['metadata']['mainNodeId']
        );

        $item = new OpenPAOrganigrammaItem();
        $item->setSettings($settings);
        $item->id = (int)$data['id'];
        $item->class_identifier = $data['class_identifier'];
        $item->node_id = (int)$data['node_id'];
        $item->name = $data['name'];
        $item->url_alias = $data['url_alias'];
        $item->collectIdList($item->id);
        $item->build();

        return $item;
    }

    public static function __set_state(array $data)
    {
        $item = new OpenPAOrganigrammaItem();
        $item->id = (int)$data['id'];
        $item->class_identifier = $data['class_identifier'];
        $item->node_id = (int)$data['node_id'];
        $item->name = $data['name'];
        $item->url_alias = $data['url_alias'];
        $item->itemIdList = $data['itemIdList'];
        $item->items = $data['items'];

        return $item;
    }

    public function setSettings(array $settings)
    {
        $this->settings = array_merge(
            array(
                'build' => true,
                'exclude' => array()
            ),
            $settings
        );
    }

    public function appendSubItemCollection(OpenPAOrganigrammaSubItemCollection $subItems)
    {
        if ($subItems->hasContent()) {
            $this->items[] = $subItems;
            $this->collectIdList($subItems->itemIdList());
        }
    }

    public function collectIdList($itemIdList)
    {
        if (!is_array($itemIdList)) {
            $itemIdList = (array)$itemIdList;
        }
        $this->itemIdList = array_unique(array_merge($this->itemIdList, $itemIdList));
    }

    public function build()
    {
        if ($this->settings['build']) {

            switch ($this->class_identifier) {
                case 'area':
                case 'servizio':
                    $this->buildAreaServizio();
                    break;

                case 'incarico':
                    $this->buildIncarico();
                    break;

                case 'ufficio':
                    $this->buildUfficio();
                    break;

                case 'struttura':
                    $this->buildStruttura();
                    break;

                case 'altra_struttura':
                    $this->buildAltraStruttura();
                    break;
            }


        }
    }

    protected function buildAreaServizio()
    {

        if (!in_array('servizi_correlati', $this->settings['exclude'])) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "servizi_correlati";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and area.id = $this->id classes [servizio] sort [name=>asc] limit 100");
            foreach ($results as $result) {

                $item = OpenPAOrganigrammaItem::instanceFromArray((array)$result, array('exclude' => array('servizi_correlati')));
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

        if (!in_array('incarichi_correlati', $this->settings['exclude']) && eZContentClass::classIDByIdentifier('incarico')) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "incarichi_correlati";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and {$this->class_identifier}.id = $this->id classes [incarico] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

        if (!in_array('uffici_correlati', $this->settings['exclude'])) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "uffici_correlati";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and {$this->class_identifier}.id = $this->id classes [ufficio] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('exclude' => array('ufficio_altre_strutture_correlate'))
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

        if (!in_array('strutture_correlate', $this->settings['exclude'])) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "strutture_correlate";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and {$this->class_identifier}.id = $this->id classes [struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('build' => false)
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

        if (!in_array('altre_strutture_correlate', $this->settings['exclude']) && eZContentClass::classIDByIdentifier('altra_struttura')) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "altre_strutture_correlate";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and {$this->class_identifier}.id = $this->id classes [altra_struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('build' => false)
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }
    }

    protected function buildIncarico()
    {
        $subItems = new OpenPAOrganigrammaSubItemCollection();
        $subItems->identifier = "incarico_uffici_correlati-incarico_altre_strutture_correlate-incarico_strutture_correlate";

        if (!in_array('incarico_uffici_correlati', $this->settings['exclude'])) {
            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and incarico.id = $this->id classes [ufficio] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result
                );
                $subItems->append($item);
            }
        }

        if (!in_array('incarico_strutture_correlate', $this->settings['exclude'])) {
            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and incarico.id = $this->id classes [struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('build' => false)
                );
                $subItems->append($item);
            }
        }

        if (!in_array('incarico_altre_strutture_correlate', $this->settings['exclude']) && eZContentClass::classIDByIdentifier('altra_struttura')) {
            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and incarico.id = $this->id classes [altra_struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('build' => false)
                );
                $subItems->append($item);
            }
        }

        $this->appendSubItemCollection($subItems);
    }

    protected function buildUfficio()
    {

        if (!in_array('ufficio_strutture_correlate', $this->settings['exclude'])) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "ufficio_strutture_correlate";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and ufficio.id = $this->id classes [struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

        if (!in_array('ufficio_altre_strutture_correlate', $this->settings['exclude']) && eZContentClass::classIDByIdentifier('altra_struttura')) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "ufficio_altre_strutture_correlate";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and ufficio.id = $this->id classes [altra_struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

    }

    protected function buildStruttura()
    {

        if (!in_array('strutture_strutture_correlate', $this->settings['exclude'])) {

            $subItems = new OpenPAOrganigrammaSubItemCollection();
            $subItems->identifier = "strutture_strutture_correlate";

            /** @var \Opencontent\Opendata\Api\Values\Content[] $results */
            $results = $this->fetch("id != '$this->id' and struttura.id = $this->id classes [struttura] sort [name=>asc] limit 100");
            foreach ($results as $result) {
                $item = OpenPAOrganigrammaItem::instanceFromArray(
                    (array)$result,
                    array('build' => false)
                );
                $subItems->append($item);
            }

            $this->appendSubItemCollection($subItems);
        }

    }

    protected function buildAltraStruttura()
    {

    }


    protected function fetch($query)
    {
        $result = array();
        try {
            $search = new \Opencontent\Opendata\Api\ContentSearch();
            $search->setEnvironment(new DefaultEnvironmentSettings());
            $searchResults = $search->search($query);
            $result = $searchResults->searchHits;
            eZDebug::writeNotice($query, $searchResults->totalCount);

        } catch (Exception $e) {
            eZDebug::writeNotice('Query error: "' . $e->getMessage() . '" in < ' . $query . ' >', __METHOD__);
        }

        return $result;
    }
}

class OpenPAOrganigrammaSubItemCollection
{
    public $identifier;

    /**
     * @var OpenPAOrganigrammaItem[]
     */
    public $items = array();

    public static function __set_state(array $data)
    {
        $item = new OpenPAOrganigrammaSubItemCollection();
        $item->identifier = $data['identifier'];
        $item->items = $data['items'];

        return $item;
    }

    public function append(OpenPAOrganigrammaItem $item)
    {
        if (!in_array($item->id, OpenPAOrganigrammaTools::$preventRepetitions)){
            if (!in_array($item->id, OpenPAOrganigrammaTools::getServiziNonAttiviIdList())) {
                $this->items[] = $item;
            }
            OpenPAOrganigrammaTools::$preventRepetitions[] = $item->id;
        }

    }

    public function hasContent()
    {
        return count($this->items) > 0;
    }

    public function itemIdList()
    {
        $list = array();
        foreach ($this->items as $item) {
            $list[] = $item->id;
            foreach ($item->items as $subItems) {
                $list = array_merge($list, $subItems->itemIdList());
            }
        }

        return array_unique($list);
    }
}
