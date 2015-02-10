<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Controllo degli alias di immagine\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );

function checkItems( $base, $items )
{
    $groupCheck = array();
    foreach( $items as $item )
    {
        if ( is_dir( "$base/$item" ) )
        {
            checkItems( "$base/$item", eZDir::findSubitems( "$base/$item" ) );
        }
        else
        {
            $groupCheck[] = "$base/$item";
        }
    }
    if ( !empty( $groupCheck ) ) checkGroup( $groupCheck );
}

function checkGroup( $items )
{
    global $options;
    
    $copyItems = $items;
    $aliases = eZINI::instance( 'image.ini' )->variable( 'AliasSettings', 'AliasList' );
    foreach( $items as $key => $item )
    {
        foreach( $aliases as $alias )
        {
            if ( strpos( $item, '_' . $alias ) !== false )
            {
                unset( $items[$key] );
            }
        }
    }

    if ( count( $items ) == 0 )
    {
        $parts = explode( '/', $copyItems[0] );
        array_pop( $parts );
        $attributeParts = explode( '-', array_pop( $parts ) );
        $attribute = eZContentObjectAttribute::fetch( $attributeParts[0], $attributeParts[1] );
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            if ( $attribute->attribute( 'object' ) instanceof eZContentObject )
            {
                $dataMap = $attribute->attribute( 'object' )->attribute( 'data_map' );
                if ( isset( $dataMap[$attribute->attribute('contentclass_attribute_identifier')]) )
                {
                    $control = $dataMap[$attribute->attribute('contentclass_attribute_identifier')];
                    if ( $control->attribute( 'version' ) == $attribute->attribute( 'version' ) && intval( $attribute->attribute( 'object' )->attribute( 'main_node_id' ) ) > 0 )
                    {
                        eZCLI::instance()->error( "Immagine originale non trovata nell'oggetto #{$attribute->attribute( 'object' )->attribute( 'id' )} nodo #{$attribute->attribute( 'object' )->attribute( 'main_node_id' )}" );
                        if ( $options['verbose'] !== null ) print_r( $copyItems );
                    }
                    else
                    {
                        if ( $options['verbose'] !== null ) eZCLI::instance()->warning( "Oggetto {$control->attribute('id')} ha un'altra immagine rispetto a {$copyItems[0]} o simili" );
                    }
                }
                else
                {
                    if ( $options['verbose'] !== null ) eZCLI::instance()->warning( "Classe cambiata per {$copyItems[0]} e simili" );
                }
            }
            else
            {
                if ( $options['verbose'] !== null ) eZCLI::instance()->warning( "Nessun oggetto per {$copyItems[0]} e simili" );
            }
        }
        else
        {
            if ( $options['verbose'] !== null ) eZCLI::instance()->warning( "Nessun attributo per {$copyItems[0]} e simili" );
        }
    }
}

try
{
    $base = eZSys::storageDirectory() . '/images';
    $items = eZDir::findSubitems( $base );
    checkItems( $base, $items );

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
