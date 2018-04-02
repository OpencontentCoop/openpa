<?php

class FindGlobalLayoutOperator
{
    static $Operators = array(
        'find_global_layout'
    );
    
    function __construct()
    {
    }
    
    function operatorList()
    {
        return self::$Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'find_global_layout' => array(
                'param' => array( 'type' => 'array', 'required' => false, 'default' => array() )
            )
        );
    }

    function findGlobalLayout( $node ){
        $result = false;
        if ( is_numeric( $node ) )
        {
            $node = eZContentObjectTreeNode::fetch( $node );
        }
        if ( !$node )
        {
            return false;
        }

        $pathArray = $node->attribute( 'path_array' );
        $nodesParams = array();
        foreach( $pathArray as $pathNodeID )
        {
            if ( $pathNodeID < eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) )
            {
                continue;
            }
            else
            {
                $nodesParams[] = array(
                    'ParentNodeID' => $pathNodeID,
                    'ResultID' => 'ezcontentobject_tree.node_id',
                    'ClassFilterType' => 'include',
                    'ClassFilterArray' => array( 'global_layout' ),
                    'Depth' => 1,
                    'DepthOperator' => 'eq',
                    'AsObject' => false,
                    'Limitation' => array() //workaround per eZContentObjectTreeNode::subTreeMultiPaths che non considera le temp_table
                );
            }
        }
        //eZDebug::writeWarning( var_export($nodesParams,1), __METHOD__);
        $findNodes = eZContentObjectTreeNode::subTreeMultiPaths( $nodesParams, array( 'SortBy' => array( 'node_id', false ) ) );
        $sortByParentNodeID = array();
        $found = false;
        if ( !empty( $findNodes ) )
        {
            foreach( $findNodes as $findNode )
            {
                $sortByParentNodeID[ $findNode['parent_node_id'] ] = $findNode;
            }

            krsort( $sortByParentNodeID );

            $reversePathArray = array_reverse( $pathArray );
            foreach( $reversePathArray as $pathNodeID )
            {
                if ( isset( $sortByParentNodeID[$pathNodeID] ) )
                {
                    $result = eZContentObjectTreeNode::makeObjectsArray( array( $sortByParentNodeID[$pathNodeID] ) );
                    if ( !empty( $result ) )
                    {
                        $result = $result[0];
                        $found = true;
                        break;
                    }
                }
            }
            if ( !$found )
            {
                $result = array_shift( $sortByParentNodeID );
                $result = eZContentObjectTreeNode::makeObjectsArray( array( $result ) );
                if ( !empty( $result ) )
                {
                    $result = $result[0];
                }
            }
        }
        return $result;
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {		
        switch ( $operatorName )
        {
            case 'find_global_layout':
            {
                $operatorValue = $this->findGlobalLayout( $operatorValue );
            }
        }
    }
    

}

?>
