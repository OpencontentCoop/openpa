<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( 'Controlla il db name' ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );


try
{
    $dbName = eZINI::instance()->variable( 'DatabaseSettings', 'Database' );
    if ( $dbName !== 'ez45_openpa_' . OpenPABase::getCurrentSiteaccessIdentifier() )
    {
        eZCLI::instance()->error( $dbName );    
    }    
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
