<?php
/**
 * Reindicizza su Solr gli oggetti accodati dal workflow event_reindexrelations.
 * Lavora a lotti (vedi [ReindexRelations] in openpa.ini) e lascia in coda quello che Solr non accetta.
 */
if ( !$isQuiet )
{
    $cli->output( "Starting processing pending search engine modifications" );
}

$eZSolr = eZSearch::getEngine();
if ( !( $eZSolr instanceof eZSolr ) )
{
    $script->shutdown( 1, 'The current search engine plugin is not eZSolr' );
}

$maxPerRun = OpenPAReindexRelations::intSetting( 'MaxPerRun', OpenPAReindexRelations::DEFAULT_MAX_PER_RUN );
$maxAgeDays = OpenPAReindexRelations::intSetting( 'MaxAgeDays', OpenPAReindexRelations::DEFAULT_MAX_AGE_DAYS );

$indexer = function ( $objectID ) use ( $eZSolr, $cli, $isQuiet )
{
    $object = eZContentObject::fetch( $objectID );
    if ( !$object instanceof eZContentObject || !$object->attribute( 'main_node_id' ) )
    {
        return null; // cancellato o senza nodo principale: niente da indicizzare
    }
    if ( !$isQuiet )
    {
        $cli->output( "\tIndexing object ID #$objectID" );
    }
    $result = $eZSolr->addObject( $object, false );
    eZContentObject::clearCache();

    return $result ? true : false;
};

$stats = OpenPAReindexRelations::processPending( $indexer, $maxPerRun, $maxAgeDays );

if ( $stats['indexed'] > 0 )
{
    $eZSolr->commit();
}

if ( $stats['indexed'] > 0 || $stats['failed'] > 0 || $stats['expired'] > 0 )
{
    OpenPAReindexRelations::log(
        "Cron: indicizzati {$stats['indexed']}, saltati {$stats['skipped']}, falliti {$stats['failed']}, " .
        "scaduti {$stats['expired']}, in coda {$stats['remaining']}" . ( $stats['stopped'] ? ' (interrotto: Solr non risponde?)' : '' )
    );
}

if ( !$isQuiet )
{
    $cli->output(
        "Done: indexed {$stats['indexed']}, skipped {$stats['skipped']}, failed {$stats['failed']}, " .
        "expired {$stats['expired']}, remaining {$stats['remaining']}"
    );
}
