<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Conversione classe organigramma\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );

$conversionParams = array(
    'name' => 'name',
    'short_name' => 'short_name',
    'abstract' => 'abstract',
    'description' => 'description',
    'image' => 'image'
);
$conversionClass = 'organigramma';

try
{
    $ini = eZINI::instance( 'override.ini' );
    $done = false;
    foreach( $ini->groups() as $group )
    {
        if ( $group['MatchFile'] == 'full/folder_mappa_organizzazione.tpl' )
        {
            if ( isset( $group['Match']['node'] ) )
            {
                $nodeID = $group['Match']['node'];
                $node = eZContentObjectTreeNode::fetch( $nodeID );
                if ( $node instanceof eZContentObjectTreeNode )
                {
                    if ( $node->attribute( 'class_identifier' ) == 'pagina_sito' )
                    {
                        if ( $node->attribute( 'name' ) == 'Organigramma' )
                        {
                            $temp = conversionFunctions::convertObject( $node->attribute( 'contentobject_id' ), $conversionClass, $conversionParams );
                            if ( $temp )
                            {
                                OpenpaLog::notice( 'Ok!' );
                                $done = true;
                                break;
                            }
                            else
                            {
                                throw new Exception( "Conversione fallita" );
                            }
                        }
                        else
                        {
                            throw new Exception( "Il nodo $nodeID non si chiama 'Organigramma'" );
                        }
                    }
                    else
                    {
                        throw new Exception( "Il nodo $nodeID non è di tipo pagina del sito" );
                    }
                }
                else
                {
                    throw new Exception( "Il nodo $nodeID non esiste" );
                }
            }
            else
            {
                throw new Exception( "Non trovo il nodo nella configurazione di override.ini" );
            }
        }        
    }
    if ( !$done )
    {
        throw new Exception( "Non trovo la configurazione di override.ini" );
    }

    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
