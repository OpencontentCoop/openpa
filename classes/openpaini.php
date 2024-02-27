<?php

class OpenPAINI
{

    public static $useDynamicIni;

    private static $seoData;

    private static $themeIdentifier;

    private static $installerVersion;

    public static $dynamicIniMap = array(
        'GestioneAttributi' => array(
            'attributi_contatti' => array(
                'from' => '_full_identifier',
                'to' => 'attribute_group.contacts',
                'value' => 1
            ),
            'zero_is_content' => array(
                'from' => '_full_identifier',
                'to' => 'table_view.show_empty',
                'value' => 1
            ),
            'AttributiNonEditabili' => array(
                'from' => '_full_identifier',
                'to' => 'edit_view.show',
                'value' => 0
            ),
            'oggetti_senza_label' => array(
                'from' => '_identifier',
                'to' => 'table_view.show_label',
                'value' => 0
            ),
            'attributes_with_title' => array(
                'from' => '_identifier',
                'to' => 'line_view.show_label',
                'value' => 1
            ),
            'attributes_to_show' => array(
                'from' => '_identifier',
                'to' => 'line_view.show',
                'value' => 1
            ),
            'attributi_da_includere_user' => array(
                'from' => 'user/_identifier',
                'to' => 'table_view.show',
                'value' => 1
            ),
            'attributes_to_show_politici' => array(
                'from' => 'politico/_identifier',
                'to' => 'table_view.show',
                'value' => 1
            ),
            'attributi_da_escludere_dalla_ricerca' => array(
                'from' => '_identifier',
                'to' => 'search_form.show',
                'value' => 0
            ),
            'attributi_da_escludere' => array(
                'from' => '_identifier',
                'to' => 'table_view.show',
                'value' => 0
            ),
            'attributi_event_da_escludere' => array(
                'from' => 'event/_identifier',
                'to' => 'table_view.show',
                'value' => 0
            ),
            'attributi_da_evidenziare' => array(
                'from' => '_identifier',
                'to' => 'table_view.highlight',
                'value' => 1
            ),
            'attributi_senza_link' => array(
                'from' => '_identifier',
                'to' => 'table_view.show_link',
                'value' => 0
            ),
        )
    );

    protected static $dynamicIniData;

    protected static $filters = array(
        'TopMenu::NodiCustomMenu',
        'GestioneSezioni::sezioni_per_tutti',
        'Attributi::EscludiDaRicerca',
        'Seo::GoogleAnalyticsAccountID',
        'Seo::EnableRobots',
        'Seo::GoogleTagManagerID',
        'Seo::GoogleSiteVerificationID',
        'Seo::RobotsText',
        'Seo::DefaultRobotsText',
        'Seo::metaAuthor',
        'Seo::metaCopyright',
        'Seo::metaDescription',
        'Seo::metaKeywords',
        'Seo::webAnalyticsItaliaID',
        'GeneralSettings::valutation',
        'GeneralSettings::theme',
        'CreditsSettings::CodeVersion',
        'Seo::GoogleCookieless',
        'Seo::WebAnalyticsItaliaCookieless',
        'Seo::CookieConsentMultimedia',
        'Seo::CookieConsentMultimediaText',
    );

    public static function variable( $block, $value, $default = null )
    {
        eZDebug::createAccumulatorGroup('Openpa Ini');
        if ( self::hasFilter( $block, $value, $default ) )
        {
            return self::filter( $block, $value, $default );
        }

        eZDebug::accumulatorStart('openpa_ini_get', 'Openpa Ini', 'Fetch standard openpa.ini');
        $ini = eZINI::instance( 'openpa.ini' );
        $result = $default;
        if ( $ini->hasVariable( $block, $value ) )
        {
            $result = $ini->variable( $block, $value );
        }
        eZDebug::accumulatorStart('openpa_ini_get');
        return $result;
    }

    public static function group( $block )
    {
        $ini = eZINI::instance( 'openpa.ini' );
        $result = null;
        if ( $ini->hasGroup( $block ) )
        {
            $result = $ini->group( $block );
        }
        return $result;
    }

    public static function useDynamicIni()
    {
        if ( self::$useDynamicIni === null )
        {
            self::$useDynamicIni = true;
            $ini = eZINI::instance('openpa.ini');
            if ($ini->hasVariable('GeneralSettings', 'UseDynamicIni'))
            {
                self::$useDynamicIni = $ini->variable('GeneralSettings', 'UseDynamicIni') == 'enabled';

            }
        }
        return self::$useDynamicIni;
    }

