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
        $this->data['attributes'] = $this->getAttributeList();
        $this->data['has_content'] = count( $this->data['attributes'] ) > 0;
        $this->data['parts'] = $this->getParts();
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