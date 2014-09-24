<?php

class ObjectHandlerServiceContentDetail extends ObjectHandlerServiceBase
{
    function run()
    {
        $list = $this->getAttributeList();
        $this->data['attributes'] = $list;
        $this->data['identifiers'] = array_keys( $list );
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
    }

    function getAttributeList()
    {
        $list = array();
        $mainContent = $this->container->attribute( 'content_main' );
        $extraInfoCollectors = $this->container->attribute( 'content_infocollection' )->attribute( 'extra_identifiers' );
        foreach( $this->container->attributesHandlers as $attribute )
        {
            $fullData = $attribute->attribute( 'full' );
            if ( $fullData['has_content']
                 && !$fullData['exclude']
                 && !in_array( $attribute->attribute( 'identifier' ), $mainContent->attribute( 'identifiers' ) )
                 && !in_array( $attribute->attribute( 'identifier' ), $extraInfoCollectors ) )
            {
                $list[$attribute->attribute( 'identifier' )] = $attribute;
            }
        }
        return $list;
    }
}