<?php

class OpenPATableViewClassExtraParameters extends OCClassExtraParametersHandlerBase
{

    public function getIdentifier()
    {
        return 'table_view';
    }

    public function getName()
    {
        return "Visualizzazione degli attributi in forma tabellare (template full)";
    }

    public function attributes()
    {
        $attributes = parent::attributes();

        $attributes[] = 'show';
        $attributes[] = 'show_label';
        $attributes[] = 'show_empty';
        $attributes[] = 'collapse_label';
        $attributes[] = 'highlight';
        $attributes[] = 'hide_link';

        return $attributes;
    }

    public function attribute( $key )
    {
        switch( $key )
        {
            case 'show':
                return $this->getAttributeIdentifierListByParameter( 'show', 1, false  );

            case 'show_label':
                return $this->getAttributeIdentifierListByParameter( 'show_label', 1, false  );

            case 'show_empty':
                return $this->getAttributeIdentifierListByParameter( 'show_empty', 1, false );

            case 'collapse_label':
                return $this->getAttributeIdentifierListByParameter( 'collapse_label', 1, false );

            case 'hide_link':
                return $this->getAttributeIdentifierListByParameter( 'hide_link', 1, false  );

            case 'highlight':
                return $this->getAttributeIdentifierListByParameter( 'highlight', 1, false );
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