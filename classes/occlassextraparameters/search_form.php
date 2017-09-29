<?php

class OpenPASearchFormClassExtraParameters extends OCClassExtraParametersHandlerBase
{

    public function getIdentifier()
    {
        return 'search_form';
    }

    public function getName()
    {
        return "Visualizzazione degli attributi nel form di ricerca";
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'show';
        return $attributes;
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'show':
                return $this->getAttributeIdentifierListByParameter( 'show' );
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