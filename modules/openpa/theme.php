<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$themeList = array();
$frontendSiteIni = eZSiteAccess::getIni(OpenPABase::getFrontendSiteaccessName());
if (in_array('designitalia', $frontendSiteIni->variable('DesignSettings', 'AdditionalSiteDesignList'))
    || 'designitalia' == $frontendSiteIni->variable('DesignSettings', 'SiteDesign')) {
    $themeList = eZDir::findSubdirs('extension/openpa_designitalia/themes', false, '/base$/');
}
sort($themeList);

if ($http->hasPostVariable('StoreTheme') && $http->hasPostVariable('Theme')) {
    $theme = trim($http->postVariable('Theme'));
    if (!in_array($theme, $themeList)) {
        $tpl->setVariable('message', 'Errore: tema non supportato');
    } else {
        $save = OpenPAINI::set("GeneralSettings", "theme", $theme);
        if ($save) {
            $tpl->setVariable('message', 'Impostazioni salvate correttamente');

            eZCache::clearByTag('template');

            $optionArray = array('iniFile' => 'site.ini',
                'iniSection' => 'ContentSettings',
                'iniVariable' => 'StaticCacheHandler');

            $options = new ezpExtensionOptions($optionArray);
            $staticCacheHandler = eZExtension::getHandlerClass($options);

            $staticCacheHandler->generateCache(true, true);

        } else {
            $tpl->setVariable('message', 'Errore!');
        }
    }
}

$tpl->setVariable('theme', OpenPAINI::variable("GeneralSettings", "theme", false));
$tpl->setVariable('theme_list', $themeList);
if (count($themeList) == 0) {
    $tpl->setVariable('message', 'Impostazione non configurabile per il tema grafico corrente');
}

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/theme.tpl');
$Result['path'] = array(array('text' => 'Impostazioni Tema', 'url' => false));
