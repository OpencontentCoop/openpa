<?php

class ObjectHandlerServiceControlMenu extends ObjectHandlerServiceBase
{
    protected $hasExtraMenu;

    function run()
    {
        $this->data['available_menu'] = array( 'top_menu', 'side_menu' );

        $this->data['show_top_menu'] = true;
        $this->fnData['top_menu'] = 'topMenu';

        $this->data['show_side_menu'] = $this->hasSideMenu();
        $this->fnData['side_menu'] = 'sideMenu';

        $this->fnData['show_extra_menu'] = 'hasExtraMenu';
    }

    protected function topMenu()
    {
        return new OpenPATempletizable( array(
            'root_node'=> OpenPaFunctionCollection::fetchHome(),
            'classes' => OpenPAINI::variable( 'TopMenu', 'IdentificatoriMenu', array() ),
            'exclude' => OpenPAINI::variable( 'TopMenu', 'NascondiNodi', array() ),
            'limits' => array(
                'level_2' => OpenPAINI::variable( 'TopMenu', 'LimiteTerzoLivello', 10 )
            ),
            'user_hash' => null,
            'max_recursion' =>  OpenPAINI::variable( 'TopMenu', 'MaxRecursion', 1 ),
            'custom_max_recursion' => $this->getTopMenuCustomRecursions(),
            'custom_fetch_parameters' => $this->getTopMenuCustomFetchParameters()
        ));
    }

    protected function sideMenu()
    {
        return new OpenPATempletizable( array(
            'root_node' => $this->getSideMenuRootNode(),
            'classes' => $this->getSideMenuClassIdentifiers(),
            'exclude' => OpenPAINI::variable( 'SideMenu', 'NascondiNodi', array() ),
            'limits' => array(),
            'user_hash' => $this->getSideMenuUserHash(),
            'max_recursion' => 4,
            'custom_max_recursion' => array(),
            'custom_fetch_parameters' => array()
        ));
    }

    //@todo
    protected function hasExtraMenu()
    {
        if ( $this->hasExtraMenu === null )
        {
            $debug = array();
            $result = false;
//            if ( !$result && $this->container->hasAttribute( 'content_gallery' ) )
//            {
//                $result = $this->container->attribute( 'content_gallery' )->attribute( 'has_images' );
//                if ( $result ) $debug[] = 'content_gallery';
//            }
            if ( !$result && $this->container->hasAttribute( 'content_related' ) )
            {
                $result = $this->container->attribute( 'content_related' )->attribute( 'has_data' );
                if ( $result ) $debug[] = 'content_related';
            }
            if ( !$result && $this->container->hasAttribute( 'content_reverse_related' ) )
            {
                $result = $this->container->attribute( 'content_reverse_related' )->attribute( 'has_data' );
                if ( $result ) $debug[] = 'content_reverse_related';
            }
            if ( !$result && $this->container->hasAttribute( 'control_children' ) )
            {
                $result = $this->container->attribute( 'control_children' )->attribute( 'current_view' ) == 'filters';
                if ( $result ) $debug[] = 'control_children';
            }
            if ( !$result && $this->container->hasAttribute( 'content_facets' ) )
            {
                $result = $this->container->attribute( 'content_facets' )->attribute( 'has_data' );
                if ( $result ) $debug[] = 'content_facets';
            }
            if ( !$result && $this->container->hasAttribute( 'content_virtual' ) )
            {
                $result = $this->container->attribute( 'content_virtual' )->attribute( 'folder' );
                if ( $result ) $debug[] = 'content_virtual';
            }
            if ( !$result && $this->container->getContentNode() instanceof eZContentObjectTreeNode) {
                $parent = $this->container->getContentNode()->attribute('parent');
                if ( $parent instanceof eZContentObjectTreeNode && $parent->attribute('node_id') > 1) {
                    $parent = OpenPAObjectHandler::instanceFromContentObject( $parent->attribute( 'object' ) );
                    if ( $parent->hasAttribute('content_virtual')) {
                        $result = $parent->attribute('content_virtual')->attribute('folder');
                        if ( $result ) $debug[] = 'content_virtual';
                    }
                }
            }
            if ( !$result && $this->container->hasAttribute( 'content_globalinfo' ) )
            {
                $result = $this->container->attribute( 'content_globalinfo' )->attribute( 'has_content' );
                if ( $result ) $debug[] = 'content_globalinfo';
                if ( !$result ){
                    $parent = $this->container->getContentNode()->attribute('parent');
                    if ( $parent instanceof eZContentObjectTreeNode && $parent->attribute('node_id') > 1) {
                        $parent = OpenPAObjectHandler::instanceFromContentObject( $parent->attribute( 'object' ) );
                        if ( $parent->hasAttribute('content_globalinfo')) {
                            $result = $parent->attribute('content_globalinfo')->attribute('has_content');
                            if ( $result ) $debug[] = 'content_globalinfo_parent';
                        }
                    }
                }
            }

            if ( !$result && $this->container->hasAttribute( 'layout' ) )
            {
                $result = $this->container->attribute( 'layout' )->attribute( 'has_content' );
                if ( $result ) $debug[] = 'layout';
            }

            $hiddenNodes = OpenPAINI::variable( 'ExtraMenu', 'NascondiNeiNodi', array() );
            $hiddenClasses = OpenPAINI::variable( 'ExtraMenu', 'NascondiNelleClassi', array() );
            if ( $this->container->getContentNode() instanceof eZContentObjectTreeNode )
            {
                if ( in_array( $this->container->getContentNode()->attribute( 'node_id' ), $hiddenNodes ) )
                {
                    return false;
                }
                if ( in_array( $this->container->getContentNode()->attribute( 'class_identifier' ), $hiddenClasses ) )
                {
                    return false;
                }
            }

            $currentChildrenView = $this->container->attribute( 'control_children' )->attribute( 'current_view' );
            $currentChildrenViewsConfigs = $this->container->attribute( 'control_children' )->attribute( 'views' );
            if (isset($currentChildrenViewsConfigs[$currentChildrenView]['hide_menu'])){
                $result = !$currentChildrenViewsConfigs[$currentChildrenView]['hide_menu'];
                if ( !$result ) $debug[] = 'hide by control_children (hide_menu)';
            }

            $this->hasExtraMenu = $result;
            eZDebug::writeDebug( implode( ', ', $debug ), __METHOD__ );
        }
        return $this->hasExtraMenu;
    }

