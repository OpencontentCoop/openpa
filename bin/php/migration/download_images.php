<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( 'Download images from endpoint' ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[url:]',
    '',
    array( 'url'  => 'Endpoint url' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

try
{
    $db = eZDB::instance();
    $attributeIds = $db->arrayQuery( "SELECT DISTINCT ezimagefile.contentobject_attribute_id FROM ezimagefile;" );
    $images  = array();
    foreach( $attributeIds as $attributeId )
    {
        $id = $attributeId['contentobject_attribute_id'];
        $originals = $db->arrayQuery( "SELECT ezimagefile.filepath FROM ezimagefile WHERE ezimagefile.contentobject_attribute_id = '{$id}' ORDER BY ezimagefile.id;" );
        if ( !empty( $originals ) )
        {
            $temp = array();
            foreach( $originals as $original )
            {
                $temp[] = $original['filepath'];
            }
            sort( $temp );
            $images[] = $temp[0];
        }
    }

    $count = count( $images );
    $cli->warning( "Found $count images" );
    foreach( $images as $image )
    {
        if ( $options['url'] )
        {
            $cli->notice( "Create $image" );
            $name = basename( $image );
            $dir = str_replace( $name, '', $image );
            $url = rtrim( $options['url'] ) . '/';
            $data = eZHTTPTool::getDataByURL( $url . $image );
            eZFile::create( $name, $dir, $data );
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
