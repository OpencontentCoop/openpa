<?php

class ObjectHandlerServiceContentInfoCollection extends ObjectHandlerServiceBase
{
    protected $infoCollectionAttribute;
    
    function run()
    {        
        $this->fnData['is_information_collector'] = 'hasInfoCollectionAttributes';
        $this->fnData['attributes'] = 'getInfoCollectionAttributes';
        $this->fnData['identifiers'] = 'getInfoCollectionAttributeIdentifiers';
        $this->fnData['extra_identifiers'] = 'getExtraInfoCollectionAttributeIdentifiers';
    }

    protected function getExtraInfoCollectionAttributeIdentifiers()
    {        
        return $this->hasInfoCollectionAttributes() ? $this->infoCollectorAttributesExtra() : array();
    }
    
    protected function hasInfoCollectionAttributes()
    {
        return count($this->getInfoCollectionAttributes() > 0);
    }
    
    protected function getInfoCollectionAttributeIdentifiers()
    {
        return array_keys( $this->getInfoCollectionAttributes() );
    }
    
    protected function getInfoCollectionAttributes()
    {
        if ($this->infoCollectionAttribute === null)
        {
            $this->infoCollectionAttribute = array();
            foreach( $this->container->attributesHandlers as $handler )
            {
                if ( $handler->attribute( 'contentobject_attribute' )->attribute( 'is_information_collector' ) )
                {
                    $this->infoCollectionAttribute[$handler->attribute( 'identifier' )] = $handler;
                }
            }
        }
        return $this->infoCollectionAttribute;
    }

    //@todo
    protected function infoCollectorAttributesExtra()
    {
        return array(
            'recipient',
            'email_receiver',
            'email_cc_receivers',
            'email_bcc_receivers',
            'consenso_trattamento_testo'
        );
    }
}