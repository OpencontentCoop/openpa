<?php

class ObjectHandlerServiceContentAttachment extends ObjectHandlerServiceBase
{
    function run()
    {
        $list = $this->getAttributeList();
        $this->data['attributes'] = $list;
        $this->data['identifiers'] = array_keys( $list );
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
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
                'Depth' => 1,
                'DepthOperator' => 'eq' ) );
        }
        return $list;
    }

    protected function getAttributeList()
    {
        $list = array();
        foreach( $this->container->attributesHandlers as $attribute )
        {
            if ( $attribute->is( 'attributi_allegati_atti' ) && $attribute->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
            {
                $list[$attribute->attribute( 'identifier' )] = $attribute->attribute( 'contentobject_attribute' );
            }
        }
        return $list;
    }
}