<?php

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
    $stateTool = new OpenPAStateTools();
    if ( !$isQuiet )
    {
        $stateTool->setLog( true );
    }
    $stateTool->changeAll();
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

