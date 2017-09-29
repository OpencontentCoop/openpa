<?php

class ObjectHandlerServiceContentFacets extends ObjectHandlerServiceBase
{
    private static $_cachedItems;
    
    function run()
    {                
        $this->fnData['has_data'] = 'getItemsCount';
        $this->fnData['items'] = 'getItems';
    }

    protected function getItemsCount()
    {
        return count( (array)$this->getItems() );
    }
    
    protected function getItems()
    {        
        if ( in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'classi_che_producono_contenuti', array() ) ) )
        {
            if ( !isset( self::$_cachedItems[$this->container->getContentObject()->attribute( 'id' )] ) )
            {
                $excludeClasses = OpenPAINI::variable( 'GestioneClassi', 'classi_da_escludere_da_blocco_ezfind', array() );
                self::$_cachedItems[$this->container->getContentObject()->attribute( 'id' )] = eZFunctionHandler::execute(
                    'openpa',
                    'faccette_classi_oggetti_correlati_inversi',
                    array(
                         'object' => $this->container->getContentObject(),
                         'class_filter_type' => 'exclude',
                         'class_filter_array' => $excludeClasses
                    )
                );   
            }
            return self::$_cachedItems[$this->container->getContentObject()->attribute( 'id' )];
        }
        return array();
    }
    


}