<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Reindicizza\n\n" ),
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
    
    $pendingAction = OpenPABase::PENDING_ACTION_RENAME_OBJECT;
    
    if ( count( $ids ) > 0 )
    {
        $count = count( $ids );
        $output = new ezcConsoleOutput();
        $progressBarOptions = array( 'emptyChar' => ' ', 'barChar'  => '=' );
        $progressBarOptions['minVerbosity'] = 10;    
        $progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
        $progressBar->start();
        
        foreach( $ids as $id )
        {            
            $progressBar->advance();
            eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( '$pendingAction', '$id' )" );
        }
        $progressBar->finish();
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
