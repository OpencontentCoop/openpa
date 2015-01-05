<?php

class ObjectHandlerServiceControlMenu extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['available_menu'] = array( 'top_menu', 'side_menu' );

        $this->data['show_top_menu'] = true;
        $this->data['top_menu'] = new OpenPATempletizable( array(
            'root_node'=> OpenPaFunctionCollection::fetchHome(),
            'classes' => OpenPAINI::variable( 'TopMenu', 'IdentificatoriMenu', array() ),
            'exclude' => OpenPAINI::variable( 'TopMenu', 'NascondiNodi', array() ),
            'limits' => array(
                'level_2' => OpenPAINI::variable( 'TopMenu', 'LimiteTerzoLivello', 10 )
            ),
            'user_hash' => null,
            'max_recursion' => 1,
            'custom_max_recursion' => $this->getTopMenuCustomRecursions(),
            'custom_fetch_parameters' => $this->getTopMenuCustomFetchParameters()
        ));

        $this->data['show_side_menu'] = $this->hasSideMenu();
        $this->data['side_menu'] = new OpenPATempletizable( array(
            'root_node' => $this->getSideMenuRootNode(),
            'classes' => $this->getSideMenuClassIdentifiers(),
            'exclude' => OpenPAINI::variable( 'SideMenu', 'NascondiNodi', array() ),
            'limits' => array(),
            'user_hash' => $this->getSideMenuUserHash(),
            'max_recursion' => 4,
            'custom_max_recursion' => array(),
            'custom_fetch_parameters' => array()
        ));
        
        $this->data['show_extra_menu'] = $this->hasExtraMenu();
    }
    
    //@todo
    protected function hasExtraMenu()
    {
        $result = false;
        if ( !$result && $this->container->hasAttribute( 'content_gallery' ) )
        {
            $result = $this->container->attribute( 'content_gallery' )->attribute( 'has_images' );
        }
        if ( !$result && $this->container->hasAttribute( 'content_related' ) )
        {
            $result = $this->container->attribute( 'content_related' )->attribute( 'has_data' );
        }
        if ( !$result && $this->container->hasAttribute( 'content_facets' ) )
        {
            $result = $this->container->attribute( 'content_facets' )->attribute( 'has_data' );
        }
        return $result;
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
        return !( in_array( $this->container->currentNodeId, $nascondiNeiNodi )
                 || in_array( $this->container->currentClassIdentifier, $nascondiNelleClassi )
                 || $nascondi );
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
                foreach( $this->container->currentPathNodeIds as $nodeId )
                {
                    $node = OpenPABase::fetchNode( $nodeId );
                    if ( in_array( $node->attribute( 'class_identifier' ), $customContextMenuClasses ) )
                    {
                        $rootNode = $node;
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