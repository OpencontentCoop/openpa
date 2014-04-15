<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo consistenza classe\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:][from:][to:]',
                                '',
                                array( 'class'  => 'Identificatore della classe da controllare',
                                       'from' => "Identificatore dell'attributo da modificare",
                                       'to' => "Nuovo nome identificatore attributo" )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{
    $errorClassCount = 0;
    $errorTreeCount = 0;
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
    
    if ( isset( $options['class'] ) && isset( $options['from'] ) && isset( $options['to'] ) )
    {
        $class = eZContentClass::fetchByIdentifier( $options['class'] );
        if ( !$class instanceof eZContentClass )
        {
            throw new Exception( "La classe {$options['class']} non esiste" );
        }
        $originalAttribute = $class->fetchAttributeByIdentifier( $options['from'] );
        if ( !$originalAttribute instanceof eZContentClassAttribute )
        {
            throw new Exception( "L'attributo {$options['from']} non esiste nella classe {$options['class']}" );
        }
        
        $identifier = trim( $options['to'] );
        $trans = eZCharTransform::instance();
        $identifier = $trans->transformByGroup( $identifier, 'identifier' );
        $alreadyExists = $class->fetchAttributeByIdentifier( $identifier );
        if ( !$alreadyExists )
        {
            $originalAttribute->setAttribute( 'identifier', $identifier );
            $originalAttribute->store();
        }
        else
        {
            throw new Exception( "L'identificatore $identifier è già in uso" );
        }
    }
    else
    {
        throw new Exception( "Inserisci tutti gli argomenti" );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
