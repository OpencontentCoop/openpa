<?php
 
class UpdateSectionType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "updatesection";
    
    const LOGFILE = 'openpa_worflow_updatesection.log';
    
    public function __construct()
    {
        parent::__construct( UpdateSectionType::WORKFLOW_TYPE_STRING, 'Aggiorna sezione per l\' utente non "A Disposizione" (servizio 50)' );
    }
 
    protected static function log( $message )
    {
        eZLog::write( $message, self::LOGFILE );
        eZCLI::instance()->output( $message );
    }
 
    public static function updateUserSection( eZContentObject $object )
    {
        if ( !$object instanceof eZContentObject )
        {
            self::log( "Oggetto non valido" );
            return false;    
        }
        $ini = OpenPABase::getIni( 'openpa_workflow.ini' );        
        if ( !$ini->hasGroup( 'WorkflowSettings' ) )
        {
            $inifilename = OpenPABase::getIniFileName( 'openpa_workflow.ini' );
            self::log( "Non è stata trovata l'impostazione WorkflowSettings in $inifilename" );
            return false;    
        }
        
        if ( !$ini->hasVariable( 'WorkflowSettings', 'updatesection' ) )
        {
            $inifilename = OpenPABase::getIniFileName( 'openpa_workflow.ini' );
            self::log( "Non è stata trovata l'impostazione updatesection nel gruppo WorkflowSettings in $inifilename" );            return false;            
        }
        
        $updatesectionSettings = $ini->variable( 'WorkflowSettings', 'updatesection' );        
        $sezioneApertoATutti = $updatesectionSettings['sezione_aperta_a_tutti'];
        $sezioneIntranet = $updatesectionSettings['sezione_intranet'];
        $servizioADisposizione = $updatesectionSettings['servizio_a_disposizone'];
        
        if ( $object->attribute( 'class_identifier' ) == 'user' )
        {
            $dataMap = $object->dataMap();
            if ( isset( $dataMap['servizio'] ) )
            {
                $servizio = $dataMap['servizio']->toString();
                $servizio = explode( '-', $servizio );
                $user = $dataMap['nominativo']->toString() . ' (' . $object->attribute('id') . ')';
                
                self::log( "Controllo sezione utente: l'utente appartiene alla sezione ". $object->attribute( 'section_id' ) . " e ai servizi " . implode(',', $servizio) );
                
                if ( $object->attribute( 'section_id' ) == $sezioneIntranet  && !in_array( $servizioADisposizione, $servizio ) )
                {                
                    eZOperationHandler::execute(
                                        'content',
                                        'updatesection',
                                        array(
                                            'node_id' => $object->attribute( 'main_node_id' ),
                                            'selected_section_id' => $sezioneApertoATutti,
                                            )
                                        );
                    eZContentCacheManager::clearContentCache( $object->attribute('id') );
                    self::log( "Aggiornata sezione per utente $user: sezione $sezioneApertoATutti" );    
                }
                elseif ( $object->attribute( 'section_id' ) != $sezioneIntranet  && in_array( $servizioADisposizione, $servizio ) )
                {
                    eZOperationHandler::execute(
                                        'content',
                                        'updatesection',
                                        array(
                                            'node_id' => $object->attribute( 'main_node_id' ),
                                            'selected_section_id' => $sezioneIntranet,
                                            )
                                        );
                    eZContentCacheManager::clearContentCache( $object->attribute('id') );
                    self::log( "Aggiornata sezione per utente $user: sezione $sezioneIntranet, servizio $servizioADisposizione" );
                }
                
                return true;
            }
        }
    }
 
    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $objectID = $parameters['object_id'];
        $object = eZContentObject::fetch( $objectID );
        
        self::updateUserSection( $object );
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType( UpdateSectionType::WORKFLOW_TYPE_STRING, 'updatesectiontype' );
