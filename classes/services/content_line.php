<?php

class ObjectHandlerServiceContentLine extends ObjectHandlerServiceBase
{
    protected $attributeList;

    function run()
    {
        $list = $this->getAttributeList();
        $this->data['attributes'] = $list;
        $this->data['identifiers'] = array_keys( $list );
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
    }

    function getAttributeList()
    {
        if ( $this->attributeList === null )
        {
            $this->attributeList = array();
            $infoCollectors = $this->container->attribute( 'content_infocollection' )->attribute( 'identifiers' );
            $extraInfoCollectors = $this->container->attribute( 'content_infocollection' )->attribute( 'extra_identifiers' );
            foreach( $this->container->attributesHandlers as $attribute )
            {
                $lineData = $attribute->attribute( 'line' );
                if ( $lineData ['has_content']
                     && !$lineData ['exclude']
                     && !in_array( $attribute->attribute( 'identifier' ), $infoCollectors )
                     && !in_array( $attribute->attribute( 'identifier' ), $extraInfoCollectors ) )
                {
                    $this->attributeList[$attribute->attribute( 'identifier' )] = $attribute;
                }
            }
        }

        return $this->attributeList;
    }
}