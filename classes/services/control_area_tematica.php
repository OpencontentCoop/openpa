<?php

class ObjectHandlerServiceControlAreaTematica extends ObjectHandlerServiceBase
{
    private static $areaTematicaNodes = array();

    function run()
    {
        $this->data['area_tematica'] = $this->getAreaTematicaNode();
        $this->data['is_area_tematica'] = $this->data['area_tematica'] != false;
    }

    protected function getAreaTematicaNode()
    {
        foreach( $this->container->currentPathNodeIds as $nodeId )
        {
            if ( !in_array( $nodeId, array_keys( self::$areaTematicaNodes ) ) )
            {
                $areeIdentifiers = OpenPAINI::variable( 'AreeTematiche', 'IdentificatoreAreaTematica', array( 'area_tematica' ) );
                $node = OpenPABase::fetchNode( $nodeId );
                if ( $node instanceof eZContentObjectTreeNode )
                {
                    if ( in_array( $node->attribute( 'class_identifier' ), $areeIdentifiers ) )
                    {
                        self::$areaTematicaNodes[$nodeId] = $node;
                        return $node;
                    }
                }
            }
            else
            {
                return self::$areaTematicaNodes[$nodeId];
            }
        }
        return false;
    }
}