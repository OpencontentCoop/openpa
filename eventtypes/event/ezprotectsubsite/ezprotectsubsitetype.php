<?php

class eZProtectSubsiteType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "ezprotectsubsite";
    
	function __construct()
    {
        parent::__construct( eZProtectSubsiteType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'opencontent', 'Protect subsite' ) );
        $this->setTriggerTypes( array( 'content' => array( 'read' => array( 'before' ) ) ) );
    }

    function execute( $process, $event )
    {
        $parameterList = $process->attribute( 'parameter_list' );
        $nodeID = $parameterList['node_id'];
        $userID = $parameterList['user_id'];
        $languageCode = $parameterList['language_code'];
        
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !OpenPASubsiteTools::isNodeInCurrentSiteaccess( $node ) )
        {            
            header( 'Location: ' . OpenPASubsiteTools::redirectUri() );
        }
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }

}

eZWorkflowEventType::registerEventType( eZProtectSubsiteType::WORKFLOW_TYPE_STRING, 'eZProtectSubsiteType' );

?>
