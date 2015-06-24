<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Remove Node\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[node_id:][parent_node_id:]', '', array( 'node_id'  => 'Node id', 'parent_node_id' => 'New parent node id' ) );
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );
try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    if ( $options['node_id'] )
    {        
        $nodeId = trim( $options['node_id'] );
        $parentNodeId = trim( $options['parent_node_id'] );
        $node = eZContentObjectTreeNode::fetch( $nodeId );
        if ( $node instanceof eZContentObjectTreeNode && is_numeric( $parentNodeId ) )
        {
            if ( $node->attribute( 'parent_node_id' ) != $parentNodeId )
            {
                eZContentObjectTreeNodeOperations::move( $nodeId, $parentNodeId );
                $pendingAction = new eZPendingActions(
                    array(
                        'action' => eZSolr::PENDING_ACTION_INDEX_SUBTREE,
                        'created' => time(),
                        'param' => $nodeId
                    )
                );
                $pendingAction->store();
            }            
        }
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
