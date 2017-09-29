<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances( 'frontend' );
foreach( $siteaccess as $sa )
{
    print "Migate: $sa \n";
    $command = "ezini set -q -s{$sa} --prepend ExtensionSettings ActiveAccessExtensions [ezflow,ezgmaplocation,ezjscore,ezmultiupload,ezodf,ezoe,ezwt,ocmaintenance,weather,ocimportalbo,occsvimport,openpa_importers,sqliimport,ocinigui,openpa,ezflowplayer,openpa_designs,ezfind,ocsearchtools,ezflip,occhangeobjectdate,ocmediaplayer,jcremoteid,ggwebservices,batchtool,ocmap,ocmaps,ezprestapiprovider,ocopendata,ocexportas,ocuserprofile,occosmos,ocselfimport,ezchangeclass,ezclasslists,collectexport,eztags,ocextensionsorder,bcgooglesitemaps,ocembed,nxc_captcha,bfsurveyfile,mugosurvey_addons,ocsurvey_userlogin,ezsurvey,ezstarrating,enhancedezbinaryfile,ocrss,ocwhatsapp,ocrecaptcha,occhangeloginname,ocoperatorscollection,wrap_operator,openpa_sensor]";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend SiteAccessSettings RelatedSiteAccessList ''";
    system( $command );
}

$siteaccess = OpenPABase::getInstances( 'debug' );
foreach( $siteaccess as $sa )
{
    print "Migate: $sa \n";
    $command = "ezini set -q -s{$sa} --prepend ExtensionSettings ActiveAccessExtensions [ezflow,ezgmaplocation,ezjscore,ezmultiupload,ezodf,ezoe,ezwt,ocmaintenance,weather,ocimportalbo,occsvimport,openpa_importers,sqliimport,ocinigui,openpa,ezflowplayer,openpa_designs,ezfind,ocsearchtools,ezflip,occhangeobjectdate,ocmediaplayer,jcremoteid,ggwebservices,batchtool,ocmap,ocmaps,ezprestapiprovider,ocopendata,ocexportas,ocuserprofile,occosmos,ocselfimport,ezchangeclass,ezclasslists,collectexport,eztags,ocextensionsorder,bcgooglesitemaps,ocembed,nxc_captcha,bfsurveyfile,mugosurvey_addons,ocsurvey_userlogin,ezsurvey,ezstarrating,enhancedezbinaryfile,ocrss,ocwhatsapp,ocrecaptcha,occhangeloginname,ocoperatorscollection,wrap_operator,openpa_sensor]";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend SiteAccessSettings RelatedSiteAccessList ''";
    system( $command );

    $saFrontend = str_replace( '_debug', '_frontend', $sa );
    $command = "ezini symlink {$saFrontend} {$sa}";
    system( $command );
}


$siteaccess = OpenPABase::getInstances( 'sensor' );
foreach( $siteaccess as $sa )
{
    print "Migate: $sa \n";
    $command = "ezini set -q -s{$sa} --prepend ExtensionSettings ActiveAccessExtensions [ezflow,ezgmaplocation,ezjscore,ezmultiupload,ezodf,ezoe,ezwt,ocmaintenance,weather,ocimportalbo,occsvimport,openpa_importers,sqliimport,ocinigui,openpa,ezflowplayer,openpa_designs,ezfind,ocsearchtools,ezflip,occhangeobjectdate,ocmediaplayer,jcremoteid,ggwebservices,batchtool,ocmap,ocmaps,ezprestapiprovider,ocopendata,ocexportas,ocuserprofile,occosmos,ocselfimport,ezchangeclass,ezclasslists,collectexport,eztags,ocextensionsorder,bcgooglesitemaps,ocembed,nxc_captcha,bfsurveyfile,mugosurvey_addons,ocsurvey_userlogin,ezsurvey,ezstarrating,enhancedezbinaryfile,ocrss,ocwhatsapp,ocrecaptcha,occhangeloginname,ocoperatorscollection,wrap_operator,openpa_sensor]";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend SiteAccessSettings RelatedSiteAccessList ''";
    system( $command );
}

$siteaccess = OpenPABase::getInstances( 'dimmi' );
foreach( $siteaccess as $sa )
{
    print "Migate: $sa \n";
    $command = "ezini set -q -s{$sa} --prepend ExtensionSettings ActiveAccessExtensions [ezflow,ezgmaplocation,ezjscore,ezmultiupload,ezodf,ezoe,ezwt,ocmaintenance,weather,ocimportalbo,occsvimport,openpa_importers,sqliimport,ocinigui,openpa,ezflowplayer,openpa_designs,ezfind,ocsearchtools,ezflip,occhangeobjectdate,ocmediaplayer,jcremoteid,ggwebservices,batchtool,ocmap,ocmaps,ezprestapiprovider,ocopendata,ocexportas,ocuserprofile,occosmos,ocselfimport,ezchangeclass,ezclasslists,collectexport,eztags,ocextensionsorder,bcgooglesitemaps,ocembed,nxc_captcha,bfsurveyfile,mugosurvey_addons,ocsurvey_userlogin,ezsurvey,ezstarrating,enhancedezbinaryfile,ocrss,ocwhatsapp,ocrecaptcha,occhangeloginname,ocoperatorscollection,wrap_operator,openpa_sensor]";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend SiteAccessSettings RelatedSiteAccessList ''";
    system( $command );
}

$siteaccess = OpenPABase::getInstances( 'backend' );
foreach( $siteaccess as $sa )
{
    print "Migate: $sa \n";
    $command = "ezini set -q -s{$sa} --prepend ExtensionSettings ActiveAccessExtensions [ezflow,ezgmaplocation,ezjscore,ezmultiupload,ezodf,ezoe,ezwt,objectrelationfilter,ocmaintenance,weather,wrap_operator,ocimportalbo,occsvimport,openpa_importers,sqliimport,ocinigui,openpa,ezflowplayer,openpa_designs,ezfind,ocsearchtools,ezflip,occhangeobjectdate,ocmediaplayer,jcremoteid,ggwebservices,batchtool,ocmap,ocmaps,ezprestapiprovider,ocopendata,ocexportas,ocuserprofile,occosmos,ocselfimport,ezchangeclass,ezclasslists,collectexport,opensemantic,eztags,ocextensionsorder,bcgooglesitemaps,ocembed,nxc_captcha,bfsurveyfile,mugosurvey_addons,ocsurvey_userlogin,ezsurvey,ezstarrating,enhancedezbinaryfile,ocrss,ocwhatsapp,ocrecaptcha,openpa_sensor]";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend SiteAccessSettings RelatedSiteAccessList ''";
    system( $command );
    $command ="ezini set -q -s{$sa} --prepend DesignSettings AdditionalSiteDesignList ''";
    system( $command );
}



