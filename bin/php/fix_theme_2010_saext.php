<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Impostazione sa extension per 2010 theme\n\n" ),
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
    $siteaccess = eZSiteAccess::current();

    $value = array(
        'ezwebin',
        'openpa_frontendsettings',
        'openpa_theme_2010'
    );

    $frontend = OpenPABase::getFrontendSiteaccessName();
    $path = "settings/siteaccess/{$frontend}/";
    $iniFile = "site.ini";
    $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );
    $ini->setVariable( "ExtensionSettings", "ActiveAccessExtensions", $value );

    if ( $ini->save() )
    {
        OpenPALog::warning( 'Salvate impostazioni in ' . $path . $iniFile );
    }

    $debug = OpenPABase::getDebugSiteaccessName();
    $path = "settings/siteaccess/{$debug}/";
    $iniFile = "site.ini";
    $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );
    $ini->setVariable( "ExtensionSettings", "ActiveAccessExtensions", $value );

    if ( $ini->save() )
    {
        OpenPALog::warning( 'Salvate impostazioni in ' . $path . $iniFile );
    }

    OpenPALog::warning( "Svuoto cache degli ini" );
    eZCache::clearById( 'global_ini' );

    OpenPALog::warning( "Svuoto cache degli template" );
    eZCache::clearById( 'template' );

    OpenPALog::warning( "Rigenero i menu" );
    OpenPAMenuTool::generateAllMenus();
    eZCache::clearById( 'template' );

    OpenPALog::warning( "Svuoto cache dei contenuti" );
    eZCache::clearById( 'content' );

    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}