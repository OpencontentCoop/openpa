<?php
class OpenPAAttributeHandler extends OpenPATempletizable
{
    public function __construct( eZContentObjectAttribute $attribute, $params = array() )
    {
        $this->data['contentobject_attribute'] = $attribute;

        $this->data['contentclass_attribute'] = $attribute->attribute( 'contentclass_attribute' );
        $this->data['contentclass'] = eZContentClass::fetch( $this->data['contentclass_attribute']->attribute( 'contentclass_id' ) );
        $this->data['identifier'] = $this->data['contentclass_attribute']->attribute( 'identifier' );
        $this->data['label'] = $this->data['contentclass_attribute']->attribute( 'name' );
        $this->data['data_type_string'] = $this->data['contentclass_attribute']->attribute( 'data_type_string' );
        $this->data['is_information_collector'] = $attribute->attribute( 'is_information_collector' );

        $this->data['full_identifier'] = $this->data['contentclass']->attribute( 'identifier' ) . '/' . $this->data['contentclass_attribute']->attribute( 'identifier' );
        $this->fnData['full'] = 'fullData';
        $this->fnData['line'] = 'lineData';
        $this->data['has_content'] = $this->hasContent();
        parent::__construct();
    }

    public function is( $settingValue, $defaults = array() )
    {
        return $this->inSettings( OpenPAINI::variable( 'GestioneAttributi', $settingValue, $defaults ) );
    }

    protected function inSettings( $settings )
    {
        return in_array( $this->data['full_identifier'], $settings ) || in_array( $this->data['identifier'], $settings );
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
            'contatti' => $this->is( 'attributi_contatti' )
        );
    }

    //@todo
    protected function hasContent()
    {
        $hasContent = $this->data['contentobject_attribute']->attribute( 'has_content' );
        if ( $this->data['contentobject_attribute']->attribute( 'data_type_string' ) == 'ezinteger'
             && $this->data['contentobject_attribute']->toString() == 0 )
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
