<?php

class ObjectHandlerServiceContentRuoliComune extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['ruoli'] = $this->fetchRuoli();
    }

    protected function fetchRuoli()
    {
        $data = array();
        $data['dipendente'] = eZFunctionHandler::execute(
            'openpa',
            'ruoli',
            array(
                'dipendente_object_id' => $this->container->currentObjectId
            )
        );

        $data['struttura'] = eZFunctionHandler::execute(
            'openpa',
            'ruoli',
            array(
                 'struttura_object_id' => $this->container->currentObjectId
            )
        );

        return $data;
    }    
}