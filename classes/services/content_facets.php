<?php

class ObjectHandlerServiceContentFacets extends ObjectHandlerServiceBase
{
    function run()
    {        
        $items = $this->getItems();        
        $this->data['has_data'] = count( $items ) > 0;
        $this->data['items'] = $items;
    }

    protected function getItems()
    {        
        $items = array();
        if ( in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'classi_che_producono_contenuti', array() ) ) )
        {
            $items = eZFunctionHandler::execute(
                'openpa',
                'faccette_classi_oggetti_correlati_inversi',
                array(
                     'object' => $this->container->getContentObject(),
                     'class_filter_type', 'exclude',
                     'class_filter_array', OpenPAINI::variable( 'GestioneClassi', 'classi_da_escludere_da_blocco_ezfind', array() )
                )
            );
        }
        return $items;
    }
    


}