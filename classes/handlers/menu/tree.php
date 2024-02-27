<?php

class OpenPATreeMenuHandler implements OpenPAMenuHandlerInterface
{
    protected $identifier = OpenPAMenuTool::TREEMENU;
    protected $parameters;
    protected $rootNodeId;
    protected $userHash;

    public function __construct( $parameters )
    {
        $this->parameters = $parameters;

        if ( isset( $parameters['scope'] ) )
            $this->identifier .= $parameters['scope'];

        $this->parameters['identifier'] = $this->identifier;
        if ( isset( $this->parameters['root_node_id'] ) )
        {
            $this->rootNodeId = $this->parameters['root_node_id'];
        }
        else
        {
            $this->rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            $this->parameters['root_node_id'] = $this->rootNodeId;
        }

        if ( isset( $this->parameters['user_hash'] ) && $this->parameters['user_hash'] != false )
        {
            $this->userHash = $this->parameters['user_hash'];
        }
        $this->parameters['user_hash'] = $this->userHash;
    }

    public function cacheFileName()
    {
        $extraCacheKey = '';
        if ( $this->userHash !== null )
        {
            $extraCacheKey = md5( $this->userHash );
        }
        if (isset($this->parameters['hide_empty_tag_callback']) && $this->parameters['hide_empty_tag_callback']){
            $extraCacheKey .= '_he_';
        }
        return $this->identifier . '_' . $this->rootNodeId . $extraCacheKey . '.cache';
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public static function menuRetrieve( $file, $mtime, $args )
    {
        $result = include( $file );
        return $result;
    }

    public static function menuGenerate( $file, $args )
    {
        extract( $args );
        $result = self::getMenu( $parameters );
        return array( 'content' => $result,
                      'scope'   => OpenPAMenuTool::CACHE_IDENTIFIER );
    }

    public static function getMenu( $parameters )
    {
        if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false  )
        {
            OpenPAMenuTool::suAnonymous();
        }

        $settingsScope = false;
        if ( isset( $parameters['scope'] ) )
            $settingsScope = $parameters['scope'];

        eZDebug::writeNotice( "Generate menu {$settingsScope} for node {$parameters['root_node_id']}", __METHOD__ );

        $rootNode = OpenPABase::fetchNode( $parameters['root_node_id'] );
        if ( !$rootNode instanceof eZContentObjectTreeNode )
        {
            return array();
        }
        $handlerObject = OpenPAObjectHandler::instanceFromObject( $rootNode );

        $classIdentifiers = array();
        $excludeNodes = array();
        $excludeSubtrees = array();
        $limits = array();
        $maxRecursion = 10;
        $fetchParameters = array();
        $customMaxRecursion = array();
        if ( $handlerObject->hasAttribute( 'control_menu' )
             && in_array( $settingsScope, $handlerObject->attribute( 'control_menu' )->attribute( 'available_menu' ) ) )
        {
            $classIdentifiers = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'classes' );
            $excludeNodes = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'exclude' );
            $excludeSubtrees = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'exclude_tree' );
            $limits  = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'limits' );
            $maxRecursion  = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'max_recursion' );
            $fetchParameters  = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'custom_fetch_parameters' );
            $customMaxRecursion  = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope )->attribute( 'custom_max_recursion' );
        }

        $settings = array(
            'limit' => $limits,
            'class_identifiers' => $classIdentifiers,
            'exclude_node_ids' => $excludeNodes,
            'exclude_subtree_ids' => $excludeSubtrees,
            'max_recursion' => $maxRecursion,
            'custom_fetch_parameters' => $fetchParameters,
            'custom_max_recursion' => $customMaxRecursion,
            'scope' => $parameters['scope'],
            'hide_empty_tag' => $parameters['hide_empty_tag'] ?? false,
            'hide_empty_tag_callback' => $parameters['hide_empty_tag_callback'] ?? false,
        );
        $result = self::treeMenu( $parameters['root_node_id'], $settings );
        if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false  )
        {
            OpenPAMenuTool::exitAnonymous();
        }
        return $result;
    }

    protected static function treeMenu( $rootNodeId, $settings )
    {
        $data = array();
        $rootNode = OpenPABase::fetchNode( $rootNodeId );
        if ( $rootNode instanceof eZContentObjectTreeNode )
        {
            $data = self::treeMenuItem( $rootNode, $settings, 0 );
        }
        return $data;
    }

    protected static function treeMenuItem( eZContentObjectTreeNode $rootNode, array &$settings, $level )
    {
        $handlerObject = OpenPAObjectHandler::instanceFromObject( $rootNode );

        if ($rootNode->attribute('node_id') == OpenPaFunctionCollection::fetchHome()->attribute('node_id'))
        {
            $sideMenuContextRootClasses = OpenPAINI::variable( 'SideMenu', 'SideMenuContextRootClasses', array() );
            if ( $settings['scope'] == 'side_menu'
                 && !in_array( $rootNode->attribute('class_identifier'), $sideMenuContextRootClasses )
            ){
                $settings['max_recursion'] = 1;
            }
        }

        if ( isset( $settings['custom_max_recursion'][$rootNode->attribute( 'node_id' )] ) )
        {
            $settings['max_recursion'] = $settings['custom_max_recursion'][$rootNode->attribute( 'node_id' )];
        }

        $menuItem = array(
            'item' => array(
                'node_id' => $rootNode->attribute( 'node_id' ),
                'remote_id' => $rootNode->object()->attribute( 'remote_id' ),
                'name' => $rootNode->attribute( 'name' ),
                'url' => $handlerObject->attribute( 'content_link' )->attribute( 'link' ),
                'internal' => $handlerObject->attribute( 'content_link' )->attribute( 'is_internal' ),
                'target' => $handlerObject->attribute( 'content_link' )->attribute( 'target' ),
            ),
            'max_recursion' => $settings['max_recursion'],
            'level' => $level,
            'children' => array()
        );

        if ($handlerObject->hasAttribute('content_tag_menu')){
            if ($handlerObject->attribute('content_tag_menu')->attribute('has_tag_menu')){

                /** @var eZTagsObject $tagRoot */
                $tagRoot = $handlerObject->attribute('content_tag_menu')->attribute('tag_menu_root');
                if ($tagRoot instanceof eZTagsObject) {
                    $level++;
                    $menuItem['has_children'] = $tagRoot->getChildrenCount();
                    foreach ($tagRoot->getChildren() as $child){
                        $append = true;
                        if ($settings['hide_empty_tag'] && is_callable($settings['hide_empty_tag_callback'])){
                            $append = call_user_func($settings['hide_empty_tag_callback'], $child);
                        }
                        if ($append) {
                            $menuItem['children'][] = [
                                'item' => [
                                    'node_id' => $rootNode->attribute('node_id') . '-' . $child->attribute('id'),
                                    'remote_id' => $child->attribute('remote_id'),
                                    'name' => $child->attribute('keyword'),
                                    'url' => $handlerObject->attribute('content_link')->attribute('link')
                                        . '/(view)/' . $child->attribute('keyword'),
                                    'internal' => $handlerObject->attribute('content_link')->attribute('is_internal'),
                                    'target' => $handlerObject->attribute('content_link')->attribute('target'),
                                ],
                                'max_recursion' => $settings['max_recursion'],
                                'level' => $level,
                                'children' => []
                            ];
                        }
                    }
                }

                return $menuItem;
            }
        }

        $fetchChildren = $level < $settings['max_recursion'];
        if ( $fetchChildren && !in_array( $rootNode->attribute( 'node_id' ), $settings['exclude_subtree_ids'] ) )
        {
            $childrenLimit = isset( $settings['limit']['level_' . $level] ) ? $settings['limit']['level_' . $level] : null;

            $childrenFetchParameters = array();
            if ( isset( $settings['custom_fetch_parameters'][$rootNode->attribute( 'node_id' )] ) )
            {
                $childrenFetchParameters = $settings['custom_fetch_parameters'][$rootNode->attribute( 'node_id' )];
            }

            $module = 'openpa';
            if ( OpenPAINI::variable( 'Menu', 'IgnoraVirtualizzazione', 'disabled' ) == 'enabled' )
            {
                $module = 'content';
            }
            elseif ( in_array( $rootNode->attribute( 'node_id' ), OpenPAINI::variable( 'Menu', 'IgnoraVirtualizzazioneNodi', array() ) ) )
            {
                $module = 'content';
            }

            /** @var eZContentObjectTreeNode[] $nodes */
            $nodes = eZFunctionHandler::execute(
                $module,
                'list',
                array_merge( array(
                    'parent_node_id' => $rootNode->attribute( 'node_id' ),
                    'sort_by' => $rootNode->attribute( 'sort_array' ),
                    'data_map_load' => false,
                    'limit' => $childrenLimit,
                    'class_filter_type' => 'include',
                    'class_filter_array' => $settings['class_identifiers'],
                ), $childrenFetchParameters )
            );
            if ( count( $nodes ) > 0 )
            {
                $level++;
                foreach( $nodes as $node )
                {
                    if ( !in_array( $node->attribute( 'node_id' ), $settings['exclude_node_ids'] ) )
                    {
                        $menuItem['children'][] = self::treeMenuItem( $node, $settings, $level );
                    }
                }
            }
        }
        $menuItem['has_children'] = count( $menuItem['children'] ) > 0;
        return $menuItem;
    }

}