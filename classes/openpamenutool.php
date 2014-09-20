<?php

class OpenPAMenuTool
{
    const CACHE_IDENTIFIER = 'openpamenu';
    
    const TOPMENU = 'topmenu';
    const LEFTMENU = 'leftmenu';
    const TREEMENU = 'treemenu';

    protected static $currentUser;
    
    public static function getLeftMenu( $parameters )
    {        
        return self::getMenu( self::LEFTMENU, $parameters );
    }
    
    public static function getTopMenu( $parameters )
    {        
        return self::getMenu( self::TOPMENU, $parameters );
    }
    
    public static function refreshMenu( $id = null, $siteAccess = null, $file = null )
    {
        if ( empty( $id ) && empty( $siteAccess ) && empty( $file ) )
        {
            $newList = array();
            eZCache::clearByTag( self::CACHE_IDENTIFIER );
        }
        else
        {
            $list = self::listCachedItems();
            $newList = $list;
            $remove = array();
            if ( isset( $list[$id] ) )
            {
                foreach( $list[$id] as $listSitaccess => $items )
                {
                    foreach( $items as $index => $item )
                    {                    
                        if ( $siteAccess )
                        {                        
                            if ( $listSitaccess == $siteAccess )
                            {                            
                                if ( $file )
                                {                                                                
                                    if ( basename( $item['cache_file'] ) == $file )
                                    {
                                        unset( $newList[$id][$siteAccess][$index] );
                                        $remove[] = $item['cache_file'];
                                    }
                                }
                                else
                                {
                                    unset( $newList[$id][$siteAccess][$index] );
                                    $remove[] = $item['cache_file'];    
                                }
                            }                        
                        }                    
                        else
                        {
                            unset( $newList[$id] );
                            $remove[] = $item['cache_file'];
                        }
                    }
                }            
            }        
            foreach( $remove as $fileToRemove )
            {
                $cacheFileHandler = eZClusterFileHandler::instance( $fileToRemove  );
                if ( $cacheFileHandler->exists() )
                {
                    $data = $cacheFileHandler->delete();
                }            
            }
        }
        self::listCachedItems( $newList );
    }
    
    public static function generateAllMenus()
    {        
        eZCache::clearByTag( 'template' );
        
        //$cli = eZCLI::instance();
        //$cli->output( 'Svuoto i topmenu di ' . self::currentSiteaccessName());
        self::refreshMenu( self::TOPMENU, self::currentSiteaccessName() );
        
        //$cli->output( 'Svuoto i leftmenu di ' . self::currentSiteaccessName() );
        self::refreshMenu( self::LEFTMENU, self::currentSiteaccessName() );
        
        $menuItems = OpenPAINI::variable( 'TopMenu', 'NodiCustomMenu');
        
        if ( empty( $menuItems ) )
        {
            $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            $rootNode = eZContentObjectTreeNode::fetch( $rootNodeId );
            $menuItems = $rootNode->subTree( array( 'ClassFilterType' => 'include',
                                               'ClassFilterArray' => OpenPAINI::variable( 'TopMenu', 'IdentificatoriMenu' ),
                                               'Limit' => OpenPAINI::variable( 'TopMenu', 'LimitePrimoLivello' ),
                                               'SortBy' => $rootNode->attribute( 'sort_array' ),
                                               'Depth' => 1,
                                               'DepthOperator' => 'eq' ) );
        }
        $areeContainers = OpenPAINI::variable( 'TopMenu', 'NodiAreeCustomMenu');
        foreach( $menuItems as $index => $item )
        {
            $position = array();
            if ( $index == 0 )
            {
                $position = array( 'firstli' );
            }
            if ( $index == count( $menuItems ) - 1 )
            {
                $position = array( 'lastli' );
            }
            $itemNodeid = $item;
            if ( $item instanceof eZContentObjectTreeNode )
            {
                $itemNodeid = $item->attribute( 'node_id' );    
            }
            
            $params = array( 'root_node_id' => $itemNodeid,
                             'position' => $position );
            
            if ( in_array( $itemNodeid, $areeContainers ) )
            {
                if ( $item instanceof eZContentObjectTreeNode )
                {
                    $itemNode = $item;
                }
                else
                {
                    $itemNode = eZContentObjectTreeNode::fetch( $itemNodeid );
                }
                
                if ( $itemNode instanceof eZContentObjectTreeNode )
                {
                    self::suAnonymous();
                    
                    //$cli->output( 'Genero il topmenu del nodo ' . $itemNodeid );
                    self::getTopMenu( $params );
                    
                    $anonymousUserID = eZINI::instance()->variable( 'UserSettings', 'AnonymousUserID' );        
                    $anonymousUser = eZUser::fetch( $anonymousUserID );
                    $userHash = implode( ',', $anonymousUser->attribute( 'role_id_list' ) ) . ',' . implode( ',', $anonymousUser->attribute( 'limited_assignment_value_list' ) );
                    
                    $aree = $itemNode->subTree( array( 'ClassFilterType' => 'include',
                                                       'ClassFilterArray' => OpenPAINI::variable( 'AreeTematiche', 'IdentificatoreAreaTematica' ),
                                                       'Depth' => 1,
                                                       'DepthOperator' => 'eq' ) );
                    foreach( $aree as $area )
                    {
                        $areaParams = array( 'root_node_id' => $area->attribute( 'node_id' ), 'user_hash' => $userHash );
                        //$cli->output( 'Genero il leftmenu anonimo dell\'area tematica ' . $area->attribute( 'node_id' ) );
                        self::getLeftMenu( $areaParams );  
                    }
                    
                    self::exitAnonymous();
                }
            }
            else
            {                
                //$cli->output( 'Genero il topmenu del nodo ' . $itemNodeid );
                self::getTopMenu( $params );  
                
                //$cli->output( 'Genero il leftmenu del nodo ' . $itemNodeid );
                self::getLeftMenu( $params );  
            }
        }        
    }
    
