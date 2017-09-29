<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

if ($http->hasPostVariable('GoogleID')) {
    $GoogleID = trim($http->postVariable('GoogleID'));
    $save = OpenPAINI::set("Seo", "GoogleAnalyticsAccountID", $GoogleID);
    if ($save) {
        $tpl->setVariable('message', 'Impostazioni salvate correttamente');
        eZCache::clearByTag('template');
    } else {
        $tpl->setVariable('message', 'Errore!');
    }
}

if ($http->hasPostVariable('Robots')) {
    OpenPAINI::set("Seo", "EnableRobots", 'enabled');
} elseif ($http->hasPostVariable('StoreSeo')) {
    OpenPAINI::set("Seo", "EnableRobots", 'disabled');
}

if ($http->hasPostVariable('StoreSeo')) {
    eZCache::clearByTag('template');
}

$tpl->setVariable('googleId', OpenPAINI::variable('Seo', 'GoogleAnalyticsAccountID', false));
$tpl->setVariable('robots', OpenPAINI::variable('Seo', 'EnableRobots', 'disabled'));

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/seo.tpl');
$Result['path'] = array(array('text' => 'Impostazioni SEO', 'url' => false));
