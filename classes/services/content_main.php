<?php

class ObjectHandlerServiceContentMain extends ObjectHandlerServiceBase
{
    private $identifiers = array();

    private $abstractIdentifiers = array();

    private $fullTextIdentifiers = array();

    protected static $attributeList = array();

    function __construct( $data = array() )
    {
        $this->identifiers = OpenPAINI::variable('ContentMain', 'Identifiers', array(
            'image',
            'oggetto',
            'abstract',
            'short_description',
            'description',
            'descrizione'
        ));

        $this->abstractIdentifiers = OpenPAINI::variable('ContentMain', 'AbstractIdentifiers', array(
            'abstract',
            'short_description',
            'oggetto',
        ));

        $this->fullTextIdentifiers = OpenPAINI::variable('ContentMain', 'FullTextIdentifiers', array(
            'descrizione',
            'description',
        ));

        parent::__construct($data);
    }

    function run()
    {
        $this->data['identifiers'] = $this->identifiers;
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

        foreach($this->abstractIdentifiers as $identifier){
            if ( isset( $attributes[$identifier] ) )
            {
                $data['abstract'] = $attributes[$identifier];
                break;
            }
        }

        foreach($this->fullTextIdentifiers as $identifier){
            if ( isset( $attributes[$identifier] ) )
            {
                $data['full_text'] = $attributes[$identifier];
                break;
            }
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
