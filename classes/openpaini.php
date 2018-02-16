<?php

class OpenPAINI
{

    public static $useDynamicIni;

    private static $enableRobots;

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
        'GeneralSettings::valutation'
        //'SideMenu::EsponiLink'
    );

    public static function variable( $block, $value, $default = null )
    {
        if ( self::hasFilter( $block, $value, $default ) )
        {
            return self::filter( $block, $value, $default );
        }

        $ini = eZINI::instance( 'openpa.ini' );
        $result = $default;
        if ( $ini->hasVariable( $block, $value ) )
        {
            $result = $ini->variable( $block, $value );
        }
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

    protected static function googleAnalyticsAccountID()
    {
        $http = eZHTTPTool::instance();
        $sessionVar = OpenPABase::getCurrentSiteaccessIdentifier() . '-GoogleAnalyticsAccountID';
        if ( !$http->hasSessionVariable($sessionVar) )
        {
            $googleAnalyticsAccountIDSiteData = eZSiteData::fetchByName('GoogleAnalyticsAccountID');
            if ( !$googleAnalyticsAccountIDSiteData instanceof eZSiteData )
            {
                $ini = eZINI::instance( 'openpa.ini' );
                if ( $ini->hasVariable( 'Seo', 'GoogleAnalyticsAccountID' ) )
                {
                    $googleAnalyticsAccountID = $ini->variable( 'Seo', 'GoogleAnalyticsAccountID' );
                    $googleAnalyticsAccountIDSiteData = new eZSiteData(array(
                        'name' => 'GoogleAnalyticsAccountID',
                        'value' => $googleAnalyticsAccountID
                    ));
                    $googleAnalyticsAccountIDSiteData->store();
                }else {
                    $http->setSessionVariable($sessionVar, false);
                }
            }
            $http->setSessionVariable($sessionVar, $googleAnalyticsAccountIDSiteData->attribute('value'));
        }
        return $http->sessionVariable($sessionVar);
    }

    protected static function isRobotsEnabled()
    {
        if ( self::$enableRobots === null )
        {
            $enableRobotsSiteData = eZSiteData::fetchByName('EnableRobots');
            if ( !$enableRobotsSiteData instanceof eZSiteData )
            {
                $ini = eZINI::instance( 'openpa.ini' );
                $enableRobotsValue = 'enabled';
                if ($ini->hasVariable( 'Seo', 'EnableRobots' )){
                    $enableRobotsValue = $ini->variable( 'Seo', 'EnableRobots' );
                }
                if (strpos(eZSys::hostname(), 'opencontent.it') !== false){
                    $enableRobotsValue = 'disabled';
                }
                $enableRobotsSiteData = new eZSiteData(array(
                    'name' => 'EnableRobots',
                    'value' => $enableRobotsValue
                ));
                $enableRobotsSiteData->store();
                self::$enableRobots = $enableRobotsValue;

            }
            self::$enableRobots = $enableRobotsSiteData->attribute('value');
        }
        return self::$enableRobots;
    }

    protected static function filter( $block, $value, $default )
    {
        $filter = $block . '::' . $value;
        switch( $filter )
        {
            case 'TopMenu::NodiCustomMenu':
                return OpenPaFunctionCollection::fetchTopMenuNodes();
            break;

            case 'GestioneSezioni::sezioni_per_tutti':
                return self::filterSezioniPerTutti();
            break;

            case 'Attributi::EscludiDaRicerca':
                return self::variable( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca', $default );
            break;

            case 'Seo::GoogleAnalyticsAccountID':
                return self::googleAnalyticsAccountID();
            break;

            case 'Seo::EnableRobots':
                return self::isRobotsEnabled();
                break;

            case 'GeneralSettings::valutation':
                    if (eZINI::instance('openpa.ini')->hasVariable('GeneralSettings', 'valutation')
                        && eZINI::instance('openpa.ini')->variable('GeneralSettings', 'valutation') == 1){
                        $valuationClass = eZContentClass::fetchByIdentifier('valuation');
                        if ($valuationClass instanceof eZContentClass){
                            return $valuationClass->objectCount() > 0;
                        }
                    }
                    return false;
            break;

        }

        if ( isset( self::$dynamicIniMap[$block][$value] ) )
        {
            if ( self::$dynamicIniData === null ){
                self::$dynamicIniData = eZClusterFileHandler::instance( self::dynamicIniCachePath() )->processCache(
                    array( 'OpenPAINI', 'dynamicIniRetrieveCache' ),
                    array( 'OpenPAINI', 'dynamicIniGenerateCache' )
                );
            }

            return isset( self::$dynamicIniData[$block][$value] ) ? self::$dynamicIniData[$block][$value] : $default;
        }

        return null;
    }

    public static function set( $block, $settingName, $value )
    {
        if ( $block && $settingName && $value ) {
            $filter = $block . '::' . $settingName;
            switch ($filter) {

                case 'Seo::GoogleAnalyticsAccountID':
                    $data = eZSiteData::fetchByName('GoogleAnalyticsAccountID');
                    if (!$data instanceof eZSiteData) {
                        $data = new eZSiteData(array(
                            'name' => 'GoogleAnalyticsAccountID',
                            'value' => ''
                        ));
                    }
                    $data->setAttribute('value', $value);
                    $data->store();

                    $http = eZHTTPTool::instance();
                    $sessionVar = OpenPABase::getCurrentSiteaccessIdentifier() . '-GoogleAnalyticsAccountID';
                    $http->setSessionVariable($sessionVar, $data->attribute('value'));

                    return true;
                    break;

                case 'Seo::EnableRobots':
                    $data = eZSiteData::fetchByName('EnableRobots');
                    if (!$data instanceof eZSiteData) {
                        $data = new eZSiteData(array(
                            'name' => 'EnableRobots',
                            'value' => ''
                        ));
                    }
                    $data->setAttribute('value', $value);
                    $data->store();

                    return true;
                    break;

                default:

                    $frontend = OpenPABase::getFrontendSiteaccessName();
                    $path = "settings/siteaccess/{$frontend}/";
                    $iniFile = "openpa.ini";
                    $ini = new eZINI($iniFile . '.append', $path, null, null, null, true, true);
                    $ini->setVariable($block, $settingName, $value);
                    eZCache::clearById('global_ini');
                    if ($ini->save()) {
                        return $path . $iniFile;
                    }

                    return false;
            }
        }
        return false;
    }


    public static function dynamicIniGenerateCache( $file ){
        $result = array();
        foreach( self::$dynamicIniMap as $block => $values ){

            $result[$block] = array();

            foreach( $values as $variable => $settings ){

                $result[$block][$variable] = array();

                list( $handler, $key ) = explode( '.', $settings['to'] );
                $matchValue = $settings['value'];

                $data = OCClassExtraParameters::fetchObjectList(OCClassExtraParameters::definition(),
                    null,
                    array(
                        'handler' => $handler,
                        'key' => $key,
                        'value' => 1
                    )
                );

                $results = array();
                $resultPart = array();
                foreach( $data as $item ){
                    $resultPart[] = $item->attribute( 'class_identifier' ) . '/' .  $item->attribute( 'attribute_identifier' );
                }

                if ( $matchValue == 0 ){
                    $classRepository = new \Opencontent\Opendata\Api\ClassRepository();
                    $classes = $classRepository->listAll();
                    foreach( $classes as $class ){
                        try {
                            $class = $classRepository->load($class['identifier']);
                            foreach ($class->fields as $field) {
                                if (!in_array($class->identifier . '/' . $field['identifier'], $resultPart)) {
                                    $results[] = $class->identifier . '/' . $field['identifier'];
                                }
                            }
                        }catch(Exception $e){
                            eZDebug::writeError($e->getMessage(), __METHOD__);
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

    public static function dynamicIniRetrieveCache( $file, $mtime ){
        if (file_exists($file)){
            $result = include( $file );
        }else{
            eZDebug::writeNotice("File $file not exists, regenerate", __METHOD__);
            $result = new eZClusterFileFailure(eZClusterFileFailure::FILE_RETRIEVAL_FAILED);
        }

        return $result;
    }

    public static function dynamicIniCachePath(){
        return eZSys::cacheDirectory() . '/' . 'openpaini.cache';
    }

    public static function clearDynamicIniCache(){
        eZClusterFileHandler::instance( self::dynamicIniCachePath() )->delete();
        eZCache::clearContentCache(null);
    }

}
