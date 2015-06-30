#!/usr/bin/env php
<?php
set_time_limit( 0 );
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
         'description' => ( "OpenPa Instances" ),
         'use-session' => false,
         'use-modules' => true,
         'use-debug' => true,
         'use-extensions' => true
    )
);


$options = $script->getOptions(
    '[check][wiki][regenerate][fixvh]',
    '',
    array(
         'check' => 'Confronta i valori live con il file instances.yml',
         'regenerate' => 'Rigenera il file instances.yml',
         'wiki' => 'Stampa a video i valori in formato wiki table',
         'fixvh' => 'Stampa a video i valori per cui è necessario correggere il virtual host'
    )
);
$script->initialize();
$script->startup();

$errors = array();

$sensorSiteaccess = OpenPABase::getInstances( 'sensor' );
$dimmiSiteaccess = OpenPABase::getInstances( 'dimmi' );
$siteaccess = OpenPABase::getInstances();

foreach( $sensorSiteaccess as $ssa )
{
    $frontendName = str_replace( '_sensor', '_frontend', $ssa );
    if ( !in_array( $frontendName, $siteaccess ) )
    {
        $siteaccess[] = $ssa;
    }
}

foreach( $dimmiSiteaccess as $ssa )
{
    $frontendName = str_replace( '_dimmi', '_frontend', $ssa );
    if ( !in_array( $frontendName, $siteaccess ) )
    {
        $siteaccess[] = $ssa;
    }
}

sort( $siteaccess );

/** @var OpenPAInstance[] $data */
$data = array();
foreach ( $siteaccess as $sa )
{
    $openPaInstance = new OpenPAInstance( $sa );
    $key = $openPaInstance->getName();
    if ( isset( $data[$key] ) )
    {
        $key = $key . '_';
    }
    $data[$key] = $openPaInstance;
}
ksort( $data );
function sortInstancesByTypeAndName( $data )
{
    $byType = array();
    $byName = array();
        
    foreach( $data as $key => $value )
    {
        $byType[$key] = $value->getType();
        $byName[$key] = $value->getName();
    }
    array_multisort( $byType, SORT_ASC, $byName, SORT_ASC, $data );
    return $data;
}

// output wiki table
if ( $options['wiki'] )
{
    $output = array();
    $index = 1;
    $headers = false;
    
    $data = sortInstancesByTypeAndName( $data );
    
    foreach ( $data as $name => $instance )
    {
        if ( !$headers )
        {
            $headers = $instance->toWikiTableRow( '', true );
        }

        $output[] = $instance->toWikiTableRow( $index );
        $index++;
    }
    
    eZCLI::instance()->output( $headers );
    foreach ( $output as $item )
    {
        eZCLI::instance()->output( $item );
    }
}

// save instances.yml
if ( $options['regenerate'] )
{
    $sitesYmlData = array();
    $sitesYmlData['server'] = eZSys::serverURL();
    $sitesYmlData['document_root'] = eZSys::rootDir();
    $sitesYmlData['instances'] = array();
    foreach ( $data as $name => $instance )
    {
        if ( $instance->isLive() )
        {
            $sitesYmlData['instances'][$instance->getSiteAccessBaseName()] = $instance->toHash();
        }
    }
    $yaml = Symfony\Component\Yaml\Yaml::dump( $sitesYmlData, 10 );
    $fileHandler = eZClusterFileHandler::instance( 'instances.yml' );
    $fileHandler->fileStoreContents( 'instances.yml', $yaml, 'opencontent_sys' );
}

if ( $options['check'] )
{
    $fileHandler = eZClusterFileHandler::instance( 'instances.yml' );
    $yaml = Symfony\Component\Yaml\Yaml::parse( $fileHandler->fetchContents() );
    foreach( $yaml['instances'] as $instanceName => $yamlValue )
    {
        try
        {
            OpenPAInstance::compare( $instanceName, $yamlValue );
        }
        catch( Exception $e )
        {
            $errors[$instanceName][] = $e->getMessage();
        }
    }
}

if ( $options['fixvh'] )
{
    foreach ( $data as $name => $instance )
    {        
        if ( $instance->isLive() )
        {
            $cli->output( "Controllo $name ", false );
            $stagingUrl = $instance->getUrl( OpenPAInstance::STAGING );
            $prodUrl = $instance->getUrl( OpenPAInstance::PRODUCTION );
            $stagingUrlParts = explode( '//', $stagingUrl );
            $checkDomain = 'http://push.opencontent.it/checkdomain.php?domain=' . $stagingUrlParts[1];
            $tries = 0;
            $maxTries = 5;
            while ( $tries < $maxTries )
            {
                $data = json_decode( eZHTTPTool::getDataByURL( $checkDomain ) );
                if ( $data )
                {
                    $tries = $maxTries;        
                }
                else
                {
                    $cli->output( '. ', false );
                    $tries++;
                    sleep( 1 );
                }
            }
            
            if ( $data )
            {
                if ( $data->result )
                {
                    if ( $data->redirect == false )
                    {
                        $message = "Aggiungere redirezione in virtual host da $stagingUrl as $prodUrl";
                        $errors[$name][] = $message;
                        $cli->error( $message );
                    }
                    else
                    {
                        if ( ( rtrim( $data->redirect, '/' ) == $prodUrl ) )
                            $cli->output( "OK" );
                        else
                        {
                            $message = "Correggere redirezione in virtual host da {$data->redirect} a $prodUrl";
                            $errors[$name][] = $message;
                            $cli->error( $message );
                        }
                    }
                }
                else
                {
                    $cli->warning( "Dominio $stagingUrl non raggiungibile da apache.opencontent.it: {$data->error}" );
                }
            }
            else
            {
                $cli->error( "Server checkdomain non raggiungibile ($checkDomain)" );                
            }
        }
    }
}

$exitCode = false;
$exitText = false;

if ( count( $errors ) > 0 )
{
    $exitCode = 1;
    $exitText = '';
    foreach( $errors as $instanceName => $error )
    {
        $exitText .= "Errori in " . $instanceName . "\n";
        $exitText .= implode( "\n", $error ) . "\n\n";
    }
}

$script->shutdown( $exitCode, $exitText );
?>