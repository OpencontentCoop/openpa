<?php

class OpenPAAttributeGroupClassExtraParameters extends OCClassExtraParametersHandlerBase
{

    public function getIdentifier()
    {
        return 'attribute_group';
    }

    public function getName()
    {
        return "Visualizzazione di attributi raggruppati";
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'contacts';

        return $attributes;
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'contacts':
                return $this->getAttributeIdentifierListByParameter( 'contacts', 1, false );
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