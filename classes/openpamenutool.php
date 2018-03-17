<?php

class OpenPAMenuTool
{
    const CACHE_IDENTIFIER = 'openpamenu';

    const TOPMENU = 'topmenu';
    const LEFTMENU = 'leftmenu';
    const TREEMENU = 'treemenu';

    /**
     * @var eZUser
     */
    private static $currentUser;

    /**
     * @param $menuHandlerIdentifier
     * @param $parameters
     *
     * @return OpenPAMenuHandlerInterface
     * @throws Exception
     */
    protected static function instanceMenuHandler( $menuHandlerIdentifier, $parameters )
    {
        if ( $menuHandlerIdentifier == self::TOPMENU || $menuHandlerIdentifier == self::LEFTMENU )
        {
            return new OpenPALegacyMenuHandler( $menuHandlerIdentifier, $parameters );
        }
        elseif( $menuHandlerIdentifier == self::TREEMENU )
        {
            return new OpenPATreeMenuHandler( $parameters );
        }
        throw new Exception( "$menuHandlerIdentifier handler not found" );
    }

    /**
     * @param array $parameters
     *
     * @return string
     * @throws Exception
     */
    public static function getLeftMenu( $parameters )
    {
        $instance = self::instanceMenuHandler( self::LEFTMENU, $parameters );
        return self::getMenu( $instance );
    }

    /**
     * @param array $parameters
     *
     * @return string
     * @throws Exception
     */
    public static function getTopMenu( $parameters )
    {
        $instance = self::instanceMenuHandler( self::TOPMENU, $parameters );
        return self::getMenu( $instance );
    }

    /**
     * @param array $parameters
     *
     * @return string
     * @throws Exception
     */
    public static function getTreeMenu( $parameters )
    {
        $instance = self::instanceMenuHandler( self::TREEMENU, $parameters );
        return self::getMenu( $instance );
    }

    /**
     * @param OpenPAMenuHandlerInterface $instance
     *
     * @return string
     */
    protected static function cacheFilePath( $instance )
    {
        $currentSiteAccess = $GLOBALS['eZCurrentAccess']['name'];
        $cachePath = eZDir::path( array( eZSys::cacheDirectory(), OpenPAMenuTool::cacheDirectory(), $currentSiteAccess, $instance->cacheFileName() ) );
        return $cachePath;
    }

    /**
     * @return string
     */
    public static function cacheDirectory()
    {
        $siteINI = eZINI::instance();
        $items = (array) $siteINI->variable( 'Cache', 'CacheItems' );
        if ( in_array( self::CACHE_IDENTIFIER, $items ) &&  $siteINI->hasGroup( 'Cache_' . self::CACHE_IDENTIFIER ))
        {
            $settings = $siteINI->group( 'Cache_' . self::CACHE_IDENTIFIER );
            if ( isset( $settings['path'] ) )
            {
                return $settings['path'];
            }
        }
        return self::CACHE_IDENTIFIER;
    }

