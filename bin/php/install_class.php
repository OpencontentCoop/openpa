<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Installa classe se non esiste\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
                                '',
                                array( 'class'  => 'Identificatore della classe da installare')
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{    
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    }    
    if ( isset( $options['class'] ) )
    {
        $identifier = $options['class'];
    }
    else
    {
        throw new Exception( "Specificare l'identificatore della classe" );
    }
    if ( eZContentClass::fetchByIdentifier( $options['class'] ) instanceof eZContentClass )
    {
        throw new Exception( "La classe $identifier esiste già" );
    }
    else
    {        
        $tools = new OpenPAClassTools( $identifier, true );
        $tools->sync();
    }    
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
