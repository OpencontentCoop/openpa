<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Installa ezsurvey\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );


try
{    
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        //throw new Exception( 'Script non eseguibile sul prototipo' );
    }
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    }

    $surveyClassIdentifier = 'survey';
    $surveyAttributeClassIdentifier = 'survey_attribute';
    $nodeID = '1130';

    $surveyWizard = eZSurveyWizard::instance();
    if ( $surveyWizard->databaseStatus() === false )
    {
        OpenPALog::notice( 'Aggiorno il database' );
        $surveyWizard->importDatabase();
    }

    OpenPALog::notice( "Installo la classe $surveyClassIdentifier" );
    $surveyClass = new OpenPAClassTools( $surveyClassIdentifier, true );
    //$surveyClass->sync();

    OpenPALog::notice( "Installo la classe $surveyAttributeClassIdentifier" );
    $surveyAttributeClass = new OpenPAClassTools( $surveyAttributeClassIdentifier, true );
    //$surveyAttributeClass->sync();

    $configList = eZSurveyRelatedConfig::fetchList();
    if ( isset( $configList[0] ) && $config[0] instanceof eZSurveyRelatedConfig )
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

    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    //OpenPALog::error( $e->getMessage() );
    $script->shutdown( $errCode, $e->getMessage() );
}
