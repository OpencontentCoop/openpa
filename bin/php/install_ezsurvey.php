<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Installa ezsurvey\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$installer = new OpenPASurveyInstaller();
$options = $installer->setScriptOptions( $script );

$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{
    /** @var eZUser $user */
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        //throw new Exception( 'Script non eseguibile sul prototipo' );
    }
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    }

    $installer->beforeInstall( $options );
    $installer->install();
    $installer->afterInstall();

    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    OpenPALog::error( $e->getMessage() );
    $script->shutdown( $errCode, $e->getMessage() );
}
