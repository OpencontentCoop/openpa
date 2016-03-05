<?php

class OpenPAINI
{

    public static $useDynamicIni = true;

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
    
    protected static function hasFilter( $block, $value, $default )
    {
        if ( in_array( $block . '::' . $value, self::$filters )
             && self::filter( $block, $value, $default ) !== null )
        {
            return true;
        }

        if ( self::$useDynamicIni && isset( self::$dynamicIniMap[$block][$value] ) )
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
        if ( $block && $settingName && $value )
        {
            $frontend = OpenPABase::getFrontendSiteaccessName();
            $path = "settings/siteaccess/{$frontend}/";
            $iniFile = "openpa.ini";
            $ini = new eZINI( $iniFile . '.append', $path, null, null, null, true, true );                
            $ini->setVariable( $block, $settingName, $value );
            eZCache::clearById( 'global_ini' );
            if ( $ini->save() )
            {
                return $path . $iniFile;
            }
            return false;
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
                        $class = $classRepository->load( $class['identifier'] );
                        foreach( $class->fields as $field ){
                            if ( !in_array( $class->identifier . '/' . $field['identifier'], $resultPart ) ){
                                $results[] = $class->identifier . '/' . $field['identifier'];
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

    public static function dynamicIniRetrieveCache( $file, $mtime ){
        $result = include( $file );
        return $result;
    }

    public static function dynamicIniCachePath(){
        return eZSys::cacheDirectory() . '/' . 'openpaini.cache';
    }

    public static function clearDynamicIniCache(){
        eZClusterFileHandler::instance( self::dynamicIniCachePath() )->purge();
    }
    
}