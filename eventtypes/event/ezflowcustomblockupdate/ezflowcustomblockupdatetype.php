<?php
 
class eZFlowCustomBlockupdateType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "ezflowcustomblockupdate";
    
    public function __construct()
    {
        parent::__construct( eZFlowCustomBlockupdateType::WORKFLOW_TYPE_STRING, 'Svuota la cache dei blocchi custom di ezflow' );
    }
 
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $customBlocks = new CustomBlockFinder();
        if ( isset( $parameters['object_id'] ) )
        {
            $objectID = $parameters['object_id'];
            $object = eZContentObject::fetch( $objectID );
            if ( $object )
            {
                $customBlocks->checkObject( $object, true );
            }
        }
        elseif ( isset( $parameters['node_id_list'] ) ) //delete
        {
            foreach( $parameters['node_id_list'] as $node_id )
            {
                $node = eZContentObjectTreeNode::fetch( $node_id );
                $customBlocks->checkNode( $node, true );
            }
        }
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( eZFlowCustomBlockupdateType::WORKFLOW_TYPE_STRING, 'eZFlowCustomBlockupdateType' );
?>