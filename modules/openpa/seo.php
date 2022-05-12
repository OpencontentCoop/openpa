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

    if ($http->hasPostVariable('GoogleCookieless')) {
        OpenPAINI::set("Seo", "GoogleCookieless", 'enabled');
    }else{
        OpenPAINI::set("Seo", "GoogleCookieless", 'disabled');
    }

    if ($http->hasPostVariable('GoogleTagManagerID')) {
        $googleTagManagerID = trim($http->postVariable('GoogleTagManagerID'));
        OpenPAINI::set("Seo", "GoogleTagManagerID", $googleTagManagerID);
    }

    if ($http->hasPostVariable('GoogleSiteVerificationID')) {
        $googleSiteVerificationID = trim($http->postVariable('GoogleSiteVerificationID'));
        OpenPAINI::set("Seo", "GoogleSiteVerificationID", $googleSiteVerificationID);
    }

    if ($http->hasPostVariable('MetaAuthor')) {
        $metaAuthor = trim($http->postVariable('MetaAuthor'));
        OpenPAINI::set("Seo", "metaAuthor", $metaAuthor);
    }

    if ($http->hasPostVariable('MetaCopyright')) {
        $metaCopyright = trim($http->postVariable('MetaCopyright'));
        OpenPAINI::set("Seo", "metaCopyright", $metaCopyright);
    }

    if ($http->hasPostVariable('MetaDescription')) {
        $metaDescription = trim($http->postVariable('MetaDescription'));
        OpenPAINI::set("Seo", "metaDescription", $metaDescription);
    }

    if ($http->hasPostVariable('MetaKeywords')) {
        $metaKeywords = trim($http->postVariable('MetaKeywords'));
        OpenPAINI::set("Seo", "metaKeywords", $metaKeywords);
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

    if ($http->hasPostVariable('WebAnalyticsItaliaID')) {
        $googleID = trim($http->postVariable('WebAnalyticsItaliaID'));
        OpenPAINI::set("Seo", "webAnalyticsItaliaID", $googleID);
    }

    if ($http->hasPostVariable('WebAnalyticsItaliaCookieless')) {
        OpenPAINI::set("Seo", "WebAnalyticsItaliaCookieless", 'enabled');
    }else{
        OpenPAINI::set("Seo", "WebAnalyticsItaliaCookieless", 'disabled');
    }

    if (OpenPAINI::variable('CookiesSettings', 'Consent', 'simple') == 'advanced') {
        if ($http->hasPostVariable('CookieConsentMultimediaText')) {
            $cookieConsentMultimediaText = trim($http->postVariable('CookieConsentMultimediaText'));
            OpenPAINI::set("Seo", "CookieConsentMultimediaText", $cookieConsentMultimediaText);
        }

        if ($http->hasPostVariable('CookieConsentMultimedia')) {
            OpenPAINI::set("Seo", "CookieConsentMultimedia", 'enabled');
        } else {
            OpenPAINI::set("Seo", "CookieConsentMultimedia", 'disabled');
        }
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
$tpl->setVariable('metaAuthor', OpenPAINI::variable('Seo', 'metaAuthor'));
$tpl->setVariable('metaCopyright', OpenPAINI::variable('Seo', 'metaCopyright'));
$tpl->setVariable('metaDescription', OpenPAINI::variable('Seo', 'metaDescription'));
$tpl->setVariable('metaKeywords', OpenPAINI::variable('Seo', 'metaKeywords'));
$tpl->setVariable('isRobotsTextDefault', $robotsTextDefault);
$tpl->setVariable('googleTagManagerID', OpenPAINI::variable('Seo', 'GoogleTagManagerID', false));
$tpl->setVariable('googleSiteVerificationID', OpenPAINI::variable('Seo', 'GoogleSiteVerificationID', false));
$tpl->setVariable('webAnalyticsItaliaId', OpenPAINI::variable('Seo', 'webAnalyticsItaliaID', false));
$tpl->setVariable('googleCookieless', OpenPAINI::variable('Seo', 'GoogleCookieless', 'disabled'));
$tpl->setVariable('webAnalyticsItaliaCookieless', OpenPAINI::variable('Seo', 'WebAnalyticsItaliaCookieless', 'disabled'));
if (OpenPAINI::variable('CookiesSettings', 'Consent', 'simple') == 'advanced') {
    $tpl->setVariable('cookieConsentMultimediaText',
        OpenPAINI::variable('Seo', 'CookieConsentMultimediaText', 'YouTube, Vimeo, Slideshare, Isuu, Facebook, Twitter, Linkedin, Instagram, Whatsapp'));
    $tpl->setVariable('cookieConsentMultimedia',
        OpenPAINI::variable('Seo', 'CookieConsentMultimedia', 'enabled'));
}

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/seo.tpl');
$Result['path'] = array(array('text' => 'Impostazioni SEO', 'url' => false));
$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = array(
    'show_path' => false
);
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $tpl->variable('persistent_variable'));
}
$Result['content_info'] = $contentInfoArray;