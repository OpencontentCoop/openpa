<?php

class ObjectHandlerServiceContentMain extends ObjectHandlerServiceBase
{
    
    protected static $attributeList;
    
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
        $attributes = $this->getAttributeList();
        $data = array();
        if ( isset( $attributes['image'] ) )
        {
            $data['image'] = $attributes['image'];
        }

        if ( isset( $attributes['abstract'] ) )
        {
            $data['abstract'] = $attributes['abstract'];
        }
        elseif ( isset( $attributes['short_description'] ) )
        {
            $data['abstract'] = $attributes['short_description'];
        }
        elseif ( isset( $attributes['oggetto'] ) )
        {
            $data['abstract'] = $attributes['oggetto'];
        }
        elseif ( isset( $attributes['ruolo'] ) )
        {
            $data['abstract'] = $attributes['ruolo'];
        }

        if ( isset( $attributes['descrizione'] ) )
        {
            $data['full_text'] = $attributes['descrizione'];
        }
        elseif ( isset( $attributes['description'] ) )
        {
            $data['full_text'] = $attributes['description'];
        }
        return $data;
    }

    protected function getAttributeCount()
    {
        return count( $this->getAttributeList() ) > 0;
    }

    protected function getAttributeList()
    {
        if ( self::$attributeList === null )
        {
            self::$attributeList = array();
            foreach( $this->container->attributesHandlers as $attribute )
            {
                $fullData = $attribute->attribute( 'full' );
                if ( in_array( $attribute->attribute( 'identifier' ), $this->data['identifiers'] )
                     && $fullData['has_content'] )
                {
                    self::$attributeList[$attribute->attribute( 'identifier' )] = $attribute;
                }
            }
        }
        return self::$attributeList;
    }
}