<?php
 
class ReindexRelationsType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "reindexrelations";
    
    const LOGFILE = 'openpa_worflow_reindexrelations.log';
    
    public function __construct()
    {
        parent::__construct( ReindexRelationsType::WORKFLOW_TYPE_STRING, 'Reindicizzazione degli oggetti correlati' );
    }
 
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        if ( isset( $parameters['object_id'] ) )
        {
            $object = eZContentObject::fetch( $parameters['object_id'] );
            if ( $object instanceof eZContentObject )
            {
                //$relations = $object->relatedContentObjectList( false, false, 0, false, array( 'AllRelations' => true ) );
                $reverseRelations = $object->reverseRelatedObjectList( false, false, 0, false, array( 'AllRelations' => true ) );
                $index = array();
                //foreach( $relations as $relation )
                //{
                //    /** @var $relation eZContentObject */
                //    $index[] = $relation->attribute( 'id' );
                //}
                foreach( $reverseRelations as $relation )
                {
                    /** @var $relation eZContentObject */
                    $index[] = $relation->attribute( 'id' );
                }
                $index = array_unique( $index );
                if ( count( $index ) > 0 )
                {
                    $pendingAction = new eZPendingActions(
                        array(
                             'action' => OpenPABase::PENDING_ACTION_INDEX_OBJECTS,
                             'created' => time(),
                             'param' => implode( '-', $index )
                        )
                    );
                    $pendingAction->store();
                }
            }
        }

        
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( ReindexRelationsType::WORKFLOW_TYPE_STRING, 'reindexrelationstype' );
?>