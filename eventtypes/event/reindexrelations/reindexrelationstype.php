<?php

/**
 * Workflow post_publish: se cambia il nome di un oggetto (o un attributo elencato in Attributes[]), accoda la reindicizzazione Solr degli
 * oggetti che lo citano (che ne conservano una copia nel proprio documento).
 * Vedi OpenPAReindexRelations per le scelte di contenimento del carico.
 */
class ReindexRelationsType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "reindexrelations";

    const LOGFILE = 'openpa_workflow_reindexrelations.log';

    public function __construct()
    {
        parent::__construct( ReindexRelationsType::WORKFLOW_TYPE_STRING, 'Reindicizzazione degli oggetti correlati' );
    }

    public function execute( $process, $event )
    {
        // Un errore qui non deve mai bloccare la pubblicazione.
        try {
            $parameters = $process->attribute( 'parameter_list' );
            if ( isset( $parameters['object_id'] ) )
            {
                $object = eZContentObject::fetch( (int)$parameters['object_id'] );
                if ( $object instanceof eZContentObject )
                {
                    $this->queueRelatedObjects( $object );
                }
            }
        }
        catch ( Throwable $e )
        {
            OpenPAReindexRelations::log( 'Errore: ' . $e->getMessage() );
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }

    private function queueRelatedObjects( eZContentObject $object )
    {
        $classes = OpenPAReindexRelations::enabledClasses();
        if ( !empty( $classes ) && !in_array( $object->attribute( 'class_identifier' ), $classes ) )
        {
            return;
        }

        $objectID = (int)$object->attribute( 'id' );
        if ( !OpenPAReindexRelations::relevantChange( $objectID, $object->attribute( 'current_version' ) ) )
        {
            return;
        }

        $limit = OpenPAReindexRelations::intSetting(
            'MaxRelatedObjects',
            OpenPAReindexRelations::DEFAULT_MAX_RELATED_OBJECTS
        );
        $truncated = false;
        $ids = OpenPAReindexRelations::reverseRelatedIDs( $objectID, $limit, $truncated );
        $queued = OpenPAReindexRelations::queue( $ids );

        OpenPAReindexRelations::log(
            "Oggetto #$objectID modificato (nome o attributi tracciati): " . count( $ids ) . " correlati, $queued accodati" .
            ( $truncated ? " (TRONCATO a $limit: gli altri non verranno reindicizzati)" : '' )
        );
    }
}
eZWorkflowEventType::registerEventType( ReindexRelationsType::WORKFLOW_TYPE_STRING, 'reindexrelationstype' );