    public static function cacheDirectory()
    {
        $siteINI = eZINI::instance();
        $items = $siteINI->variable( 'Cache', 'CacheItems' );
        if ( in_array( self::CACHE_IDENTIFIER, $items ) &&  $siteINI->hasGroup( 'Cache_' . self::CACHE_IDENTIFIER ))
        {            
            $settings = $siteINI->group( 'Cache_' . self::CACHE_IDENTIFIER );
            if ( isset( $settings['path'] ) )
            {
                $path = rtrim( eZSys::cacheDirectory(), '/' ) . '/' . rtrim( $settings['path'], '/' ) . '/';
                    
                eZDir::mkdir( $path, false, true );
                return $path;
            }
        }
        return false;
    }
    
    public static function listCachedItems( $newData = false )
    {
        $cacheMenu = self::cacheDirectory() . 'openpamenu.php';
        $cacheFileHandler = eZClusterFileHandler::instance( $cacheMenu  );
        if ( $cacheFileHandler->exists() )
        {
            $data = $cacheFileHandler->fetchContents();
        }
        else
        {
            $data = serialize( array() );
        }
        
        $data = unserialize( $data );
        
        if ( $newData )
        {            
            $cacheFileHandler->storeContents( serialize( $newData ) );
        }
        
        return $data;
    }    
    
    protected static function registerCachedMenu( $id, $siteaccess, $values )
    {
        $data = self::listCachedItems();
        
        if ( !isset( $data[$id] ) )
        {
            $data[$id] = array();
        }
        
        if ( !isset( $data[$id][$siteaccess] ) )
        {
            $data[$id][$siteaccess] = array();
        }
        
        foreach( $data[$id][$siteaccess] as $item )
        {
            if( $item === $values )
            {
                return;  
            }
        }
        $data[$id][$siteaccess][] = $values;
        
        self::listCachedItems( $data );
    }
    
    protected static function currentSiteaccessName()
    {
        $siteaccess = eZSiteAccess::current();
        return $siteaccess['name'];
    }
    
    protected static function suAnonymous()
    {
        $anonymousUserID = eZINI::instance()->variable( 'UserSettings', 'AnonymousUserID' );
        self::$currentUser = eZUser::currentUser();
        $anonymousUser = eZUser::fetch( $anonymousUserID );
        if ( $anonymousUser instanceof eZUser )
        {
            eZUser::setCurrentlyLoggedInUser( $anonymousUser, $anonymousUser->attribute( 'contentobject_id' ) );
        }
    }
    
    protected static function exitAnonymous()
    {
        if ( self::$currentUser instanceof eZUser )
        {
            eZUser::setCurrentlyLoggedInUser( self::$currentUser, self::$currentUser->attribute( 'contentobject_id' ) );
        }
    }
        
    protected static function notice( $message )
    {
        eZCLI::instance()->notice( $message );
    }
    
