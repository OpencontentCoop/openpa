<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Cancella la cache per gli oggetti della classe selezionata\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
                                '',
                                array( 'class'  => 'Identificatore della classe' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{
    if ( isset( $options['class'] ) )
    {
        $classIdentifier = $options['class'];
    }
    else
    {
        throw new Exception( "Specificare la classe" );
    }
    
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( !$class instanceof eZContentClass )
    {
        throw new Exception( "Classe $classIdentifier non trovata" );
    }
    
    $objects = eZPersistentObject::fetchObjectList( eZContentObject::definition(),
                                                    array( 'id' ),
                                                    array( 'contentclass_id' => $class->attribute( 'id' ) ),
                                                    null,
                                                    null,
                                                    false );
    $ids = array();
    foreach( $objects as $object )
    {
        $ids[] = $object['id'];
    }
    
    if ( count( $ids ) > 0 )
    {
        eZCache::clearByID( 'template' );
        eZContentCacheManager::clearContentCacheIfNeeded( $ids );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