    protected static function hasFilter( $block, $value, $default )
    {
        if ( in_array( $block . '::' . $value, self::$filters )
             && self::filter( $block, $value, $default ) !== null )
        {
            return true;
        }

        if ( self::useDynamicIni() && isset( self::$dynamicIniMap[$block][$value] ) )
        {
            return true;
        }

        return false;
    }

    protected static function filterSezioniPerTutti()
    {
        $result = array();
        $ini = eZINI::instance( 'openpa.ini' );
        if ( $ini->hasVariable( 'GestioneSezioni', 'sezioni_per_tutti' ) )
        {
            $result = (array) $ini->variable( 'GestioneSezioni', 'sezioni_per_tutti' );
        }
        $alboSection = eZSection::fetchByIdentifier( 'albotelematicotrentino', false );
        if ( is_array( $alboSection ) )
        {
            $result[] = $alboSection['id'];
        }
        return $result;
    }

    private static function getDynamicIniData()
    {
        if ( self::$dynamicIniData === null ){
            eZDebug::accumulatorStart('dynamic_ini_map', false, 'dynamic_ini_map');
            self::$dynamicIniData = eZClusterFileHandler::instance( self::dynamicIniCachePath() )->processCache(
                function ( $file ){
                    if (file_exists($file)){
                        $result = include( $file );
                    }else{
                        eZDebug::writeNotice("File $file not exists, regenerate", __METHOD__);
                        $result = new eZClusterFileFailure(eZClusterFileFailure::FILE_RETRIEVAL_FAILED);
                    }

                    return $result;
                },
                function (){
                    $result = array();

                    $classes = eZDB::instance()->arrayQuery(
                        'SELECT id, identifier, serialized_name_list ' .
                        'FROM ezcontentclass ' .
                        'WHERE version=0'
                    );
                    $classAttributes = eZContentClassAttribute::fetchList(false);

                    $classAttributesByClassId = array();
                    foreach ($classAttributes as $classAttribute){
                        $classAttributesByClassId[$classAttribute['contentclass_id']][] = $classAttribute;
                    }

                    $keyDefinitionName =  class_exists('OCClassExtraParameters') ? OCClassExtraParameters::getKeyDefinitionName() : 'key';

                    foreach( OpenPAINI::$dynamicIniMap as $block => $values ){

                        $result[$block] = array();

                        foreach( $values as $variable => $settings ){

                            [ $handler, $key ] = explode( '.', $settings['to'] );
                            $matchValue = $settings['value'];

                            $data = OCClassExtraParameters::fetchObjectList(OCClassExtraParameters::definition(),
                                null,
                                array(
                                    'handler' => $handler,
                                    $keyDefinitionName => $key,
                                    'value' => 1
                                )
                            );

                            $results = array();
                            $resultPart = array();
                            foreach( $data as $item ){
                                $resultPart[] = $item->attribute( 'class_identifier' ) . '/' .  $item->attribute( 'attribute_identifier' );
                            }

                            if ( $matchValue == 0 ){
                                foreach( $classes as $class ){
                                    foreach ($classAttributesByClassId[$class['id']] as $classAttribute) {
                                        if (!in_array($class['identifier'] . '/' . $classAttribute['identifier'], $resultPart)) {
                                            $results[] = $class['identifier'] . '/' . $classAttribute['identifier'];
                                        }
                                    }
                                }
                            }else{
                                $results = $resultPart;
                            }

                            $results= array_unique( $results );
                            array_multisort( $results );
                            $result[$block][$variable] = array_values( $results );

                        }
                    }

                    return array( 'content' => $result,
                        'scope'   => OpenPAMenuTool::CACHE_IDENTIFIER );
                }
            );
            eZDebug::accumulatorStop('dynamic_ini_map');
        }
    }

