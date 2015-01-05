<?php

class ObjectHandlerServiceContentContacts extends ObjectHandlerServiceBase
{
    protected $attributeList;
    
    function run()
    {
        $list = $this->getAttributeList();
        $this->data['show_label'] = true; //@todo
        $this->data['label'] = 'Contatti'; //@todo
        $this->data['attributes'] = $list;
        $this->data['identifiers'] = array_keys( $list );
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
    }

    function getAttributeList()
    {
        if ( $this->attributeList === null )
        {
            $this->attributeList = array();
            $mainContent = $this->container->attribute( 'content_main' );
            $extraInfoCollectors = $this->container->attribute( 'content_infocollection' )->attribute( 'extra_identifiers' );
            foreach( $this->container->attributesHandlers as $attribute )
            {
                $fullData = $attribute->attribute( 'full' );
                if ( $fullData['has_content']
                     && !$fullData['exclude']
                     && $attribute->is( 'attributi_contatti' )
                     && !in_array( $attribute->attribute( 'identifier' ), $extraInfoCollectors ) )
                {
                    $this->attributeList[$attribute->attribute( 'identifier' )] = $attribute;
                }
            }
        }
        return $this->attributeList;
    }
}