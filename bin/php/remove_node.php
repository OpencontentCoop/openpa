<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Remove Node\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[node_id:]', '', array( 'node_id'  => 'Node id') );
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );
try
{
    if ( $options['node_id'] )
    {        
        $nodeId = trim( $options['node_id'] );
        OpenPALog::notice( "Remove $nodeId" );
        eZContentObjectTreeNode::removeNode( $nodeId );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}