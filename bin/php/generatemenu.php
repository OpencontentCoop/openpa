<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo consistenza classe\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( $script->isQuiet() ? OpenPALog::ERROR : OpenPALog::ALL );

try
{
    OpenPAMenuTool::generateAllMenus();
    eZCache::clearByTag( 'template' );
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
