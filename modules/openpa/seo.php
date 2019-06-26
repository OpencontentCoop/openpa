<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();


if ($http->hasPostVariable('StoreSeo')) {

    if ($http->hasPostVariable('GoogleID')) {
        $googleID = trim($http->postVariable('GoogleID'));
        OpenPAINI::set("Seo", "GoogleAnalyticsAccountID", $googleID);
    }

    if ($http->hasPostVariable('GoogleTagManagerID')) {
        $googleTagManagerID = trim($http->postVariable('GoogleTagManagerID'));
        OpenPAINI::set("Seo", "GoogleTagManagerID", $googleTagManagerID);
    }

    if ($http->hasPostVariable('GoogleSiteVerificationID')) {
        $googleSiteVerificationID = trim($http->postVariable('GoogleSiteVerificationID'));
        OpenPAINI::set("Seo", "GoogleSiteVerificationID", $googleSiteVerificationID);
    }

    if ($http->hasPostVariable('Robots')) {
        OpenPAINI::set("Seo", "EnableRobots", 'enabled');
    } else {
        OpenPAINI::set("Seo", "EnableRobots", 'disabled');
    }

    if ($http->hasPostVariable('RobotsText')) {
        $robotsText = trim($http->postVariable('RobotsText'));
        OpenPAINI::set("Seo", "RobotsText", $robotsText);
    }

    eZCache::clearByTag('template');

    eZExtension::getHandlerClass(new ezpExtensionOptions(array('iniFile' => 'site.ini',
        'iniSection' => 'ContentSettings',
        'iniVariable' => 'StaticCacheHandler')))->generateCache(true, true);

    $module->redirectTo('/openpa/seo');
    return;
}

$robotsTextDefault = false;
$robotsText = OpenPAINI::variable('Seo', 'RobotsText', '');
if (empty($robotsText)) {
    $robotsText = OpenPAINI::variable('Seo', 'DefaultRobotsText', false);
    $robotsTextDefault = true;
}

$tpl->setVariable('googleId', OpenPAINI::variable('Seo', 'GoogleAnalyticsAccountID', false));
$tpl->setVariable('robots', OpenPAINI::variable('Seo', 'EnableRobots', 'disabled'));
$tpl->setVariable('robotsText', $robotsText);
$tpl->setVariable('isRobotsTextDefault', $robotsTextDefault);
$tpl->setVariable('googleTagManagerID', OpenPAINI::variable('Seo', 'GoogleTagManagerID', false));
$tpl->setVariable('googleSiteVerificationID', OpenPAINI::variable('Seo', 'GoogleSiteVerificationID', false));

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/seo.tpl');
$Result['path'] = array(array('text' => 'Impostazioni SEO', 'url' => false));
