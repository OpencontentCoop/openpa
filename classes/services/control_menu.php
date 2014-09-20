<?php

class ObjectHandlerServiceControlMenu extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['show_side_menu'] = $this->hasSideMenu();
        $this->data['side_menu_root_node'] = $this->getSideMenuRootNode();
        $this->data['side_menu_classes'] = $this->getSideMenuClassIdentifiers();
        $this->data['side_menu_exclude'] = OpenPAINI::variable( 'SideMenu', 'NascondiNodi', array() );
        $this->data['side_menu_limits'] = array();
        $this->data['side_menu_user_hash'] = $this->getSideMenuUserHash();
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
        return $rootNode;
    }

    protected function getSideMenuClassIdentifiers()
    {
        $classes = false;
        $rootNode = $this->data['side_menu_root_node'];
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