    /**
     * @param OpenPAMenuHandlerInterface $instance
     *
     * @return mixed
     */
    protected static function getMenu( $instance )
    {
        $parameters = $instance->getParameters();
        if ( OpenPAINI::variable( 'CacheSettings', 'Menu' ) == 'disabled' )
        {
            return call_user_func( array( get_class( $instance ), 'getMenu' ), $parameters );
        }
        else
        {
            $cacheFilePath = self::cacheFilePath( $instance );
            $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );

            return $cacheFile->processCache(
                array( get_class( $instance ), 'menuRetrieve' ),
                array( get_class( $instance ), 'menuGenerate' ),
                null,
                null,
                compact( 'parameters' )
            );
        }
    }

    public static function refreshMenu( $id = null, $siteAccess = 'current' )
    {
        if ( $id === null && $siteAccess === false )
        {
            eZCache::clearByTag( self::CACHE_IDENTIFIER );
        }
        else
        {
            if ( $siteAccess === 'current' )
            {
                $siteAccess = $GLOBALS['eZCurrentAccess']['name'];
            }

            $ini = eZINI::instance();
            if ( $siteAccess === false )
            {
                if ( $ini->hasVariable( 'SiteAccessSettings', 'RelatedSiteAccessList' )
                     && $relatedSiteAccessList = $ini->variable(
                        'SiteAccessSettings',
                        'RelatedSiteAccessList'
                    )
                )
                {
                    if ( !is_array( $relatedSiteAccessList ) )
                    {
                        $relatedSiteAccessList = array( $relatedSiteAccessList );
                    }
                    $relatedSiteAccessList[] = $GLOBALS['eZCurrentAccess']['name'];
                    $siteAccesses = array_unique( $relatedSiteAccessList );
                }
                else
                {
                    $siteAccesses = $ini->variable(
                        'SiteAccessSettings',
                        'AvailableSiteAccessList'
                    );
                }
            }
            else
            {
                $siteAccesses = array( $siteAccess );
            }
            if ( !empty( $siteAccesses ) )
            {
                $cacheBaseDir = eZDir::path(
                    array( eZSys::cacheDirectory(), self::cacheDirectory() )
                );
                $fileHandler = eZClusterFileHandler::instance();
                $fileHandler->fileDeleteByDirList( $siteAccesses, $cacheBaseDir, $id );
            }
        }
    }

    public static function generateAllMenus()
    {
        $designList = (array) eZINI::instance()->variable( 'DesignSettings', 'AdditionalSiteDesignList' );
        if ( in_array( 'admin', $designList ) )
        {
            return false;
        }

        eZCache::clearByTag( 'template' );

        $siteAccess = $GLOBALS['eZCurrentAccess']['name'];
        OpenPALog::notice( "Clear all menu for siteaccess $siteAccess" );
        self::refreshMenu();

        $menuItems = OpenPaFunctionCollection::fetchTopMenuNodes();

        foreach( $menuItems as $index => $item )
        {
            $position = array();
            if ( $index == 0 ) $position = array( 'firstli' );
            if ( $index == count( $menuItems ) - 1 ) $position = array( 'lastli' );

            if ( $item instanceof eZContentObjectTreeNode )
            {
                $itemNode = $item;
            }
            else
            {
                $itemNode = eZContentObjectTreeNode::fetch( $item );
            }
            if ( $itemNode instanceof eZContentObjectTreeNode )
            {

                $itemNodeId = $itemNode->attribute( 'node_id' );

                OpenPALog::notice( "Generate menu for node $itemNodeId" );

                $params = array(
                    'root_node_id' => $itemNodeId,
                    'position' => $position
                );

                if ( in_array( 'openpa_design_base', $designList ) )
                {
                    self::getTopMenu( $params );
                    self::getLeftMenu( $params );
                }

                if ( in_array( 'ocbootstrap', $designList ) )
                {
                    $openpa = OpenPAObjectHandler::instanceFromObject( $itemNode );
                    if ( $openpa instanceof OpenPAObjectHandler )
                    {
                        $menuService = $openpa->service( 'control_menu' );
                        if ( $menuService instanceof ObjectHandlerServiceControlMenu )
                        {
                            $topMenuParams = array(
                                'root_node_id' => $itemNodeId,
                                'scope' => 'top_menu'
                            );
                            self::getTreeMenu( $topMenuParams );
                            $sideMenuRootNode = $menuService->attribute( 'side_menu' )->attribute( 'root_node' );
                            if ( $sideMenuRootNode instanceof eZContentObjectTreeNode )
                            {
                                $sideMenuParams = array(
                                    'root_node_id' => $sideMenuRootNode->attribute( 'node_id' ),
                                    'scope' => 'side_menu',
                                    'user_hash' => $menuService->attribute(
                                        'side_menu'
                                    )->attribute( 'user_hash' )
                                );
                                self::getTreeMenu( $sideMenuParams );
                            }
                        }
                    }
                }

                if ( in_array(  $itemNodeId,  OpenPAINI::variable( 'TopMenu', 'NodiAreeCustomMenu', array() )  ) )
                {
                    self::suAnonymous();
                    /** @var eZUser $anonymousUser */
                    $anonymousUser = eZUser::fetch( eZUser::anonymousId() );
                    $userHash = implode(  ',',  $anonymousUser->attribute( 'role_id_list' )  ) . ',';
                    $userHash .= implode( ',', $anonymousUser->attribute( 'limited_assignment_value_list' ) );
                    /** @var eZContentObjectTreeNode[] $aree */
                    $aree = $itemNode->subTree(
                        array(
                            'ClassFilterType' => 'include',
                            'ClassFilterArray' => OpenPAINI::variable(
                                'AreeTematiche',
                                'IdentificatoreAreaTematica'
                            ),
                            'Depth' => 1,
                            'DepthOperator' => 'eq'
                        )
                    );
                    foreach ( $aree as $area )
                    {
                        if ( in_array( 'openpa_design_base', $designList ) )
                        {
                            self::getLeftMenu( array(
                                'root_node_id' => $area->attribute( 'node_id' ),
                                'user_hash' => $userHash
                            ));
                        }
                        if ( in_array( 'ocbootstrap', $designList ) )
                        {
                            $openpa = OpenPAObjectHandler::instanceFromObject( $area );
                            if ( $openpa instanceof OpenPAObjectHandler )
                            {
                                $menuService = $openpa->service( 'control_menu' );
                                if ( $menuService instanceof ObjectHandlerServiceControlMenu )
                                {
                                    $sideMenuParams = array(
                                        'root_node_id' => $menuService->attribute(
                                            'side_menu'
                                        )->attribute( 'root_node' )->attribute( 'node_id' ),
                                        'scope' => 'side_menu',
                                        'user_hash' => $menuService->attribute(
                                            'side_menu'
                                        )->attribute( 'user_hash' )
                                    );

                                    self::getTreeMenu( $sideMenuParams );
                                }
                            }
                        }
                    }
                    self::exitAnonymous();
                }
            }
        }
        return true;
    }

    public static function suAnonymous()
    {
        self::$currentUser = eZUser::currentUser();
        $anonymousUser = eZUser::fetch( eZUser::anonymousId() );
        if ( $anonymousUser instanceof eZUser && self::$currentUser->id() != eZUser::anonymousId() )
        {
            eZUser::setCurrentlyLoggedInUser( $anonymousUser, $anonymousUser->id() );
        }
    }

    public static function exitAnonymous()
    {
        if ( self::$currentUser instanceof eZUser && self::$currentUser->id() != eZUser::anonymousId() )
        {
            eZUser::setCurrentlyLoggedInUser( self::$currentUser, self::$currentUser->id() );
        }
    }
}
