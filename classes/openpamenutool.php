<?php

class OpenPAMenuTool
{
    const CACHE_IDENTIFIER = 'openpamenu';
    
    const TOPMENU = 'topmenu';
    const LEFTMENU = 'leftmenu';
    
    protected static $currentUser;
    
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
    
    public static function getTopMenu( $parameters )
    {        
        $cachePath = self::cacheDirectory() . self::currentSiteaccessName() . '/';        
        if ( $cachePath )
        {
            $cacheFile = $cachePath . self::TOPMENU . '.html';
            $cacheFileHandler = eZClusterFileHandler::instance( $cacheFile );            
            if ( !$cacheFileHandler->exists() )
            {            
                self::suAnonymous();
                $contents = self::generateTopMenu( $parameters );
                self::exitAnonymous();
                $parameters['cache_file'] = $cacheFile;
                self::registerCachedMenu( self::TOPMENU, self::currentSiteaccessName(), $parameters );
                $cacheFileHandler->storeContents( $contents );
            }
            return $cacheFileHandler->fetchContents();
        }
        else
        {
            eZDebug::writeWarning( 'Topmenu generated without cache' );
            return self::generateTopMenu( $parameters );
        }
    }
    
    protected static function generateTopMenu( &$parameters )
    {        
        eZDebug::writeNotice( 'Generate ' .  self::TOPMENU );
        
        if ( !isset( $parameters['root_node_id'] ) )
            $parameters['root_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
        
        if ( !isset( $parameters['template'] ) )
            $parameters['template'] = 'menu/cached/' . self::TOPMENU . '.tpl';
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'root_node_id', $parameters['root_node_id'] );
        return $tpl->fetch( 'design:' . $parameters['template'] );
    }
    
    protected static function notice( $message )
    {
        eZCLI::instance()->notice( $message );
    }    
    
    public static function getLeftMenu( $parameters )
    {        
        $cachePath = self::cacheDirectory() . self::currentSiteaccessName() . '/';        
        if ( $cachePath )
        {            
            if ( isset( $parameters['root_node_id'] ) )
                $rootNodeId = $parameters['root_node_id'];
            else
                $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            $cacheFile = $cachePath . self::LEFTMENU . '_' . $rootNodeId . '.html';            
            $cacheFileHandler = eZClusterFileHandler::instance( $cacheFile );            
            if ( !$cacheFileHandler->exists() )
            {            
                self::suAnonymous();
                $contents = self::generateLeftMenu( $parameters );
                self::exitAnonymous();
                $parameters['cache_file'] = $cacheFile;
                self::registerCachedMenu( self::LEFTMENU, self::currentSiteaccessName(), $parameters );
                $cacheFileHandler->storeContents( $contents );
            }
            return $cacheFileHandler->fetchContents();
        }
        else
        {
            eZDebug::writeWarning( 'Topmenu generated without cache' );
            return self::generateTopMenu( $parameters );
        }
    }
    
    protected static function generateLeftMenu( &$parameters )
    {        
        eZDebug::writeNotice( 'Generate ' . self::LEFTMENU );
        
        if ( !isset( $parameters['root_node_id'] ) )
            $parameters['root_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
        
        if ( !isset( $parameters['template'] ) )
            $parameters['template'] = 'menu/cached/' . self::LEFTMENU . '.tpl';
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( 'root_node_id', $parameters['root_node_id'] );
        return $tpl->fetch( 'design:' . $parameters['template'] );
    }
    
    public static function refreshMenu( $id, $siteAccess = null, $file = null )
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
        self::listCachedItems( $newList );
    }
    
    public static function generateAllMenus()
    {        
        self::notice( 'Svuoto i topmenu di ' . self::currentSiteaccessName());
        self::refreshMenu( self::TOPMENU, self::currentSiteaccessName() );
        
        self::notice( 'Svuoto i leftmenu di ' . self::currentSiteaccessName() );
        self::refreshMenu( self::LEFTMENU, self::currentSiteaccessName() );

        $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
        $params = array( 'root_node_id' => $rootNodeId );
        self::notice( 'Genero il topmenu del nodo ' . $rootNodeId );
        self::getTopMenu( $params );
        $menuItems = OpenPAINI::variable( 'TopMenu', 'NodiCustomMenu');
        if ( empty( $menuItems ) )
        {
            $rootNode = eZContentObjectTreeNode::fetch( $rootNodeId );
            $menuItems = $rootNode->subTree( array( 'ClassFilterType' => 'include',
                                               'ClassFilterArray' => OpenPAINI::variable( 'TopMenu', 'IdentificatoriMenu' ),
                                               'Limit' => OpenPAINI::variable( 'TopMenu', 'LimitePrimoLivello' ),
                                               'SortBy' => $rootNode->attribute( 'sort_array' ),
                                               'Depth' => 1,
                                               'DepthOperator' => 'eq' ) );
        }
        $areeContainers = OpenPAINI::variable( 'TopMenu', 'NodiAreeCustomMenu');
        foreach( $menuItems as $item )
        {
            $itemNodeid = $item;
            if ( $item instanceof eZContentObjectTreeNode )
            {
                $itemNodeid = $item->attribute( 'node_id' );    
            }
            $params = array( 'root_node_id' => $itemNodeid );
            self::notice( 'Genero il leftmenu del nodo ' . $itemNodeid );
            self::getLeftMenu( $params );            
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
                if ( $itemNode instanceof eZContentObjectTreeNode && OpenPAINI::variable( 'AreeTematiche', 'IdentificatoreAreaTematica', false ) )
                {
                    $aree = $itemNode->subTree( array( 'ClassFilterType' => 'include',
                                                       'ClassFilterArray' => OpenPAINI::variable( 'AreeTematiche', 'IdentificatoreAreaTematica' ),
                                                       'Depth' => 1,
                                                       'DepthOperator' => 'eq' ) );
                    foreach( $aree as $area )
                    {
                        $areaParams =  array( 'root_node_id' => $area->attribute( 'node_id' ) ) ;
                        self::notice( 'Genero il leftmenu dell\'area tematica ' . $area->attribute( 'node_id' ) );
                        self::getLeftMenu( $areaParams );  
                    }
                }
            }
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
}

eZDebug::appendBottomReport( 'OpenPAMenuTool', array( 'OpenPAMenuTool', 'printDebugReport' ) );