<?php
if ( !$isQuiet )
{
    $cli->output( "Starting processing pending search engine modifications" );
}

$eZSolr = eZSearch::getEngine();
if ( !( $eZSolr instanceof eZSolr ) )
{
        $script->shutdown( 1, 'The current search engine plugin is not eZSolr' );
}

$contentObjects = array();
$db = eZDB::instance();

$key = OpenPABase::PENDING_ACTION_INDEX_OBJECTS;


$entries = $db->arrayQuery( "SELECT param FROM ezpending_actions WHERE action = '$key'" );

if ( is_array( $entries ) && count( $entries ) != 0 )
{        
    foreach ( $entries as $entry )
    {
        $objectIDs = $entry['param'];
        $items = explode( '-', $objectIDs );
        foreach ( $items as $objectID )
        {
            $cli->output( "\tIndexing object ID #$objectID" );
            $object = eZContentObject::fetch( $objectID );
            if ( $object )
            {                    
                $eZSolr->addObject( $object, false );
            }
            eZContentObject::clearCache();
        }
        $db->begin();
        $db->query( "DELETE FROM ezpending_actions WHERE action = '$key' AND param = '$objectIDs'" );
        $db->commit();
    }
    $eZSolr->commit();
    
}


if ( !$isQuiet )
{
    $cli->output( "Done" );
}