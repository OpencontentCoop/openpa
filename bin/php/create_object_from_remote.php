<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Copia oggetto del prototipo via API\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();
$options = $script->getOptions( '[locale_parent_object_remote_id:][remote_node_id:][test:]',
                                '',
                                array( 'locale_parent_object_remote_id'  => 'RemoteID dell\'oggetto contenitore locale (esempio: 67045e53aedf0fd398627d63f46182c3)',
                                       'remote_node_id' => 'ID Nodo remoto (esempio: 998)',
                                       'test' => 'Se uguale a 1: esegue un test senza creare l\'oggetto' )
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
    
    if ( !isset( $options['locale_parent_object_remote_id'] ) )
    {
        throw new Exception( "specificare locale_parent_object_remote_id" );
    }
    
    if ( !isset( $options['remote_node_id'] ) )
    {
        throw new Exception( "specificare remote_node_id" );
    }
    
    //@todo mettere in un openpa.ini
    $remoteNodeLink = 'http://openpa.opencontent.it/api/opendata/v1/content/node/' . $options['remote_node_id'];
    $remoteNode = OpenPAApiNode::fromLink( $remoteNodeLink );
    
    if ( !$remoteNode )
    {
        throw new Exception( "Sorgente remota $remoteNodeLink non raggiungibile" );
    }
    
    $parentObject = eZContentObject::fetchByRemoteID( $options['locale_parent_object_remote_id'] );
    if ( !$parentObject instanceof eZContentObject )
    {
       throw new Exception( "Oggetto locale non trovato" );
    }
    
    if ( !$parentObject->attribute( 'main_node_id' ) )
    {
       throw new Exception( "Node locale non trovato" );
    }

    if ( $options['test'] == 1 )
    {
        OpenPALog::notice( 'Remoto: ' . $remoteNode->metadata['objectName'] );
        OpenPALog::notice( 'Contenitore locale: ' . $parentObject->attribute( 'name' ) );
    }
    else
    {
        $new = $remoteNode->createContentObject( $parentObject->attribute( 'main_node_id' ) );
        if ( !$new instanceof eZContentObject )
        {
            throw new Exception( "Errore creando il nuovo oggetto: controlla i log" );
        }
    }
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
