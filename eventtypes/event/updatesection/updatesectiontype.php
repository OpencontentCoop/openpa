<?php
 
class UpdateSectionType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "updatesection";
    
    const LOGFILE = 'openpa_worflow_updatesection.log';
    
    public function __construct()
    {
        parent::__construct( UpdateSectionType::WORKFLOW_TYPE_STRING, 'Aggiorna sezione per l\' utente non "A Disposizione" (servizio 50)' );
    }
 
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $objectID = $parameters['object_id'];
        $object = eZContentObject::fetch( $objectID );
        
        $ini = OpenPABase::getIni( 'openpa_workflow.ini' );
        
        if ( !$ini->hasGroup( 'WorkflowSettings' ) )
        {
            $inifilename = OpenPABase::getIniFileName( 'openpa_workflow.ini' );
            eZLog::write( "Non è stata trovata l'impostazione WorkflowSettings in $inifilename", self::LOGFILE );
            return eZWorkflowType::STATUS_NONE;    
        }
        
        if ( !$ini->hasVariable( 'WorkflowSettings', 'updatesection' ) )
        {
            $inifilename = OpenPABase::getIniFileName( 'openpa_workflow.ini' );
            eZLog::write( "Non è stata trovata l'impostazione updatesection nel gruppo WorkflowSettings in $inifilename. (Vedi esempio nel log successivo)", self::LOGFILE );
            $sample = array(
                'sezione_aperta_a_tutti' => 1,
                'sezione_intranet' => 2,
                'servizio_a_disposizone' => 3
            );
            eZLog::write( var_export( $sample, 1 ), self::LOGFILE );
            return eZWorkflowType::STATUS_NONE;   
        }
        
        $updatesectionSettings = $ini->variable( 'WorkflowSettings', 'updatesection' );
        
        $sezioneApertoATutti = $updatesectionSettings['sezione_aperta_a_tutti'];
        $sezioneIntranet = $updatesectionSettings['sezione_intranet'];
        $servizioADisposizione = $updatesectionSettings['servizio_a_disposizone'];
        
        if ( $object->attribute( 'class_identifier' ) == 'user' )
        {
            $dataMap = $object->dataMap();
            $servizio = $dataMap['servizio']->toString();
            $servizio = explode( '-', $servizio );
            $user = $dataMap['nominativo']->toString() . ' (' . $object->attribute('id') . ')';
            
            //eZLog::write( "L'utente $user è sottoposto al workflow UpdateSectionType: l'utente appartiene alla sezione ". $object->attribute( 'section_id' ). " e ai servizi " . implode(',', $servizio), self::LOGFILE );
            
            if ( $object->attribute( 'section_id' ) == $sezioneIntranet  && !in_array( $servizioADisposizione, $servizio ) )
            {                
                eZOperationHandler::execute('content',
                                    'updatesection',
                                    array(
                                        'node_id' => $object->attribute( 'main_node_id' ),
                                        'selected_section_id' => $sezioneApertoATutti,
                                        )
                                    );
                eZContentCacheManager::clearContentCache( $object->attribute('id') );
                eZLog::write( "Aggiornata sezione per utente $user", self::LOGFILE );    
            }
            elseif ( $object->attribute( 'section_id' ) != $sezioneIntranet  && in_array( $servizioADisposizione, $servizio ) )
            {
                eZOperationHandler::execute('content',
                                    'updatesection',
                                    array(
                                        'node_id' => $object->attribute( 'main_node_id' ),
                                        'selected_section_id' => $sezioneIntranet,
                                        )
                                    );
                eZContentCacheManager::clearContentCache( $object->attribute('id') );
                eZLog::write( "Aggiornata sezione per utente $user: sezione $sezioneIntranet, servizio $servizioADisposizione", self::LOGFILE );
            }
        }
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( UpdateSectionType::WORKFLOW_TYPE_STRING, 'updatesectiontype' );
?>