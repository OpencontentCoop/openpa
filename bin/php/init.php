<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA Init\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions(
    '[go-live][reload]',
    '',
    array(
         'go-live' => 'Esegue le operazioni per la messa in produzione',
         'reload' => 'Non esegue le operazioni di installazione iniziale'
    )
);

$script->initialize();
$script->setUseDebugAccumulators( true );

try
{
    $reload = $options['reload'];
    $siteaccess = eZSiteAccess::current();
    $instance = new OpenPAInstance( $siteaccess['name'] );

    $user = eZUser::fetchByName( 'admin' );
    if ( !$user instanceof eZUser )
    {
        throw new Exception( 'Admin user not found' );
    }
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );

    if ( $options['go-live'] )
    {
        $cli->output( "Conferma i parametri di produzione di " . $instance->getName() );

        require( 'golive/url.php' );
        require( 'golive/url_staging.php' );
        require( 'golive/google_id.php' );
        require( 'golive/production_date.php' );
    }
    else
    {
        require( 'init/site_name.php' );
        require( 'init/anonymous_user_login.php' );
        require( 'init/create_app_section.php' );
        require( 'init/reindex.php' );
        require( 'init/clear_cache.php' );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1;
    //$cli->error( $e->getMessage() );
    $script->shutdown( $errCode, $e->getMessage() );
}
