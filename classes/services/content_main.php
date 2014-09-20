<?php

class ObjectHandlerServiceContentMain extends ObjectHandlerServiceBase
{
    function run()
    {
        //@todo
        $this->data['identifiers'] = array(
            'image',
            'ruolo',
            'ruolo2',
            'oggetto',
            'abstract',
            'short_description',
            'data_iniziopubblicazione',
            'data_archiviazione'
        );
        $this->data['attributes'] = $this->getAttributeList();
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
    }

    protected function getAttributeList()
    {
        $list = array();
        foreach( $this->container->attributesHandlers as $attribute )
        {
            $fullData = $attribute->attribute( 'full' );
            if ( in_array( $attribute->attribute( 'identifier' ), $this->data['identifiers'] )
                 && $fullData['has_content'] )
            {
                $list[$attribute->attribute( 'identifier' )] = $attribute;
            }
        }
        return $list;
    }
}