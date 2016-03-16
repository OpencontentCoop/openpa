<?php

require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Conversione classe organigramma\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[id:]',
    '',
    array( 'id'  => 'ID dell\'oggetto da parsare')
);

$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();
$cli->setUseStyles( true );
$cli->setIsQuiet( $isQuiet );

/** @var eZUser $user */
$user = eZUser::fetchByName( 'admin' );
if ( $user )
{
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
}
else
{    
    throw new InvalidArgumentException( "Non esiste un utente admin" ); 
}

try
{
    $stateTools = new OpenPAStateTools();
    if ( !$isQuiet )
    {
        $stateTools->setLog( true );
    }

    $stateTools->changeState( $options['id'] );
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}
$script->shutdown();

