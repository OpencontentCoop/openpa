<?php

class ObjectHandlerServiceContentReverseRelated extends ObjectHandlerServiceBase
{
    function run()
    {        
        $this->fnData['has_data'] = 'hasData';
    }
    
    protected function hasData()
    {
        $count = 0;

        if ( !in_array($this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'nascondi_blocco_riferimenti', array('folder') ))) {

            $params = array('AsObject' => false, 'LoadDataMap' => false, 'Limit' => 1);

            $excludeClasses = OpenPAINI::variable('GestioneClassi', 'escludi_da_riferimenti', array());
            if (!empty( $excludeClasses )) {
                $params['RelatedClassIdentifiers'] = array();
                $classRepo = new \Opencontent\Opendata\Api\ClassRepository();
                $all = $classRepo->listAll();
                foreach ($all as $class) {
                    if (!in_array($class['identifier'], $excludeClasses)) {
                        $params['RelatedClassIdentifiers'][] = $class['identifier'];
                    }
                }
            }
            if (!in_array($this->container->currentClassIdentifier,
                OpenPAINI::variable('GestioneClassi', 'classi_che_producono_contenuti', array()))
            ) {
                $list = $this->container->getContentObject()->reverseRelatedObjectList(false, false, false, $params);
                $count = count($list);
            } elseif ($this->container->currentClassIdentifier == 'politico') {

                $params['AllRelations'] = eZContentFunctionCollection::contentobjectRelationTypeMask(array('attribute'));
                $list = $this->container->getContentObject()->reverseRelatedObjectList(false, 'politico/membri', false,
                    $params);
                $count = count($list);
            } elseif ($this->container->currentClassIdentifier == 'organo_politico') {
                $params['AllRelations'] = eZContentFunctionCollection::contentobjectRelationTypeMask(array('attribute'));
                $list = $this->container->getContentObject()->reverseRelatedObjectList(false,
                    'gemellaggio/circoscrizione', false, $params);
                $count = count($list);
            }
        }
        return $count > 0;
    }
}
