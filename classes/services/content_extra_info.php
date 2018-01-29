<?php

class ObjectHandlerServiceContentExtraInfo extends ObjectHandlerServiceBase
{
    protected $currentExtraInfoNode;

    function run()
    {
        $this->fnData['extra_info'] = 'getExtraInfoData';
    }

    function getExtraInfoData()
    {
        $data = array();
        $layout = $this->findGlobalLayout();
        if ( $layout instanceof eZContentObjectAttribute && $layout->attribute( 'has_content' ) )
        {
            $content = $layout->attribute( 'content' );
            foreach( $content->attribute( 'zones' ) as $zone )
            {
                $data[$zone->attribute( 'identifier' )] = $zone;
            }
        }
        return $data;
    }

    /**
     * @return eZContentObjectAttribute|false
     */
    protected function findGlobalLayout()
    {
        $this->findGlobalLayoutNode();
        $data = false;
        if ( $this->currentExtraInfoNode instanceof eZContentObjectTreeNode )
        {
            $dataMap = $this->currentExtraInfoNode->attribute( 'data_map' );
            foreach( $dataMap as $attribute )
            {
                if ( $attribute->attribute( 'data_type_string' ) == 'ezpage' )
                {
                    $data = $attribute;
                    break;
                }
            }
        }
        return $data;
    }

    protected function findGlobalLayoutNode()
    {
        if ( $this->currentExtraInfoNode == null )
        {
            $nodesParams = array();
            foreach( $this->container->currentPathNodeIds as $pathNodeId )
            {
                if ((int)$pathNodeId > 1) {
                    $nodesParams[] = array(
                        'ParentNodeID' => (int)$pathNodeId,
                        'ResultID' => 'ezcontentobject_tree.node_id',
                        'ClassFilterType' => 'include',
                        'ClassFilterArray' => array('global_layout'),
                        'Depth' => 1,
                        'DepthOperator' => 'eq',
                        'AsObject' => false,
                        'Limitation' => array() //workaround per eZContentObjectTreeNode::subTreeMultiPaths che non considera le temp_table
                    );
                }
            }
            if ( !empty( $nodesParams ) )
            {
                $findNodes = eZContentObjectTreeNode::subTreeMultiPaths( $nodesParams, array( 'SortBy' => array( 'node_id', false ) ) );
                $resultSortByParentNodeID = array();
    
                if ( !empty( $findNodes ) )
                {
                    foreach( $findNodes as $findNode )
                    {
                        $resultSortByParentNodeID[ $findNode['parent_node_id'] ] = $findNode;
                    }
                    krsort( $resultSortByParentNodeID );
                    $reversePathArray = array_reverse( $this->container->currentPathNodeIds );
                    foreach( $reversePathArray as $pathNodeId )
                    {
                        if ( isset( $resultSortByParentNodeID[$pathNodeId] ) )
                        {
                            $result = eZContentObjectTreeNode::makeObjectsArray( array( $resultSortByParentNodeID[$pathNodeId] ) );
                            if ( !empty( $result ) )
                            {
                                if ( $result[0]->attribute( 'can_read' ) )
                                {
                                    $this->currentExtraInfoNode = $result[0];
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            $this->currentExtraInfoNode = false;
            return false;
        }
    }
}