    protected static function getMenu( $identifier, $parameters )
    {
        $cachePath = self::cacheDirectory() . self::currentSiteaccessName() . '/';        
        if ( $cachePath )
        {            
            if ( isset( $parameters['root_node_id'] ) )
                $rootNodeId = $parameters['root_node_id'];
            else
                $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            
            $extraCacheKey = '';
            if ( isset( $parameters['user_hash'] ) && $parameters['user_hash'] != false )
            {
                $extraCacheKey = md5( $parameters['user_hash'] );
                $parameters['user_hash'] = $extraCacheKey;
            }

            if ( $identifier == self::TREEMENU )
            {
                $cacheFile = $cachePath . $identifier . '_' . $rootNodeId . $extraCacheKey . '.cache';
            }
            else
            {
                $cacheFile = $cachePath . $identifier . '_' . $rootNodeId . $extraCacheKey . '.html';
            }
            $cacheFileHandler = eZClusterFileHandler::instance( $cacheFile );            
            if ( !$cacheFileHandler->exists() )
            {            
                if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false  ) self::suAnonymous();
                
                $contents = self::generateMenu( $identifier, $parameters );
                
                if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false )  self::exitAnonymous();
                
                $parameters['cache_file'] = $cacheFile;
                
                self::registerCachedMenu( $identifier, self::currentSiteaccessName(), $parameters );
                
                $cacheFileHandler->storeContents( $contents );
            }
            return $cacheFileHandler->fetchContents();
        }
        else
        {
            eZDebug::writeWarning( 'Topmenu generated without cache' );
            return self::generateMenu( $identifier, $parameters );
        }
    }
        
    protected static function generateMenu( $identifier, &$parameters )
    {        
        if ( $identifier == self::TREEMENU )
        {
            $settingsScope = $parameters['scope'];
            $handlerObject = OpenPAObjectHandler::instanceFromObject( null );
            $classIdentifiers = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope . '_classes' );
            $excludeNodes = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope . '_exclude' );
            $limits  = $handlerObject->attribute( 'control_menu' )->attribute( $settingsScope . '_limits' );

            $settings = array(
                'limit' => $limits,
                'class_identifiers' => $classIdentifiers,
                'exclude_node_ids' => $excludeNodes

            );
            return serialize( self::treeMenu( $parameters['root_node_id'], $settings ) );
        }
        else
        {
            if ( !isset( $parameters['root_node_id'] ) )
                $parameters['root_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );

            if ( !isset( $parameters['template'] ) )
                $parameters['template'] = 'menu/cached/' . $identifier . '.tpl';

            $extraNotice = '';
            if ( isset( $parameters['user_hash'] ) && $parameters['user_hash'] != false  )
            {
                $extraNotice = ' with user_hash key';
            }
            eZDebug::writeNotice( 'Generate ' . $identifier . ' on node ' . $parameters['root_node_id'] . $extraNotice );

            $tpl = eZTemplate::factory();
            foreach( $parameters as $key => $value )
            {
                $tpl->setVariable( $key, $value );
            }
            return $tpl->fetch( 'design:' . $parameters['template'] );
        }
    }
    
    public static function printDebugReport( $as_html = true )
    {
        if ( !eZTemplate::isTemplatesUsageStatisticsEnabled() )
            return '';

        $stats = '';
        if ( $as_html )
        {
            $stats .= '<h3>OpenPA Cache menu:</h3>';
            $stats .= '<table id="openpacachemenu" class="debug_resource_usage" title="Lista dei menu in cache">';
            $list = self::listCachedItems();                        
            $stats .= '<tr><th>Siteacces</th><th>Parametri</th><th></th></tr>';
            foreach( $list as $id => $data )
            {                
                $stats .= "<tr><th colspan=\"2\">{$id}</th><th><a href=\"/openpa/refreshmenu/{$id}/\">Svuota</a></th></tr>";
                foreach( $data as $siteaccess => $items )
                {
                    foreach( $items as $item )
                    {
                        $values = array();
                        $file = basename( $item['cache_file'] );
                        unset( $item['cache_file'] );
                        foreach( $item as $key => $value )
                        {
                            $values[] = is_object( $value ) || is_array( $value ) ? "$key: (" . gettype( $value ) . ")" : "$key: " . $value;
                        }
                        $valueString = implode( '; ', $values );
                        $stats .= "<tr class='data'><td>{$siteaccess}</td><td>{$valueString}</td><td><a href=\"/openpa/refreshmenu/{$id}/{$siteaccess}/{$file}\">Svuota</a></td></tr>";
                    }
                }                
            }
            $stats .= '</table>';
        }

        return $stats;
    }

    public static function getTreeMenu( $parameters )
    {
        return unserialize( self::getMenu( self::TREEMENU, $parameters ) );
    }

    public static function treeMenu( $rootNodeId, $settings )
    {
        $data = array();
        $rootNode = OpenPABase::fetchNode( $rootNodeId );
        if ( $rootNode instanceof eZContentObjectTreeNode )
        {
            $data = self::treeMenuItem( $rootNode, $settings, 0 );
        }
        return $data;
    }

    protected static function treeMenuItem( eZContentObjectTreeNode $rootNode, array $settings, $level )
    {
        $handlerObject = OpenPAObjectHandler::instanceFromObject( $rootNode );
        $menuItem = array();
        $menuItem['item'] = array(
            'node_id' => $rootNode->attribute( 'node_id' ),
            'name' => $rootNode->attribute( 'name' ),
            'url' => $handlerObject->attribute( 'content_link' )->attribute( 'link' ),
            'internal' => $handlerObject->attribute( 'content_link' )->attribute( 'is_internal' ),
            'target' => $handlerObject->attribute( 'content_link' )->attribute( 'target' )
        );
        $menuItem['level'] = $level;
        $menuItem['children'] = array();
        if ( $level < 4 )
        {
            $nodes = eZFunctionHandler::execute(
                'content',
                'list',
                array(
                     'parent_node_id' => $rootNode->attribute( 'node_id' ),
                     'sort_by' => $rootNode->attribute( 'sort_array' ),
                     'data_map_load' => false,
                     'limit' => isset( $settings['limit']['level_' . $level] ) ? $settings['limit']['level_' . $level] : null,
                     'class_filter_type' => 'include',
                     'class_filter_array' => $settings['class_identifiers']
                )
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

eZDebug::appendBottomReport( 'OpenPAMenuTool', array( 'OpenPAMenuTool', 'printDebugReport' ) );