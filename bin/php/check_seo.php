<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo impostazioni SEO\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::ALL );

$siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
$seoCode = eZINI::instance( 'openpa.ini' )->variable( 'Seo', 'GoogleAnalyticsAccountID' );

OpenPALog::notice( $siteName . ' ' . $seoCode );

$script->shutdown();
