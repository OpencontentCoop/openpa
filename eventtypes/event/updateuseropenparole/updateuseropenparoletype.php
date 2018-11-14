<?php

class UpdateUserOpenpaRole extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "updateuseropenparole";

    public function __construct()
    {
        parent::__construct(UpdateUserOpenpaRole::WORKFLOW_TYPE_STRING,
            "Aggiorna l'attributo ruolo del utente in base al ruolo relazionato");
    }

    public function execute($process, $event)
    {
        $parameters = $process->attribute('parameter_list');
        $trigger = $parameters['trigger_name'];
        if ($trigger == 'post_publish') {
            if (isset($parameters['object_id'])) {
                $object = eZContentObject::fetch((int)$parameters['object_id']);
                if ($object instanceof eZContentObject && $object->attribute('class_identifier') == 'ruolo') {
                    $dataMap = $object->dataMap();
                    $roleName = $object->attribute('name');

                    if (isset($dataMap['utente']) && $dataMap['utente']->hasContent()
                        && isset($dataMap['struttura_di_riferimento'])
                        && $dataMap['struttura_di_riferimento']->hasContent()) {

                        $users = OpenPABase::fetchObjects(explode('-', $dataMap['utente']->toString()));

                        $strutture = OpenPABase::fetchObjects(explode('-', $dataMap['struttura_di_riferimento']->toString()));
                        foreach ($strutture as $struttura) {
                            $roleName .= ' ' . $struttura->attribute('name');
                        }

                        foreach ($users as $user) {
                            $userDataMap = $user->dataMap();
                            if (isset($userDataMap['ruolo'])
                                && !$userDataMap['ruolo']->hasContent()
                                && $userDataMap['ruolo']->attribute('data_type_string') == eZStringType::DATA_TYPE_STRING) {

                                $userDataMap['ruolo']->fromString($roleName);
                                $userDataMap['ruolo']->store();
                                eZSearch::addObject($user);
                                eZContentCacheManager::clearContentCacheIfNeeded($user->attribute('id'));
                            }
                        }
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
                if ($node->attribute('class_identifier') == 'ruolo') {
                    $dataMap = $node->object()->dataMap();
                    $roleName = $node->attribute('name');

                    if (isset($dataMap['utente']) && $dataMap['utente']->hasContent()
                        && isset($dataMap['struttura_di_riferimento'])
                        && $dataMap['struttura_di_riferimento']->hasContent()) {

                        $users = OpenPABase::fetchObjects(explode('-', $dataMap['utente']->toString()));

                        $strutture = OpenPABase::fetchObjects(explode('-', $dataMap['struttura_di_riferimento']->toString()));
                        foreach ($strutture as $struttura) {
                            $roleName .= ' ' . $struttura->attribute('name');
                        }
                        foreach ($users as $user) {
                            $userDataMap = $user->dataMap();
                            if (isset($userDataMap['ruolo'])
                                && $userDataMap['ruolo']->hasContent()
                                && $userDataMap['ruolo']->attribute('data_type_string') == eZStringType::DATA_TYPE_STRING) {

                                if ($userDataMap['ruolo']->toString() == $roleName) {
                                    $userDataMap['ruolo']->fromString('');
                                    $userDataMap['ruolo']->store();
                                    eZSearch::addObject($user);
                                    eZContentCacheManager::clearContentCacheIfNeeded($user->attribute('id'));
                                }
                            }
                        }
                    }
                }
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType(UpdateUserOpenpaRole::WORKFLOW_TYPE_STRING, 'UpdateUserOpenpaRole');
