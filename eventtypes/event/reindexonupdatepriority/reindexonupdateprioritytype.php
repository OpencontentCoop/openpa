<?php

class ReindexOnUpdatePriorityType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "reindexonupdatepriority";

    public function __construct()
    {
        $this->eZWorkflowEventType( ReindexOnUpdatePriorityType::WORKFLOW_TYPE_STRING, 'Reindicizzazione del contenuto dopo aggiornamento priorità' );
        $this->setTriggerTypes( array( 'content' => array( 'updatepriority' => array( 'after' ) ) ) );
    }

    /**
     * @param eZWorkflowProcess $process
     * @param eZWorkflowEvent $event
     *
     * @return int
     */
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $nodeIdList = $parameters['priority_id'];

        /** @var eZContentObjectTreeNode[] $nodes */
        $nodes = eZContentObjectTreeNode::fetch( $nodeIdList );        
        foreach( $nodes as $node )
        {
            eZSearch::addObject( $node->attribute( 'object' ), true );
        }
        
        $object = eZContentObject::fetchByNodeID( $parameters['node_id'] );
        if ( $object instanceof eZContentObject )
        {
            eZContentCacheManager::clearContentCache( $object->attribute( 'id' ) );
        }
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( ReindexOnUpdatePriorityType::WORKFLOW_TYPE_STRING, 'ReindexOnUpdatePriorityType' );
