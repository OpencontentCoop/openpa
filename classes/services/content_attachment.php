<?php

class ObjectHandlerServiceContentAttachment extends ObjectHandlerServiceBase
{
    protected $list;
    
    function run()
    {        
        $this->fnData['attributes'] = 'getAttributeList';
        $this->fnData['identifiers'] = 'getAttributeListIdentifiers';
        $this->fnData['has_content'] = 'getAttributeListCount';
        $this->fnData['children_count'] = 'getChildrenCount';
        $this->fnData['children'] = 'getChildren';
    }

    protected function getChildrenCount()
    {
        $count = 0;
        $node = $this->container->getContentNode();
        if ( $node instanceof eZContentObjectTreeNode )
        {
            $count = $node->subTreeCount( array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'file_pdf' ),
                'Depth' => 1,
                'DepthOperator' => 'eq' ) );
        }
        return $count;
    }

    protected function getChildren()
    {
        $list = array();
        $node = $this->container->getContentNode();
        if ( $node instanceof eZContentObjectTreeNode )
        {
            $list = $node->subTree( array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array( 'file_pdf' ),
                'SortBy' => $node->attribute( 'sort_array' ),
                'Depth' => 1,
                'DepthOperator' => 'eq' ) );
        }
        return $list;
    }

    protected function getAttributeListIdentifiers()
    {
        return array_keys( $this->getAttributeList() );
    }
    
    protected function getAttributeListCount()
    {
        return count( $this->getAttributeList() ) > 0;
    }
    
    protected function getAttributeList()
    {
        if ($this->list === null)
        {
            $this->list = array();
            foreach( $this->container->attributesHandlers as $attribute )
            {
                if ( $attribute->is( 'attributi_allegati_atti' ) && $attribute->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
                {
                    $this->list[$attribute->attribute( 'identifier' )] = $attribute->attribute( 'contentobject_attribute' );
                }
            }
        }
        return $this->list;
    }
}