    protected static function filter( $block, $value, $default )
    {
        eZDebug::accumulatorStart('openpa_ini_filter', 'Openpa Ini', 'Fetch dynamic openpa.ini');
        $result = null;
        $filter = $block . '::' . $value;
        switch( $filter )
        {
            case 'TopMenu::NodiCustomMenu':
                $result = OpenPaFunctionCollection::fetchTopMenuNodes();
                break;

            case 'GestioneSezioni::sezioni_per_tutti':
                $result = self::filterSezioniPerTutti();
                break;

            case 'Attributi::EscludiDaRicerca':
                $result = self::variable( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca', $default );
                break;

            case 'Seo::GoogleAnalyticsAccountID':
                $result = self::getSeoData()['googleAnalyticsAccountID'];
                break;

            case 'Seo::EnableRobots':
                $result = self::getSeoData()['enableRobots'];
                break;

            case 'Seo::GoogleTagManagerID':
                $result = self::getSeoData()['googleTagManagerID'];
                break;

            case 'Seo::GoogleSiteVerificationID':
                $result = self::getSeoData()['googleSiteVerificationID'];
                break;

            case 'Seo::RobotsText':
                $result = self::getSeoData()['robotsText'];
                break;

            case 'Seo::DefaultRobotsText':
                $result = file_get_contents('robots.txt');
                break;

            case 'Seo::metaAuthor':
                $result = self::getSeoData()['metaAuthor'];
                break;

            case 'Seo::metaCopyright':
                $result = self::getSeoData()['metaCopyright'];
                break;

            case 'Seo::metaDescription':
                $result = self::getSeoData()['metaDescription'];
                break;

            case 'Seo::metaKeywords':
                $result = self::getSeoData()['metaKeywords'];
                break;

            case 'Seo::webAnalyticsItaliaID':
                $result = self::getSeoData()['webAnalyticsItaliaID'];
                break;

            case 'Seo::WebAnalyticsItaliaCookieless':
                $result = self::getSeoData()['WebAnalyticsItaliaCookieless'];
                break;

            case 'Seo::GoogleCookieless':
                $result = self::getSeoData()['GoogleCookieless'];
                break;

            case 'Seo::CookieConsentMultimedia':
                $result = self::getSeoData()['CookieConsentMultimedia'];
                break;


            case 'Seo::CookieConsentMultimediaText':
                $result = self::getSeoData()['CookieConsentMultimediaText'];
                break;

            case 'GeneralSettings::valutation':
                if (eZINI::instance('openpa.ini')->hasVariable('GeneralSettings', 'valutation')
                    && eZINI::instance('openpa.ini')->variable('GeneralSettings', 'valutation') == 1){
                    $valuationClass = eZContentClass::fetchByIdentifier('valuation');
                    if ($valuationClass instanceof eZContentClass){
                        $result = $valuationClass->objectCount() > 0;
                    }
                }
                break;

            case 'GeneralSettings::theme':
                $result = self::getThemeIdentifier($default);
                break;

            case 'CreditsSettings::CodeVersion':
                $codeVersion = null;
                $versionFile = eZSys::rootDir() . '/VERSION';
                if (file_exists($versionFile)){
                    $codeVersion = file_get_contents($versionFile);
                }elseif (eZINI::instance('openpa.ini')->hasVariable('CreditsSettings', 'CodeVersion')) {
                    $codeVersion = eZINI::instance('openpa.ini')->variable('CreditsSettings', 'CodeVersion');
                }
                $installerVersion = false;
                if (self::$installerVersion === null){
                    self::$installerVersion = eZSiteData::fetchByName('ocinstaller_version');
                }
                if (self::$installerVersion instanceof eZSiteData){
                    $installerVersion = self::$installerVersion->attribute('value');
                    if (strpos($codeVersion, $installerVersion) !== false){
                        $installerVersion = '';
                    }else{
                        $installerVersion = '-' . $installerVersion;
                    }
                }

                $result = trim($codeVersion) . $installerVersion;
                break;
        }

        if ( !$result && isset( self::$dynamicIniMap[$block][$value] ) )
        {
            self::getDynamicIniData();
            $result = isset( self::$dynamicIniData[$block][$value] ) ? self::$dynamicIniData[$block][$value] : $default;
        }

        eZDebug::accumulatorStart('openpa_ini_filter');
        return $result;
    }

    public static function set( $block, $settingName, $value )
    {
        if ( $block && $settingName && $value !== null) {
            $filter = $block . '::' . $settingName;
            switch ($filter) {

                case 'Seo::GoogleAnalyticsAccountID':
                    return self::setSeoData('googleAnalyticsAccountID', $value);
                    break;

                case 'Seo::EnableRobots':
                    return self::setSeoData('enableRobots', $value);
                    break;

                case 'Seo::GoogleTagManagerID':
                    return self::setSeoData('googleTagManagerID', $value);
                    break;

                case 'Seo::GoogleSiteVerificationID':
                    return self::setSeoData('googleSiteVerificationID', $value);
                    break;

                case 'Seo::RobotsText':
                    return trim(self::setSeoData('robotsText', $value));
                    break;

                case 'Seo::DefaultRobotsText':
                    return false;
                    break;

                case 'Seo::metaAuthor':
                    return trim(self::setSeoData('metaAuthor', $value));
                    break;

                case 'Seo::metaCopyright':
                    return trim(self::setSeoData('metaCopyright', $value));
                    break;

                case 'Seo::metaDescription':
                    return trim(self::setSeoData('metaDescription', $value));
                    break;

                case 'Seo::metaKeywords':
                    return trim(self::setSeoData('metaKeywords', $value));
                    break;

                case 'Seo::webAnalyticsItaliaID':
                    return self::setSeoData('webAnalyticsItaliaID', $value);
                    break;

                case 'GeneralSettings::theme':
                    self::setThemeIdentifier($value);

                    return true;
                    break;

                case 'Seo::WebAnalyticsItaliaCookieless':
                    self::setSeoData('WebAnalyticsItaliaCookieless', $value);
                    break;

                case 'Seo::GoogleCookieless':
                    self::setSeoData('GoogleCookieless', $value);
                    break;

                case 'Seo::CookieConsentMultimedia':
                    self::setSeoData('CookieConsentMultimedia', $value);
                    break;

                case 'Seo::CookieConsentMultimediaText':
                    self::setSeoData('CookieConsentMultimediaText', $value);
                    break;

                default:

                    $frontend = OpenPABase::getFrontendSiteaccessName();
                    $path = "settings/siteaccess/{$frontend}/";
                    $iniFile = "openpa.ini";
                    $ini = new eZINI($iniFile . '.append', $path, null, null, null, true, true);
                    $ini->setVariable($block, $settingName, $value);
                    eZCache::clearById(array('global_ini'));
                    if ($ini->save()) {
                        return $path . $iniFile;
                    }

                    return false;
            }
        }
        return false;
    }

    public static function dynamicIniCachePath(){
        return eZSys::cacheDirectory() . '/' . 'openpa/ini/dynamicini.cache';
    }

    public static function clearDynamicIniCache(){
        eZClusterFileHandler::instance( self::dynamicIniCachePath() )->delete();
        eZClusterFileHandler::instance( self::dynamicIniCachePath() )->purge();
        self::$dynamicIniData = null;
        self::getDynamicIniData();
        eZCache::clearContentCache(null);
    }

    public static function clearCache()
    {
        self::clearDynamicIniCache();
    }

    private static function getThemeIdentifier($default)
    {
        if (self::$themeIdentifier === null) {
            self::$themeIdentifier = OpenPAPageData::getThemeIdentifierCache()->processCache(
                function ($file, $mtime) {
                    if (file_exists($file)) {
                        $result = include( $file );
                    }

                    return $result;
                },
                function () {
                    $themeIdentifierSiteData = eZSiteData::fetchByName('Theme');
                    if (!$themeIdentifierSiteData instanceof eZSiteData) {
                        $themeIdentifierSiteData = new eZSiteData(array(
                            'name' => 'Theme',
                            'value' => ''
                        ));
                        $ini = eZINI::instance('openpa.ini');
                        if ($ini->hasVariable('GeneralSettings', 'theme')) {
                            $themeIdentifier = $ini->variable('GeneralSettings', 'theme');
                            $themeIdentifierSiteData->setAttribute('value', $themeIdentifier);
                            $themeIdentifierSiteData->store();
                        }
                    }
                    $result = $themeIdentifierSiteData->attribute('value');

                    return array(
                        'content' => $result,
                        'scope' => 'theme_identifier'
                    );
                }
            );

            if (empty(self::$themeIdentifier) || self::$themeIdentifier instanceof eZClusterFileFailure){
                self::$themeIdentifier = $default;
            }
        }

        return self::$themeIdentifier;
    }

    private static function setThemeIdentifier($value)
    {
        $data = eZSiteData::fetchByName('Theme');
        if (!$data instanceof eZSiteData) {
            $data = new eZSiteData(array(
                'name' => 'Theme',
                'value' => ''
            ));
        }
        $data->setAttribute('value', $value);
        $data->store();
        $cacheFile = OpenPAPageData::getThemeIdentifierCache();
        $cacheFile->delete();
        $cacheFile->purge();
        self::$themeIdentifier = null;
    }

    private static function getSeoData()
    {
        if (self::$seoData === null) {
            self::$seoData = OpenPAPageData::getSeoCache()->processCache(
                function ($file) {
                    if (file_exists($file)) {
                        $result = include($file);
                    }

                    return $result;
                },
                function () {
                    $siteData = eZSiteData::fetchByName('SeoSettings');
                    if (!$siteData instanceof eZSiteData) {
                        $result = self::generateSeoData();
                    } else {
                        $result = json_decode($siteData->attribute('value'), true);
                    }

                    return array(
                        'content' => $result,
                        'scope' => 'cache'
                    );
                }
            );
        }

        if (self::isSeoDisabledByHostname()) {
            self::$seoData['enableRobots'] = 'disabled';
        }

        return array_merge(
            self::getEmptySeoData(),
            (array)self::$seoData
        );
    }

    private static function setSeoData($key, $value)
    {
        $siteData = eZSiteData::fetchByName('SeoSettings');
        if (!$siteData instanceof eZSiteData) {
            self::generateSeoData();
            $siteData = eZSiteData::fetchByName('SeoSettings');
        }

        $data = json_decode($siteData->attribute('value'), true);
        $data[$key] = $value;

        $siteData->setAttribute('value', json_encode($data));
        $siteData->store();

        self::$seoData = null;
        $cacheFile = OpenPAPageData::getSeoCache();
        $cacheFile->delete();
        $cacheFile->purge();

        return true;
    }

    private static function getEmptySeoData()
    {
        return array(
            'googleAnalyticsAccountID' => '',
            'enableRobots' => 'disabled',
            'googleTagManagerID' => '',
            'googleSiteVerificationID' => '',
            'robotsText' => '',
            'metaAuthor' => false,
            'metaCopyright' => false,
            'metaDescription' => false,
            'metaKeywords' => false,
            'webAnalyticsItaliaID' => '',
            'GoogleCookieless' => 'disabled',
            'WebAnalyticsItaliaCookieless' => 'disabled',
            'CookieConsentMultimedia' => 'enabled',
            'CookieConsentMultimediaText' => 'YouTube, Vimeo, Slideshare, Isuu, Facebook, Twitter, Linkedin, Instagram, Whatsapp',
        );
    }

    private static function generateSeoData()
    {
        $siteData = eZSiteData::fetchByName('SeoSettings');
        if (!$siteData instanceof eZSiteData) {

            $data = self::getEmptySeoData();

            // recupero le informazioni dagli ini o dalla logica precedente di storage

            $googleAnalyticsAccountIDSiteData = eZSiteData::fetchByName('GoogleAnalyticsAccountID');
            if ($googleAnalyticsAccountIDSiteData instanceof eZSiteData) {
                $data['googleAnalyticsAccountID'] = $googleAnalyticsAccountIDSiteData->attribute('value');
                $googleAnalyticsAccountIDSiteData->remove();
            }else{
                $ini = eZINI::instance('openpa.ini');
                if ($ini->hasVariable('Seo', 'GoogleAnalyticsAccountID')) {
                    $data['googleAnalyticsAccountID'] = $ini->variable('Seo', 'GoogleAnalyticsAccountID');
                }
            }
            $cacheFile = OpenPAPageData::getGoogleAnalyticsCache();
            $cacheFile->delete();
            $cacheFile->purge();

            $enableRobotsSiteData = eZSiteData::fetchByName('EnableRobots');
            if ( $enableRobotsSiteData instanceof eZSiteData ) {
                $data['enableRobots'] = $enableRobotsSiteData->attribute('value');
                $enableRobotsSiteData->remove();
            }else{
                $ini = eZINI::instance( 'openpa.ini' );
                $enableRobotsValue = 'enabled';
                if ($ini->hasVariable( 'Seo', 'EnableRobots' )){
                    $enableRobotsValue = $ini->variable( 'Seo', 'EnableRobots' );
                }
                $data['enableRobots'] = self::isSeoDisabledByHostname() ? 'disabled' : $enableRobotsValue;
            }

            $siteData = new eZSiteData(array(
                'name' => 'SeoSettings',
                'value' => json_encode($data)
            ));
            $siteData->store();
        }

        return json_decode($siteData->attribute('value'), true);
    }

    private static function isSeoDisabledByHostname(): bool
    {
        $ini = eZINI::instance( 'openpa.ini' );
        if ($ini->hasVariable('Seo', 'DisabledDomainList')){
            $disableSeoByHostList = (array)$ini->variable('Seo', 'DisabledDomainList');
        }else{
            $disableSeoByHostList = ['opencontent.it'];
        }
        foreach ($disableSeoByHostList as $host) {
            if (strpos(eZSys::hostname(), $host) !== false) {
                return true;
            }
        }

        return false;
    }
}
