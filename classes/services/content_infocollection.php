<?php

class ObjectHandlerServiceContentInfoCollection extends ObjectHandlerServiceBase
{
    function run()
    {
        $infoCollections = $this->getInfoCollectionAttributes();
        $this->data['is_information_collector'] = count( $infoCollections ) > 0;
        $this->data['attributes'] = $infoCollections;
        $this->data['identifiers'] = array_keys( $infoCollections );
        $this->data['extra_identifiers'] = $this->data['is_information_collector'] ? $this->infoCollectorAttributesExtra() : array();
    }

    protected function getInfoCollectionAttributes()
    {
        $data = array();
        foreach( $this->container->attributesHandlers as $handler )
        {
            if ( $handler->attribute( 'contentobject_attribute' )->attribute( 'is_information_collector' ) )
            {
                $data[$handler->attribute( 'identifier' )] = $handler;
            }
        }
        return $data;
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