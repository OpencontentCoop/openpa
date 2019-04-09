<?php

class RefreshOpenpaPagedata extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "refreshopenpapagedata";

    public function __construct()
    {
        parent::__construct(RefreshOpenpaPagedata::WORKFLOW_TYPE_STRING,
            "Aggiorna la OpenPA Pagedata cache alla modifica della homepage o di un'area tematica e svuota la cache dei template");
    }

    public function execute($process, $event)
    {
        $ini = eZINI::instance('openpa.ini');
        $areeIdentifiers = $ini->hasVariable('AreeTematiche', 'IdentificatoreAreaTematica') ?
            $ini->variable('AreeTematiche', 'IdentificatoreAreaTematica') :
            array('area_tematica');

        $parameters = $process->attribute('parameter_list');
        $trigger = $parameters['trigger_name'];
        if ($trigger == 'post_publish') {
            if (isset($parameters['object_id'])) {
                $object = eZContentObject::fetch((int)$parameters['object_id']);
                if ($object instanceof eZContentObject) {
                    if ($object->attribute('main_node_id') == OpenPaFunctionCollection::fetchHome()->attribute('node_id')){
                        OpenPAPageData::clearOnModifyHomepage();
                        eZCache::clearByTag('template');
                    }

                    if (in_array($object->attribute('class_identifier'), $areeIdentifiers) && $object->attribute('current_version') == 1){
                        OpenPAPageData::clearOnModifyAreaTematica();
                        eZCache::clearByTag('template');
                    }
                }
            }
        } elseif ($trigger == 'pre_delete') {
            /** @var eZContentObjectTreeNode[] $nodeList */
            $nodeList = eZContentObjectTreeNode::fetch($parameters['node_id_list']);
            if ($nodeList instanceof eZContentObjectTreeNode) {
                $nodeList = array($nodeList);
            }
            foreach ($nodeList as $node) {
                if (in_array($node->attribute('class_identifier'), $areeIdentifiers)){
                    OpenPAPageData::clearOnModifyAreaTematica();
                    eZCache::clearByTag('template');
                }
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType(RefreshOpenpaPagedata::WORKFLOW_TYPE_STRING, 'RefreshOpenpaPagedata');
