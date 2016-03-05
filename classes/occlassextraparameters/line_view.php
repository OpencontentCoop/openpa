<?php

class OpenPALineViewClassExtraParameters extends OCClassExtraParametersHandlerBase
{

    public function getIdentifier()
    {
        return 'line_view';
    }

    public function getName()
    {
        return "Visualizzazione degli attributi nella vista elenco (template line)";
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'show';
        $attributes[] = 'show_label';
        $attributes[] = 'show_link';
        return $attributes;
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'show':
                return $this->getAttributeIdentifierListByParameter( 'show', 1, false );

            case 'show_label':
                return $this->getAttributeIdentifierListByParameter( 'show_label', 1, false );

            case 'show_link':
                return $this->getAttributeIdentifierListByParameter( 'show_link', 1, false );
        }

        return parent::attribute( $key );
    }

    protected function attributeEditTemplateUrl()
    {
        return 'design:openpa/extraparameters/' . $this->getIdentifier() . '/edit_attribute.tpl';
    }

    protected function classEditTemplateUrl()
    {
        return 'design:openpa/extraparameters/' . $this->getIdentifier() . '/edit_class.tpl';
    }

    public function storeParameters( $data )
    {
        parent::storeParameters( $data );
        OpenPAINI::clearDynamicIniCache();
    }
}