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

        $this->data['full_identifier'] = $this->data['contentclass']->attribute( 'identifier' ) . '/' . $this->data['contentclass_attribute']->attribute( 'identifier' );
        $this->data['full'] = $this->fullData();
        $this->data['line'] = $this->lineData();
        $this->data['has_content'] = $this->hasContent();
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
            'exclude' => $this->is( 'attributi_da_escludere' ),
            'highlight' => $this->is( 'attributi_da_evidenziare' ),
            'has_content' => $this->hasContent(),
            'show_link' => !$this->is( 'attributi_senza_link' )
        );
    }

    //@todo
    protected function hasContent()
    {
        return $this->data['contentobject_attribute']->attribute( 'has_content' ) || $this->is( 'zero_is_content', array( 'ente_controllato/onere_complessivo' ) );
    }

    protected function lineData()
    {
        return array();
    }
}
