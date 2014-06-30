<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Alter ezcontentobject_attribute data_int\n\n" ),
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

    $test = $db->arrayQuery( 'SELECT data_type FROM information_schema.columns WHERE column_name = \'data_int\' AND table_name = \'ezcontentobject_attribute\';' );
    if ( $test[0]['data_type'] != 'bigint' )
    {
        $db->query( 'ALTER TABLE ezcontentobject_attribute ALTER COLUMN data_int TYPE BIGINT;' );
        $db->query( 'ALTER TABLE ezcontentobject_attribute ALTER COLUMN sort_key_int TYPE BIGINT;' );
        OpenPALog::notice( 'La tabella ezcontentobject_attribute è stata aggiornata' );
    }
    else
    {
        OpenPALog::notice( 'La tabella ezcontentobject_attribute è ok' );
    }
    $script->shutdown();
}
catch( eZDBException $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}