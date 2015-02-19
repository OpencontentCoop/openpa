<?php

class OpenPASurveyInstaller implements OpenPAInstaller
{
    protected $options = array();

    public function setScriptOptions( eZScript $script )
    {
        return $script->getOptions();
    }

    public function beforeInstall( $options = array() )
    {
        $this->options = $options;
    }

    public function install()
    {
        $surveyClassIdentifier = 'survey';
        $surveyAttributeClassIdentifier = 'survey_attribute';

        $remoteId = OpenPABase::getFrontendSiteaccessName() . '_survey_attributes';
        $contentObject = eZContentObject::fetchByRemoteID( $remoteId );
        if ( !$contentObject instanceof eZContentObject )
        {
            $params                     = array();
            $params['class_identifier'] = 'folder';
            $params['remote_id']        = $remoteId;
            $params['parent_node_id']   = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' );
            $params['attributes']       = array( 'name' => 'Testi questionari' );
            $contentObject = eZContentFunctions::createAndPublishObject( $params );
        }
        if ( $contentObject )
        {
            $nodeID = $contentObject->attribute( 'main_node_id' );
        }
        else
        {
            throw new Exception( "Fallita creazione nodo parent survey attributes" );
        }

        $surveyWizard = eZSurveyWizard::instance();
        if ( $surveyWizard->databaseStatus() === false )
        {
            OpenPALog::notice( 'Aggiorno il database' );
            $surveyWizard->importDatabase();
        }

        OpenPALog::notice( "Installo la classe $surveyClassIdentifier" );
        $surveyClass = new OpenPAClassTools( $surveyClassIdentifier, true );
        $surveyClass->sync();

        OpenPALog::notice( "Installo la classe $surveyAttributeClassIdentifier" );
        $surveyAttributeClass = new OpenPAClassTools( $surveyAttributeClassIdentifier, true );
        $surveyAttributeClass->sync();

        $configList = eZSurveyRelatedConfig::fetchList();
        if ( isset( $configList[0] ) && $configList[0] instanceof eZSurveyRelatedConfig )
        {
            $config = $configList[0];
        }
        else
        {
            $config = eZSurveyRelatedConfig::create();
        }

        OpenPALog::notice( "Configuro la classe attributi del questionario" );
        $config->setAttribute( 'contentclass_id', $surveyAttributeClass->getLocale()->attribute( 'id' ) );

        OpenPALog::notice( "Configuro il parent node degli attributi del questionario" );
        $config->setAttribute( 'node_id', $nodeID );

        $config->store();
    }

    public function afterInstall()
    {
        return false;
    }
}