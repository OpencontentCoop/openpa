<?php
class OpenPAAttributeHandler extends OpenPATempletizable
{
    protected $attribute;
     
    protected $contentClassAttribute;
    
    protected $contentClass;
    
    public function __construct( eZContentObjectAttribute $attribute, $params = array() )
    {
        $this->attribute = $attribute;        
        
        $this->data['contentobject_attribute'] = $attribute;
        $this->fnData['contentclass_attribute'] = 'getContentClassAttribute';
        $this->fnData['contentclass'] = 'getContentClass';
        $this->fnData['identifier'] = 'getContentClassAttributeIdentifier';
        $this->fnData['label'] = 'getContentClassAttributeName';
        $this->data['data_type_string'] = $this->attribute->attribute( 'data_type_string' );
        $this->data['is_information_collector'] = $this->attribute->attribute( 'is_information_collector' );

        $this->fnData['full_identifier'] = 'getFullIdentifier';
        $this->fnData['full'] = 'fullData';
        $this->fnData['line'] = 'lineData';
        $this->fnData['has_content'] = 'hasContent';
        parent::__construct();
    }
    
    protected function getFullIdentifier()
    {
        return $this->getContentClass()->attribute( 'identifier' ) . '/' . $this->getContentClassAttribute()->attribute( 'identifier' );
    }
    
    protected function getContentClass()
    {
        if ($this->contentClass === null ){
           $this->contentClass = eZContentClass::fetch( $this->getContentClassAttribute()->attribute( 'contentclass_id' ) );
        }
        return $this->contentClass;
    }
    
    protected function getContentClassAttribute()
    {
        if ($this->contentClassAttribute === null ){
           $this->contentClassAttribute =  $this->attribute->attribute( 'contentclass_attribute' );
        }
        return $this->contentClassAttribute;
    }
    
    protected function getContentClassAttributeIdentifier()
    {
        return $this->getContentClassAttribute()->attribute( 'identifier' );
    }
    
    protected function getContentClassAttributeName()
    {
        return $this->getContentClassAttribute()->attribute( 'name' );
    }

    public function is( $settingValue, $defaults = array() )
    {
        return $this->inSettings( OpenPAINI::variable( 'GestioneAttributi', $settingValue, $defaults ) );
    }

    protected function inSettings( $settings )
    {
        return in_array( $this->getFullIdentifier(), $settings ) || in_array( $this->getContentClassAttributeIdentifier(), $settings );
    }

    protected function fullData()
    {
        return array(
            'show_label' => !$this->is( 'oggetti_senza_label' ),
            'exclude' => $this->is( 'attributi_da_escludere' ) || $this->data['is_information_collector'] || $this->is( 'attributi_da_escludere' ),
            'highlight' => $this->is( 'attributi_da_evidenziare' ),
            'has_content' => $this->hasContent(),
            'show_link' => !$this->is( 'attributi_senza_link' ),
            'show_empty' => $this->is( 'zero_is_content' ),
            'contatti' => $this->is( 'attributi_contatti' ),
            'collapse_label' => false
        );
    }

    //@todo
    protected function hasContent()
    {
        $hasContent = $this->attribute->attribute( 'has_content' );
        if ( $this->attribute->attribute( 'data_type_string' ) == 'ezinteger'
             && $this->attribute->toString() == 0 )
        {
            if ( $this->is( 'zero_is_content', array( 'ente_controllato/onere_complessivo' ) ) )
            {
                $hasContent = true;
            }
            else
            {
                $hasContent = false;
            }
        }
        return $hasContent;
    }

    protected function lineData()
    {
        return array(
            'show_label' => $this->is( 'attributes_with_title' ),
            'exclude' => !$this->is( 'attributes_to_show' ) || $this->data['is_information_collector'],
            //'highlight' => $this->is( 'line_attributi_da_evidenziare' ),
            'has_content' => $this->hasContent(),
            'show_link' => !$this->is( 'attributi_senza_link' )
        );
    }
}
