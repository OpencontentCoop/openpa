<?php

/*
    workflow di post pubblicazione "Aggiunge collocazione a evento sui calendari relazionati al tipo di evento" seconso cui:
    quando viene pubblicato un evento, all'evento in pubblicazione vengono aggiunte le collocazioni in base alla tipologia di evento spuntata.
    Le collocazioni sono definitie per ciascuna tipologia nell'attributo Calendari di riferimento. 
    Vedi ad esempio: http://www.comune.rovereto.tn.it/backend/Classificazioni/Classificazioni-del-territorio-e-turismo/Tipi-di-eventi/Cultura
*/
 
class AddCalendarByEventType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "addcalendarbyevent";
    
    public function __construct()
    {
        parent::__construct( AddCalendarByEventType::WORKFLOW_TYPE_STRING, 'Aggiunge collocazione a evento sui calendari relazionati al tipo di evento' );
    }
 
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $objectID = $parameters['object_id'];
        $object = eZContentObject::fetch( $objectID );
        if ( $object instanceof eZContentObject )
        {            
            $nodeID = $object->attribute( 'main_node_id' );
            $ini = OpenPABase::getIni( 'openpa_workflow.ini' );
            
            if ( $ini->hasGroup( 'CalendarWorkflowSettings' ) )
            {
                $eventClasses = (array) $ini->variable( 'CalendarWorkflowSettings', 'EventClasses' );
                $eventTypeIdentifier = $ini->variable( 'CalendarWorkflowSettings', 'EventTypeIdentifier' );
                $typeCalendarsIdentifier = $ini->variable( 'CalendarWorkflowSettings', 'TypeCalendarsIdentifier' );
                $addLocations = array();
                if ( in_array( $object->attribute( 'class_identifier' ), $eventClasses ) )
                {
                    $dataMap = $object->attribute( 'data_map' );
                    if ( isset( $dataMap[$eventTypeIdentifier] )
                         && $dataMap[$eventTypeIdentifier] instanceOf eZContentObjectAttribute
                         && $dataMap[$eventTypeIdentifier]->attribute( 'has_content' ) )
                    {
                        $attributeContent = $dataMap[$eventTypeIdentifier]->toString();
                        $relationIds = explode( '-', $attributeContent );
                        {
                            foreach( $relationIds as $relationId )
                            {
                                $relation = eZContentObject::fetch( $relationId );
                                if ( $relation instanceof eZContentObject )
                                {
                                    $relationDataMap = $relation->attribute( 'data_map' );
                                    if ( isset( $relationDataMap[$typeCalendarsIdentifier] )
                                         && $relationDataMap[$typeCalendarsIdentifier] instanceOf eZContentObjectAttribute
                                         && $relationDataMap[$typeCalendarsIdentifier]->attribute( 'has_content' ) )
                                    {
                                        $relationAttributeContent = $relationDataMap[$typeCalendarsIdentifier]->attribute( 'content' );
                                        foreach( $relationAttributeContent['relation_list'] as $item )
                                        {
                                            if ( isset( $item['node_id'] ) )
                                            {
                                                $addLocations[] = $item['node_id'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ( !empty( $addLocations ) )
                {
                    $operationResult = eZOperationHandler::execute( 'content',
                                                                    'addlocation', array( 'node_id'              => $nodeID,
                                                                                          'object_id'            => $objectID,
                                                                                          'select_node_id_array' => $addLocations ),
                                                                    null,
                                                                    true );
                }
            }
        }
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( AddCalendarByEventType::WORKFLOW_TYPE_STRING, 'addcalendarbyeventtype' );
?>