    protected function getTopMenuCustomFetchParameters()
    {
        $data = array();
        $topMenuRootNodeIds = OpenPAINI::variable( 'TopMenu', 'NodiCustomMenu', array() );
        foreach( $topMenuRootNodeIds as $nodeId )
        {
            if ( in_array( $nodeId, OpenPAINI::variable( 'TopMenu', 'NodiAreeCustomMenu', array() ) ) )
            {
                $data[$nodeId] = array(
                    'limitation' => array()
                );
            }
            if ( in_array( $nodeId, OpenPAINI::variable( 'TopMenu', 'NodiEstesiCustomMenu', array() ) ) )
            {
                $data[$nodeId] = array(
                    'limit' => OpenPAINI::variable( 'TopMenu', 'LimiteSecondoLivello', 4 )
                );
            }
        }
        return $data;
    }

    protected function getTopMenuCustomRecursions()
    {
        $data = array();
        $topMenuRootNodeIds = OpenPAINI::variable( 'TopMenu', 'NodiCustomMenu' );
        foreach( $topMenuRootNodeIds as $nodeId )
        {
            if ( in_array( $nodeId, OpenPAINI::variable( 'TopMenu', 'NodiEstesiCustomMenu', array() ) ) )
            {
                $data[$nodeId] = 2;
            }
            if ( in_array( $nodeId, OpenPAINI::variable( 'TopMenu', 'NodiSoloPrimoLivello', array() ) ) )
            {
                $data[$nodeId] = 1;
            }
        }
        return $data;
    }

    protected function hasSideMenu()
    {
        $nascondi = OpenPAINI::variable( 'SideMenu', 'Nascondi', false );
        $nascondiNeiNodi = OpenPAINI::variable( 'SideMenu', 'NascondiNeiNodi', array() );
        $nascondiNelleClassi = OpenPAINI::variable( 'SideMenu', 'NascondiNelleClassi', array() );

        $currentChildrenView = $this->container->attribute( 'control_children' )->attribute( 'current_view' );
        $currentChildrenViewsConfigs = $this->container->attribute( 'control_children' )->attribute( 'views' );
        $nascondiPerChildrenView = false;
        if (isset($currentChildrenViewsConfigs[$currentChildrenView]['hide_menu'])){
            $nascondiPerChildrenView = $currentChildrenViewsConfigs[$currentChildrenView]['hide_menu'];
        }

        return !( in_array($this->container->currentNodeId, $nascondiNeiNodi)
                  || in_array($this->container->currentClassIdentifier, $nascondiNelleClassi)
                  || $nascondi
                  || $nascondiPerChildrenView
        );
    }

    protected function getSideMenuRootNode()
    {
        $rootNode = false;
        if ( $this->container->hasAttribute( 'control_area_tematica' )
             && $this->container->attribute( 'control_area_tematica' )->attribute( 'is_area_tematica' ) )
        {
            $rootNode = $this->container->attribute( 'control_area_tematica' )->attribute( 'area_tematica' );
        }

        if ( !$rootNode instanceof eZContentObjectTreeNode )
        {
            $customContextMenuClasses = OpenPAINI::variable( 'SideMenu', 'SideMenuContextRootClasses', array() );
            if ( !empty( $customContextMenuClasses ) )
            {
                $currentPathNodeIds = explode( '/', $this->container->getContentNode()->attribute( 'path_string' ) );
                foreach( $currentPathNodeIds as $nodeId )
                {
                    $node = OpenPABase::fetchNode( $nodeId );
                    if ( $node instanceof eZContentObjectTreeNode )
                    {
                        if ( in_array( $node->attribute( 'class_identifier' ), $customContextMenuClasses ) )
                        {
                            $rootNode = $node;
                        }
                    }
                }
            }
        }

        if ( !$rootNode instanceof eZContentObjectTreeNode && isset( $this->container->currentPathNodeIds[0] ) )
        {
            $rootNode = OpenPABase::fetchNode( $this->container->currentPathNodeIds[0] );
        }
        //if ( !$rootNode instanceof eZContentObjectTreeNode )
        //{
        //    $rootNode = OpenPaFunctionCollection::fetchHome();
        //}
        return $rootNode;
    }

    protected function getSideMenuClassIdentifiers()
    {
        $classes = false;
        $rootNode = $this->getSideMenuRootNode();
        if ( $rootNode instanceof eZContentObjectTreeNode )
        {
            $classes = OpenPAINI::variable( 'SideMenu', 'IdentificatoriMenu_' . $rootNode->attribute( 'class_identifier' ), false );
        }
        if ( !$classes )
        {
            $classes = OpenPAINI::variable( 'SideMenu', 'IdentificatoriMenu', array() );
        }
        return $classes;
    }

    protected function getSideMenuUserHash()
    {
        if ( $this->container->attribute( 'control_area_tematica' )->attribute( 'is_area_tematica' ) )
        {
            $this->container->currentUserHashString;
        }
        return false;
    }
}
