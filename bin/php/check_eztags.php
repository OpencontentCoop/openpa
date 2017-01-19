<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA check eztags installation status\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{
    $db = eZDB::instance();
    $db->setErrorHandling( eZDB::ERROR_HANDLING_EXCEPTIONS );

    $test = $db->arrayQuery( 'SELECT id FROM eztags;' );
    
    OpenPALog::warning( 'Ci sono ' . count($test) . ' eztags registrati' );
    
    $script->shutdown();
}
catch( eZDBException $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, 'Non installato' );
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}