<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo impostazioni SEO\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[id:]',
                                '',
                                array( 'id'  => 'ID di monitoraggio')
);
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );
try
{
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], '_backend' ) !== false )
    {
        throw new Exception( 'Questo script va eseguito in frontend' );        
    }
    
    if ( $options['id'] )
    {        
        $id = trim( $options['id'] );
        $save = OpenPAINI::set( "Seo", "GoogleAnalyticsAccountID", $id );
        if ( $save )
        {        
            OpenPALog::warning( 'Salvato id in ' . $save );
        }
    }
        
    $siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
    $seoCode = eZINI::instance( 'openpa.ini' )->variable( 'Seo', 'GoogleAnalyticsAccountID' );
    
    OpenPALog::notice( $siteName . ' ' . $seoCode );
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}