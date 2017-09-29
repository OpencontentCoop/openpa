<?php

class ObjectHandlerServiceContentLine extends ObjectHandlerServiceBase
{
    protected $attributeList;

    function run()
    {
        $this->fnData['attributes'] = 'getAttributeList';
        $this->fnData['identifiers'] = 'getAttributeListIdentifiers';
        $this->fnData['has_content'] = 'getAttributeListCount';
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
    
    protected function getAttributeListIdentifiers()
    {
        return array_keys( $this->getAttributeList() );
    }
    
    protected function getAttributeListCount()
    {
        return count( $this->getAttributeList() ) > 0;
    }
}