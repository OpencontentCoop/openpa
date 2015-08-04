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
        $priorityArray = $parameters['priority'];

        /** @var eZContentObjectTreeNode[] $nodes */
        $nodes = eZContentObjectTreeNode::fetch( $priorityArray );
        foreach( $nodes as $node )
        {
            eZSearch::addObject( $node->attribute( 'object' ), true );
        }
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( ReindexOnUpdatePriorityType::WORKFLOW_TYPE_STRING, 'reindexonupdatepriority' );
