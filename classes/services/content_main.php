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
            'data_archiviazione',
            'description',
            'descrizione'
        );
        $this->fnData['attributes'] = 'getAttributeList';
        $this->fnData['has_content'] = 'getAttributeCount';
        $this->fnData['parts'] = 'getParts';
    }

    protected function getParts()
    {
        $data = array();
        if ( isset( $this->data['attributes']['image'] ) )
        {
            $data['image'] = $this->data['attributes']['image'];
        }

        if ( isset( $this->data['attributes']['abstract'] ) )
        {
            $data['abstract'] = $this->data['attributes']['abstract'];
        }
        elseif ( isset( $this->data['attributes']['short_description'] ) )
        {
            $data['abstract'] = $this->data['attributes']['short_description'];
        }
        elseif ( isset( $this->data['attributes']['oggetto'] ) )
        {
            $data['abstract'] = $this->data['attributes']['oggetto'];
        }
        elseif ( isset( $this->data['attributes']['ruolo'] ) )
        {
            $data['abstract'] = $this->data['attributes']['ruolo'];
        }

        if ( isset( $this->data['attributes']['descrizione'] ) )
        {
            $data['full_text'] = $this->data['attributes']['descrizione'];
        }
        elseif ( isset( $this->data['attributes']['description'] ) )
        {
            $data['full_text'] = $this->data['attributes']['description'];
        }
        return $data;
    }

    protected function getAttributeCount()
    {
        return count( $this->getAttributeList() ) > 0;
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