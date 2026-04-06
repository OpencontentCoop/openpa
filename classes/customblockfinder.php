<?php


class CustomBlockFinder
{

    function __construct()
    {
        $this->cacheControlBlockTypes = array( 'Lista' );
        $this->customAttributeNodeID = array( 'node_id', 'parent_node_id' );
        $this->customAttributeInclude = 'includi_classi';
        $this->customAttributeExclude = 'escludi_classi';
    }    
    
    function checkNode( $node, $clearObjects = true )
    {
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            eZDebug::writeError( "Var node isn't eZContentObjectTreeNode", __METHOD__ );
            return false;
        }
        $results = $this->find();
        $classIdentifier = $node->attribute( 'class_identifier' );
        foreach( $results as $result )
        {
            $do = false;
            foreach( $result->parentNodeToCheck as $resultNode )
            {
                if ( in_array( $resultNode->nodeID, $node->attribute( 'path_array' )  ) )
                {                    
                    if
                    (
                           ( !$resultNode->classesToInclude && !$resultNode->classesToExclude )
                        || ( $resultNode->classesToInclude && in_array( $classIdentifier, $resultNode->classesToInclude ) )
                        || ( $resultNode->classesToExclude && !in_array( $classIdentifier, $resultNode->classesToExclude ) )
                    )
                    {
                        $do = true;
                    }                    
                }
            }
            if ( $clearObjects && $do )
            {
                eZContentCacheManager::clearContentCache( $result->objectToClear );               
            }
        }
    }
    
    function checkObject( $object, $clearObjects = true )
    {
        if ( !$object instanceof eZContentObject )
        {
            eZDebug::writeError( "Var object isn't eZContentObject", __METHOD__ );
            return false;
        }
        $assignedNodes = $object->attribute( 'assigned_nodes' );
        foreach( $assignedNodes as $node )
        {
            $this->checkNode( $node, $clearObjects );
        }        
    }
    
    /*
    ritorna un array di oggetti CustomBlockResult  
    */
    function find()
    {    
        $results = array();
        
        $ezpages = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(),
                                                array( 'contentobject_id', 'id' ),
                                                array( 'data_type_string' => 'ezpage' ),
                                                true );
        $objectsIDs = array();
        foreach( $ezpages as $ezpage )
        {
            if ( !array_key_exists( $ezpage->attribute( 'contentobject_id' ), $objectsIDs ) )
            {
                $objectsIDs[$ezpage->attribute( 'contentobject_id' )] = $ezpage->attribute( 'id' );
            }
        }
        
        $objects = eZContentObject::fetchIDArray( array_keys( $objectsIDs ) );
        foreach( $objects as $object )
        {
            $attribute = eZContentObjectAttribute::fetch( $objectsIDs[$object->attribute( 'id' )], $object->attribute( 'current_version' ) );
            if ( $attribute instanceof eZContentObjectAttribute && $attribute->hasContent() )
            {
                $zones = $attribute->content()->attribute( 'zones' );
                foreach( $zones as $zone )
                {
                    $blocks = $zone->attribute( 'blocks' );
                    if ( $zone->getBlockCount() > 0 )
                    {
                        foreach( $blocks as $block )
                        {                    
                            if ( $block instanceof eZPageBlock && $block->hasAttribute( 'type' ) && in_array( $block->attribute( 'type' ), $this->cacheControlBlockTypes ) )
                            {
                                if ( $block->hasAttribute( 'custom_attributes' ) )
                                {
                                    $customAttributes = $block->attribute( 'custom_attributes' );
                                    
                                    $nodeToAdd = false;
                                    foreach( $this->customAttributeNodeID as $customAttributeNodeID  )
                                    {
                                        if ( isset( $customAttributes[$customAttributeNodeID] ) )
                                        {
                                            $nodeToAdd = $customAttributes[$customAttributeNodeID];
                                            break;
                                        }
                                    }
                                    
                                    if ( $nodeToAdd )
                                    {                                        
                                        if ( !isset( $results[$object->attribute( 'id' )] ) )
                                        {
                                            $result = new CustomBlockFinderResult( $object->attribute( 'id' ) );
                                        }
                                        else
                                        {
                                            $result = $results[$object->attribute( 'id' )];
                                        }                                        
                                        
                                        $includeClasses = false;
                                        $excludeClasses = false;                                        
                                        if ( isset( $customAttributes[$this->customAttributeInclude] ) && !empty( $customAttributes[$this->customAttributeInclude] ) )
                                        {
                                            $includeClasses = $customAttributes[$this->customAttributeInclude];
                                        }
                                        elseif ( isset( $customAttributes[$this->customAttributeExclude] ) && !empty( $customAttributes[$this->customAttributeExclude] ) )
                                        {
                                            $excludeClasses = $customAttributes[$this->customAttributeExclude];
                                        }
                                        
                                        $result->addNode( $nodeToAdd, $includeClasses, $excludeClasses );
                                        $results[$object->attribute( 'id' )] = $result;
                                    }                                    
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $results;
        
    }
    
}

class CustomBlockFinderResult
{
    public $objectToClear;
    public $parentNodeToCheck = array();    
    
    function __construct( $objectToClear )
    {
        $this->objectToClear = $objectToClear;
    }
    
    function addNode( $id, $includeClasses, $excludeClasses )
    {
        $this->parentNodeToCheck[$id] = new CustomBlockFinderResultNode( $id, $includeClasses, $excludeClasses );
    }
}

class CustomBlockFinderResultNode
{
    public $nodeID;
    public $classesToInclude;
    public $classesToExclude;
    
    function __construct( $id, $includeClasses, $excludeClasses )
    {
        $this->nodeID = $id;
        if ( !empty( $includeClasses ) )
        {
            $this->classesToInclude = explode( ',', $includeClasses );
        }
        if ( !empty( $excludeClasses ) )
        {
            $this->classesToExclude = explode( ',', $excludeClasses );
        }
    }
}

?>
