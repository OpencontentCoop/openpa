<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo consistenza classe\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
                                '',
                                array( 'class'  => 'Identificatore della classe da controllare')
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
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    }
    
    if ( isset( $options['class'] ) )
    {
        $classi = array( $options['class'] );
    }
    else
    {
        $contentClasses = eZContentClass::fetchAllClasses();
        $classi = array();
        foreach( $contentClasses as $class )
        {
            $classi[] = $class->attribute( 'identifier' );
        }
    }
    
    foreach( $classi as $identifier )
    {
        try
        {
            $tools = new OpenPAClassTools( $identifier );
            $tools->compare();
            $result = $tools->getData();                
            if ( $result->missingAttributes )
            {
                OpenPALog::warning( 'Attributi mancanti rispetto al prototipo: ' . count( $result->missingAttributes ) );
                if ( isset( $options['verbose'] ) )
                {
                    foreach( $result->missingAttributes as $identifier => $original )
                    {
                        OpenPALog::notice( " -> $identifier ({$original->DataTypeString})" );
                    }
                }
            }
            if ( $result->extraAttributes )
            {
                OpenPALog::error( 'Attributi aggiuntivi rispetto al prototipo: ' . count( $result->extraAttributes ) );
                if ( isset( $options['verbose'] ) )
                {
                    foreach( $result->extraAttributes as $attribute )
                    {
                        $detail = $result->extraDetails[$attribute->Identifier];
                        OpenPALog::notice( " -> {$attribute->Identifier} ({$attribute->DataTypeString}) ({$detail['count']} oggetti)" );
                    }
                }
            }
            if ( $result->hasDiffAttributes )
            {
                if ( count( $result->errors ) > 0 )
                    OpenPALog::error( 'Attributi che differiscono dal prototipo: ' . count( $result->diffAttributes ) );
                else
                    OpenPALog::notice( 'Attributi che differiscono dal prototipo: ' . count( $result->diffAttributes ) );
                
                foreach( $result->diffAttributes as $identifier => $value )
                {
                    if ( isset( $options['verbose'] ) )
                    {
                        if ( isset( $result->errors[$identifier] ) )
                            OpenPALog::warning( " -> $identifier" );
                        else
                            OpenPALog::notice( " -> $identifier" );
                    }
                        
                    foreach( $value as $diff )
                    {
                        $alert = isset( $result->errors[$identifier] ) && $result->errors[$identifier] == $diff['field_name'] ? '*' : ' ';
                        if ( $alert == '*' )
                            OpenPALog::error( "  $alert {$diff['field_name']} ({$diff['detail']['count']} oggetti)" );
                        elseif ( isset( $options['verbose'] ) )
                            OpenPALog::notice( "  $alert {$diff['field_name']} ({$diff['detail']['count']} oggetti)" );
                    }                        
                }
            }
            if ( $result->hasDiffProperties )
            {
                OpenPALog::notice( 'Proprietà che differiscono dal prototipo: '  . count( $result->diffProperties ) );
                if ( isset( $options['verbose'] ) )
                {
                    foreach( $result->diffProperties as $property )
                    {                        
                        OpenPALog::notice( " -> {$property['field_name']}" );
                    }
                }
            }
        }
        catch( Exception $e )
        {
            OpenPALog::error( $e->getMessage() );
            $errorClassCount++;
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
