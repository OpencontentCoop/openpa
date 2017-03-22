<?php

class ObjectHandlerServiceContentMain extends ObjectHandlerServiceBase
{
    
    protected static $attributeList = array();
    
    function run()
    {
        $this->data['identifiers'] = OpenPAINI::variable('ContentMain', 'Identifiers', array(
            'image',
            'ruolo',
            'ruolo2',
            'oggetto',
            'abstract',
            'short_description',
            'description',
            'descrizione'
        ));
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
        if ( $this->container->getContentObject() instanceof eZContentObject )
        {            
            if ( !isset( self::$attributeList[$this->container->getContentObject()->attribute('id' )] ) )
            {
                self::$attributeList[$this->container->getContentObject()->attribute('id' )] = array();
                foreach( $this->container->attributesHandlers as $attribute )
                {
                    $fullData = $attribute->attribute( 'full' );
                    if ( in_array( $attribute->attribute( 'identifier' ), $this->data['identifiers'] )
                         && $fullData['has_content'] )
                    {
                        self::$attributeList[$this->container->getContentObject()->attribute('id' )][$attribute->attribute( 'identifier' )] = $attribute;
                    }
                }
            }
            return self::$attributeList[$this->container->getContentObject()->attribute('id' )];
        }
        return array();
    }
}