<?php

class ObjectHandlerServiceContentRuoliComune extends ObjectHandlerServiceBase
{
    protected $usersAndRolesBySubtree;
    
    function run()
    {
        $this->fnData['ruoli'] = 'fetchRuoli';
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
        
        $data['subtree'] = eZFunctionHandler::execute(
            'openpa',
            'ruoli',
            array(
                 'subtree_array' => array( $this->container->currentNodeId )
            )
        );
        
        return $data;
    }    
}