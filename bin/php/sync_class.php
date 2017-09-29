<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo consistenza classe\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:][force][removeExtras][install]',
                                '',
                                array( 'class'  => 'Identificatore della classe da sincronizzare',
                                       'force' => 'Forza la sincronizzazione',
                                       'removeExtras' => 'Forza la rimozione di attributi extra prototipo',
                                       'install' => 'Installa la classe se non esiste' )
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
    
    $force = (bool) $options['force'];
    $removeExtras = (bool) $options['removeExtras'];
    $install = (bool) $options['install'];
    
    foreach( $classi as $identifier )
    {
        try
        {
            $tools = new OpenPAClassTools( $identifier, $install );
            $tools->compare();
            $result = $tools->getData();                
            if ( $result->missingAttributes )
            {
                OpenPALog::error( 'Attributi mancanti rispetto al prototipo' );
                foreach( $result->missingAttributes as $identifier => $original )
                {
                    OpenPALog::notice( " -> $identifier ({$original->DataTypeString})" );
                }
            }
            if ( $result->extraAttributes )
            {
                OpenPALog::error( 'Attributi aggiuntivi rispetto al prototipo' );
                foreach( $result->extraAttributes as $attribute )
                {
                    $detail = $result->extraDetails[$attribute->Identifier];
                    OpenPALog::notice( " -> {$attribute->Identifier} ({$attribute->DataTypeString}) ({$detail['count']} oggetti)" );
                }
            }
            if ( $result->hasDiffAttributes )
            {
                $identifiers = array_keys($result->diffAttributes);
                $errors = array_intersect( array_keys($result->errors), $identifiers );
                $warnings = array_intersect( array_keys($result->warnings), $identifiers );
                
                if ( count( $errors ) > 0 )
                    OpenPALog::error( 'Attributi che differiscono dal prototipo: ' . count( $result->diffAttributes ) );
                elseif ( count( $warnings ) > 0 )
                    OpenPALog::warning( 'Attributi che differiscono dal prototipo: ' . count( $result->diffAttributes ) );
                else
                    OpenPALog::notice( 'Attributi che differiscono dal prototipo: ' . count( $result->diffAttributes ) );
                
                if ( isset( $options['verbose'] ) )
                {
                    foreach( $result->diffAttributes as $identifier => $value )
                    {
                        if ( isset( $result->errors[$identifier] ) )
                            OpenPALog::error( " -> $identifier" );
                        elseif ( isset( $result->warnings[$identifier] ) )
                            OpenPALog::warning( " -> $identifier" );
                        else
                            OpenPALog::notice( " -> $identifier" );                    
                            
                        foreach( $value as $diff )
                        {                        
                            if ( isset( $result->errors[$identifier][$diff['field_name']] ) )
                                OpenPALog::error( "    {$diff['field_name']}" );
                            elseif ( isset( $result->warnings[$identifier][$diff['field_name']] ) )
                                OpenPALog::warning( "    {$diff['field_name']}" );
                            else
                                OpenPALog::notice( "    {$diff['field_name']}" );
                        }                        
                    }
                }
            }
            if ( $result->hasDiffProperties )
            {
                if ( isset( $result->errors['properties'] ) )
                    OpenPALog::notice( 'Proprietà che differiscono dal prototipo: '  . count( $result->diffProperties ) );
                elseif ( isset( $result->warnings['properties'] ) )
                    OpenPALog::notice( 'Proprietà che differiscono dal prototipo: '  . count( $result->diffProperties ) );
                else
                    OpenPALog::notice( 'Proprietà che differiscono dal prototipo: '  . count( $result->diffProperties ) );
                
                if ( isset( $options['verbose'] ) )
                {
                    foreach( $result->diffProperties as $property )
                    {                        
                        if ( isset( $result->errors['properties'][$property['field_name']] ) )
                            OpenPALog::error( "    {$property['field_name']}" );
                        elseif ( isset( $result->warnings['properties'][$property['field_name']] ) )
                            OpenPALog::warning( "    {$property['field_name']}" );
                        else
                            OpenPALog::notice( "    {$property['field_name']}" );
                    }
                }
            }
            $tools->sync( $force, $removeExtras );
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
