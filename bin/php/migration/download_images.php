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
        $attributes = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(),
                                                null,
                                                array( "id" => $id ),
                                                array( 'version' => 'desc' ),
                                                array( 'offset' => 0, 'length' => 1 ) );
        if ( isset( $attributes[0] ) && $attributes[0] instanceof eZContentObjectAttribute )
        {
            if ( $attributes[0]->content() instanceof eZImageAliasHandler )
            {
                $data = $attributes[0]->content()->attribute( 'original' );
                if ( isset( $data['full_path'] ) )
                {
                    $images[] = $data['full_path'];
                }
            }
        }
        //$originals = $db->arrayQuery( "SELECT * FROM ezimagefile WHERE ezimagefile.contentobject_attribute_id = '{$id}' ORDER BY ezimagefile.filepath DESC;" );
        //if ( !empty( $originals ) )
        //{
        //    $temp = array();
        //    foreach( $originals as $original )
        //    {
        //        $temp[] = $original['filepath'];
        //    }
        //    sort( $temp );            
        //    $images[] = $temp[0];
        //}        
    }    
    sort( $images );

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
        else
        {
            $cli->notice( "$image" );
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
