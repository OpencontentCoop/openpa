<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo consistenza classe\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[appid:]',
                                '',
                                array( 'appid'  => 'Application ID in comunita.tn.it')
);
$script->initialize();
$script->setUseDebugAccumulators( true );

$cli = eZCLI::instance();

OpenPALog::setOutputLevel( OpenPALog::ALL );

try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
    if ( stripos( $siteaccess['name'], 'consorzio' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sui siti del consorzio' );        
    } 
    
    $classes = array(
        "categoria_sensor",
        "sensor_container",
        //"rassegna_sensor",
        "sensor",
        "stato_sensor",
        "tipo_sensor"
    );
    
    foreach( $classes as $identifier )
    {
        //OpenPALog::warning( 'Controllo class ' . $identifier );
        $tools = new OpenPAClassTools( $identifier, true );
        if ( !$tools->isValid() )
        {            
            $tools->sync( true );
            OpenPALog::warning( "La classe $identifier è stata aggiornata" );
        }        
    }
    
    if ( !$options['appid'] )
    {
        $siteINI = eZINI::instance();
        $description = $name = $siteINI->variable( 'SiteSettings', 'SiteName' ) . ' - SensorCivico';    
        $siteUrl = rtrim( $siteINI->variable( 'SiteSettings', 'SiteURL' ), '/' );
        $command = "cd /home/httpd/entilocali.opencontent.it/html/; php extension/ocentilocali/bin/php/generate_oauth_app.php --nome=\"{$name}\" --descrizione=\"{$description}\" --endpoint=http://{$siteUrl}/sensorcivico/signin  -sdefault_backend; cd /home/httpd-bis/openpa.opencontent.it/html";
        $cli->output();
        $cli->warning( "Esegui il seguente il comando per generare l'app id, poi riesegui il comando iniziale passando anche il valore --appid=<valore_restituito>" );
        $cli->output();
        $cli->output( $command );
        $cli->output();
        $script->shutdown();
    }
    else
    {
        $appId = trim( $options['appid'] );
        $frontend = OpenPABase::getFrontendSiteaccessName();
        $path = "settings/siteaccess/{$frontend}/";
        $iniFile = "sensor.ini";
        $block = "SigninSettings";
        $settingName = "AppClientId";
        $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );                
        $ini->setVariable( $block, $settingName, $appId );
        $writeOk = $ini->save();
        if ( $writeOk )
        {
            eZCache::clearByTag( 'ini' );
            OpenPALog::warning( 'Salvato appid in ' . $path . $iniFile );
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
