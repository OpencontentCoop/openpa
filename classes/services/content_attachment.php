<?php

class ObjectHandlerServiceContentAttachment extends ObjectHandlerServiceBase
{
    function run()
    {
        $list = $this->getAttributeList();
        $this->data['attributes'] = $list;
        $this->data['identifiers'] = array_keys( $list );
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
    }

    protected function getAttributeList()
    {
        $list = array();
        foreach( $this->container->attributesHandlers as $attribute )
        {
            if ( $attribute->is( 'attributi_allegati_atti' ) && $attribute->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
            {
                $list[$attribute->attribute( 'identifier' )] = $attribute;
            }
        }
        return $list;
    }
}