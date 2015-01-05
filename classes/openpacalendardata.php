<?php

class OpenPACalendarData
{
    const INTERVAL_MONTH = 'P1M';
    const INTERVAL_WEEK = 'P1W';
    const PICKER_DATE_FORMAT = 'd-m-Y';
    const FULLDAY_IDENTIFIER_FORMAT = 'Y-n-j';
    const DAY_IDENTIFIER_FORMAT = 'j';
    const MONTH_IDENTIFIER_FORMAT = 'n';
    const YEAR_IDENTIFIER_FORMAT = 'Y';    
    
    const VIEW_CALENDAR = 'calendar';
    const VIEW_PROGRAM = 'program';
    
    static $CUSTOM_PARAMETERS_KEYS = array( 'custom_interval', 'custom_filter' );
    static $CUSTOM_FILTERS = array(
        'MANIFESTAZIONE' => 'subattr_tipo_evento___name____s:"Manifestazione"',
        'SPECIAL' => 'attr_special_b:true'
    );
    const CUSTOM_TAG_TODAY = 'TODAY';
    const CUSTOM_TAG_TOMORROW = 'TOMORROW';
    
    public $calendar,
        $hasCustomParameters = false,
        $data = array();
    
    protected $parameters, $filters = array(), $view;
    
    public static function timezone()
    {
        //@todo                
        return new DateTimeZone( date_default_timezone_get() );
    }
    
    function __construct( eZContentObjectTreeNode $calendar )
    {
        $this->calendar = $calendar;
    }
    
    public function setParameter( $key, $value )
    {
        $this->parameters[$key] = $value;
    }
    
    protected function setCustomParameter( $key, $value )
    {
        if ( in_array( $key, self::$CUSTOM_PARAMETERS_KEYS ) )
        {
            switch( $key )
            {
                case 'custom_interval':
                    $this->hasCustomParameters = true;
                    $parts = explode( '-', $value );
                    $startTag = $parts[0];
                    switch( $startTag )
                    {
                        case self::CUSTOM_TAG_TODAY:                            
                            break;
                        
                        case self::CUSTOM_TAG_TOMORROW:
                            $tomorrow = mktime( 0, 0, 0, date("m"), date("d") + 1, date("Y") );
                            $this->setParameter( 'day', date( self::DAY_IDENTIFIER_FORMAT, $tomorrow ) );
                            $this->setParameter( 'month', date( self::MONTH_IDENTIFIER_FORMAT, $tomorrow ) );
                            $this->setParameter( 'year', date( self::YEAR_IDENTIFIER_FORMAT, $tomorrow ) );                            
                            break;
                    }
                    if ( isset( $parts[1] ) )
                    {
                        $this->setParameter( 'interval', $parts[1] );
                    }
                    else
                    {
                        $this->setParameter( 'interval', 'PT1439M' ); // 23 ore e 59 minuti
                    }
                    break;
                case 'custom_filter':
                    $this->hasCustomParameters = true;
                    $parts = explode( '-', $value );
                    foreach( $parts as $part )
                    {
                        if ( array_key_exists( $part, self::$CUSTOM_FILTERS ) )
                        {
                            $this->filters[] = self::$CUSTOM_FILTERS[$part];
                        }                        
                    }
                    break;
            }
        }
    }
    
    public function setParameters( $params )
    {            
        //eZDebug::writeNotice( $params, __METHOD__ );
        $defaultParameters = self::defaultParameters();
        
        if ( $this->calendar instanceof eZContentObjectTreeNode )
        {
            $defaultParameters['subtree'] = array( $this->calendar->attribute( 'node_id' ) );
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->calendar->attribute( 'data_map' );
            if ( isset( $dataMap['subtree_array'] )
                 && $dataMap['subtree_array'] instanceof eZContentObjectAttribute
                 && $dataMap['subtree_array']->attribute( 'data_type_string' ) == 'ezobjectrelationlist'
                 && $dataMap['subtree_array']->attribute( 'has_content' ) )
            {            
                $content = $dataMap['subtree_array']->attribute( 'content' );
                foreach( $content['relation_list'] as $item )
                {
                    if ( isset( $item['node_id'] ) && intval( $item['node_id'] ) > 0  )
                    {
                        $defaultParameters['subtree'][] = $item['node_id'];
                    }
                }
            }
            elseif ( isset( $dataMap['subtree'] )
                     && $dataMap['subtree'] instanceof eZContentObjectAttribute
                     && $dataMap['subtree']->attribute( 'data_type_string' ) == 'ezobjectrelation'
                     && $dataMap['subtree']->attribute( 'has_content' ) )
            {                
                $content = $dataMap['subtree']->attribute( 'content' );
                if ( $content instanceof eZContentObject )
                {
                    if ( intval( $content->attribute( 'main_node_id' ) ) > 0 )
                    {
                        $defaultParameters['subtree'][] = $content->attribute( 'main_node_id' );
                    }
                }
            }            
        }
        
        foreach( $defaultParameters as $key => $value )
        {
            if ( isset( $params[$key] ) )
            {
                if ( empty( $params[$key] ) )
                {
                    $this->setParameter( $key, $value );
                }
                else
                {
                    if ( is_string( $params[$key] ) )
                    {
                        $paramValue = explode( '|', $params[$key] );
                        if ( count( $paramValue ) == 1 ) $paramValue = $paramValue[0];                        
                    }
                    else
                    {
                        $paramValue = $params[$key];
                    }
                    $this->setParameter( $key, $paramValue );
                }
            }
            else
            {
                $this->setParameter( $key, $value );
            }
        }
        
        foreach( self::$CUSTOM_PARAMETERS_KEYS as $key )
        {
            if ( isset( $params[$key] ) )
            {
                $this->setCustomParameter( $key, $params[$key] );
            }
        }
                
        if ( isset( $params['view'] ) )
        {
            $this->view = $params['view'];
        }
        //eZDebug::writeNotice( $this->parameters, __METHOD__ );
    }
    
    public function fetch()
    {
        $startDateArray = array(
            'hour' => '00',
            'minute' => '00',
            'second' => '00',
            'month' => $this->parameters['month'],
            'day' => $this->parameters['day'],
            'year' => $this->parameters['year']          
        );
        $originalStartDateTime = DateTime::createFromFormat( 'H i s n j Y', implode( ' ', $startDateArray ), self::timezone() );
        if ( !$originalStartDateTime instanceof DateTime )
        {
            throw new Exception( "Data non valida" );
        }
        $this->parameters['picker_date'] = date( self::PICKER_DATE_FORMAT, $originalStartDateTime->getTimestamp() );
        $this->parameters['search_from_picker_date'] = $this->parameters['picker_date'];
        if ( $this->parameters['interval'] == self::INTERVAL_MONTH && !$this->hasCustomParameters )             
        {
            $startDateArray['day'] = 1;
            $startDateTime = DateTime::createFromFormat( 'H i s n j Y', implode( ' ', $startDateArray ), self::timezone() );        
        }
        elseif ( $this->parameters['interval'] == self::INTERVAL_WEEK && !$this->hasCustomParameters )             
        {
            $startDateTime = clone $originalStartDateTime;
            $dayOfWeek = $startDateTime->format( "w" ) - 1;
            $startDateTime->modify( "-$dayOfWeek day" );
        }
        else
        {
            $startDateTime = clone $originalStartDateTime;
        }
        if ( !$startDateTime instanceof DateTime )
        {
            throw new Exception( "Data non valida" );
        }        
        $interval = new DateInterval( $this->parameters['interval'] );
        if ( !$interval instanceof DateInterval )
        {
            throw new Exception( "Intervallo non valido" );
        }        
        
        // start day        
        $this->parameters['timestamp'] = $startDateTime->getTimestamp();
        $this->parameters['days_of_month'] = date( 't', $startDateTime->getTimestamp() );
        $this->parameters['start_weekday'] = date( 'w', $startDateTime->getTimestamp() );
        $endOfMonthArray = array_merge( $startDateArray, array( 'day' => $this->parameters['days_of_month'] ) );
        $endOfMonthDateTime = DateTime::createFromFormat( 'H i s n j Y', implode( ' ', $endOfMonthArray ), self::timezone() );                  
        $this->parameters['end_weekday'] = date( 'w', $endOfMonthDateTime->getTimestamp() );
        $this->parameters['search_from_solr'] = ezfSolrDocumentFieldBase::preProcessValue( $startDateTime->getTimestamp(), 'date' );
        $this->parameters['search_from_timestamp'] = $startDateTime->getTimestamp();
        
        // end day
        $endDateTime = clone $startDateTime;
        $endDateTime->add( $interval );
        $this->parameters['search_to_solr'] = ezfSolrDocumentFieldBase::preProcessValue( $endDateTime->getTimestamp() - 1 , 'date' );
        $this->parameters['search_to_timestamp'] = $endDateTime->getTimestamp();
        $this->parameters['search_to_picker_date'] = date( self::PICKER_DATE_FORMAT, $endDateTime->getTimestamp() );
        
        // filter        
        $this->filters[] = array(
            'or',
            'attr_from_time_dt:[' . $this->parameters['search_from_solr'] . ' TO ' . $this->parameters['search_to_solr'] . ']',
            'attr_to_time_dt:[' . $this->parameters['search_from_solr'] . ' TO ' . $this->parameters['search_to_solr'] . ']',
            array(
                'and',
                'attr_from_time_dt:[* TO ' . $this->parameters['search_from_solr'] . ']',
                'attr_to_time_dt:[' . $this->parameters['search_to_solr'] . ' TO *]'
            )
        );
        
        $facets = array();
        //esempio: $this->parameters['Materia'] = '"Economia e diritto"';
        foreach( self::relatedParameters() as $fieldIdentifier => $fieldName )
        {            
            if ( isset( $this->parameters[$fieldName] ) && $this->parameters[$fieldName] !== false )
            {
                if ( is_array( $this->parameters[$fieldName] ) )
                {                    
                    $orFilter = array( 'or' );
                    foreach( $this->parameters[$fieldName] as $value )
                    {
                        $filterValue = addcslashes( $value, '"' );
                        $orFilter[] = "subattr_{$fieldIdentifier}___name____s:\"{$filterValue}\"";
                    }
                    $this->filters[] = $orFilter;
                }
                else
                {
                    $filterValue = addcslashes( $this->parameters[$fieldName], '"' );
                    $this->filters[] = "subattr_{$fieldIdentifier}___name____s:\"{$filterValue}\"";
                }
            }
            
            $facets[] = array( 'field' => "subattr_{$fieldIdentifier}___name____s",
                               'name'  => $fieldName,
                               'limit' => 100,
                               'sort' => 'alpha' );
        }
        
        $this->filters[] = '-subattr_tipo_eventi_manifestazioni___name____s:"Manifestazione"';
        
        if ( is_array( $this->parameters['filter'] ) )
        {
            $this->filters = array_merge( $this->filters, $this->parameters['filter'] );
        }
        
        $sortBy = array(            
            'attr_priority_si' => 'desc',
            'attr_special_b' => 'desc'
        );
        
        if ( class_exists( 'ezfIndexEventDuration' ) )
        {
            $sortBy['extra_event_duration_s'] = 'asc';    
        }
        
        $sortBy['attr_from_time_dt'] = 'asc';
                
        $this->parameters['fields_to_return'] = array_unique( array_merge( $this->parameters['fields_to_return'], array( 'attr_from_time_dt', 'attr_to_time_dt', 'meta_node_id_si', 'meta_url_alias_ms', 'meta_main_url_alias_ms' ) ) );
        
        $solrFetchParams = array(
            'SearchOffset' => 0,
            'SearchLimit' => 1000,
            'Facet' => $facets,
            'SortBy' => $sortBy,
            'Filter' => $this->filters,
            'SearchContentClassID' => null,
            'SearchSectionID' => null,
            'SearchSubTreeArray' => $this->parameters['subtree'],
            'AsObjects' => false,
            'SpellCheck' => null,
            'IgnoreVisibility' => null,
            'Limitation' => null,
            'BoostFunctions' => null,
            'QueryHandler' => 'ezpublish',
            'EnableElevation' => true,
            'ForceElevation' => true,
            'SearchDate' => null,
            'DistributedSearch' => null,
            'FieldsToReturn' => $this->parameters['fields_to_return'],
            'SearchResultClustering' => null,
            'ExtendedAttributeFilter' => array()
        );        
        $solrSearch = new eZSolr();
        $solrResult = $solrSearch->search( $this->parameters['query'], $solrFetchParams );
        
        //eZDebug::writeNotice( $solrFetchParams, __METHOD__ );
        //eZDebug::writeNotice( $solrResult, __METHOD__ );        
        
        $this->data['parameters'] = $this->parameters;
        $this->data['fetch_parameters'] = $solrFetchParams;
        
        /** @var ezfSearchResultInfo $extra */
        $extra = $solrResult['SearchExtras'];
        $facetFields = $extra->attribute( 'facet_fields' );
        //eZDebug::writeNotice( $solrResult['SearchExtras'], __METHOD__ );
        $resultFacets = array();
        foreach( $facets as $index => $facet )
        {
            if ( isset( $facetFields[$index]['queryLimit'] ) && !empty( $facetFields[$index]['queryLimit'] ) )
            {
                if ( isset( $facetFields[$index]['fieldList'] ) )
                {
                    foreach( $facetFields[$index]['fieldList'] as $key => $value )
                    {
                        $resultFacets[ $facet['name']][] = $key;
                    }
                }
                else
                {
                    foreach( $facetFields[$index]['nameList'] as $key => $value )
                    {
                        $resultFacets[ $facet['name']][] = $key;
                    }
                }
            }
        }        
        $this->data['search_facets'] = $this->sortFacets( $resultFacets );        
        
        $events = array();
        foreach( $solrResult['SearchResult'] as $item )
        {            
            $item = $this->fixMainNode( $item );
            $event = OpenPACalendarItem::fromEzfindResultArray( $item );
            if ( $event->isValid() )
            {
                $events[] = $event;
            }
        }
        $timeTableEvents = $this->fetchTimeTableEvents();
        if ( count( $timeTableEvents ) )
        {
            $events = self::reorderEvents( array_merge( $events, $timeTableEvents ) );
        }
        //eZDebug::writeNotice( $events, __METHOD__ );  
        $this->data['search_count'] = count( $events );
        $eventsByDay = array();
        $byDayInterval = new DateInterval( 'P1D' );
        /** @var DateTime[] $byDayPeriod */
        $byDayPeriod = new DatePeriod( $startDateTime, $byDayInterval, $endDateTime );
        foreach( $byDayPeriod as $date )
        {
            $identifier = $date->format( self::FULLDAY_IDENTIFIER_FORMAT );            
            $calendarDay = new OpenPACalendarDay( $identifier );            
            $calendarDay->addEvents( $events );
            $eventsByDay[$identifier] = $calendarDay;            
        }
        $this->data['events'] = $events;
        $this->data['day_by_day'] = $eventsByDay;
        //eZDebug::writeNotice( $eventsByDay, __METHOD__ ); 
        //eZDebug::writeNotice( $this->data['search_facets'], __METHOD__ );         
    }
    
    protected function fixMainNode( $item )
    {
        $useIndex = false;        
        $pathStringArray = array();
        if ( isset( $item['path_string_ms'] ) )
        {
            $pathStringArray = $item['path_string_ms'];
        }
        elseif ( isset( $item['path_string'] ) )
        {
            $pathStringArray = $item['path_string'];
        }
        foreach( $pathStringArray as $key => $path )
        {
            $pathArray = explode( '/', ltrim( $path, '/' ) );
            $isInSubTree = array_intersect( $this->parameters['subtree'], $pathArray );            
            if ( count( $isInSubTree ) > 0 )
            {
                $useIndex = $key;
                break;
            }
        }        
        if ( $useIndex !== false && isset( $item['url_alias'][$useIndex] ) && isset( $item['node_id'][$useIndex] ) )
        {
            if ( isset( $item['main_node_id_si'] ) )
                $item['main_node_id_si'] = $item['node_id'][$useIndex];
            elseif ( isset( $item['main_node_id'] ) )
                $item['main_node_id'] = $item['node_id'][$useIndex];
            
            if ( isset( $item['main_url_alias_ms'] ) )
                $item['main_url_alias_ms'] = $item['url_alias'][$useIndex];
            elseif ( isset( $item['main_url_alias'] ) )
                $item['main_url_alias'] = $item['url_alias'][$useIndex];
        }
        //eZDebug::writeNotice( $item, __METHOD__ );
        return $item;
    }

    protected static function reorderEvents( $items )
    {
        usort(
            $items, function( OpenPACalendarItem $a, OpenPACalendarItem $b ) {
                return intval( $a->attribute( 'fromDateTime' ) > $b->attribute( 'fromDateTime' ) );
             }
        );
        return $items;
    }


    protected function fetchTimeTableEvents()
    {
        $events = array();

        $filters = array(
            'or',
            'attr_timetable_from_time_dt:[' . $this->parameters['search_from_solr'] . ' TO ' . $this->parameters['search_to_solr'] . ']',
            'attr_timetable_to_time_dt:[' . $this->parameters['search_from_solr'] . ' TO ' . $this->parameters['search_to_solr'] . ']',
            array(
                'and',
                'attr_timetable_from_time_dt:[* TO ' . $this->parameters['search_from_solr'] . ']',
                'attr_timetable_to_time_dt:[' . $this->parameters['search_to_solr'] . ' TO *]'
            )
        );

        $solrFetchParams = array(
            'SearchOffset' => 0,
            'SearchLimit' => 1000,
            'Filter' => $filters,
            'SearchSubTreeArray' => $this->parameters['subtree']
        );
        $solrSearch = new eZSolr();
        $solrResult = $solrSearch->search( $this->parameters['query'], $solrFetchParams );

        foreach( $solrResult['SearchResult'] as $item )
        {
            $events = array_merge( $events, OpenPACalendarTimeTable::getEvents( $item, $this->parameters ) );
        }
        return $events;
    }

    protected function sortFacets( $resultFacets )
    {
        $sorted = array();
        foreach( $resultFacets as $name => $values )
        {
            $sorted[$name] = $this->makeFacetTree( $name, $values );
        }        
        return $sorted;
    }
    
    protected function makeFacetTree( $name, $values )
    {
        $return = array();
        switch( $name )
        {            
            case 'Materia':
                $filter = array( 'or' );
                foreach( $values as $value )
                {
                    $filter[] = "attr_titolo_s:\"{$value}\"";
                }
                $solrFetchParams = array(
                    'SearchOffset' => 0,
                    'SearchLimit' => count( $values ),
                    'Filter' => $filter,
                    'SearchContentClassID' => array( 'materia', 'sotto_materia' ),
                    'AsObjects' => false                    
                );        
                $solrSearch = new eZSolr();
                $solrResult = $solrSearch->search( '', $solrFetchParams );
                
                if ( $solrResult['SearchCount'] == 0 )
                {
                    foreach( $values as $value )
                    {
                        $return[] = array( 'indent' => false,
                                           'name' => $value,
                                           'value' => $value );
                    }
                }
                else
                {                    
                    $materie = array();
                    foreach( $solrResult['SearchResult'] as $item )
                    {
                        if ( $item['class_identifier'] == 'materia' )
                        {
                            $materie[$item['name']] = $item['main_node_id'];
                        }
                    }                    
                    foreach( $materie as $name => $nodeID )
                    {
                        $return[] = array( 'indent' => false,
                                           'name' => $name,
                                           'value' => $name,
                                        );
                        foreach( $solrResult['SearchResult'] as $item )
                        {
                            if ( $item['class_identifier'] == 'sotto_materia'
                                 && $item['main_parent_node_id'] == $nodeID )
                            {
                                $return[] = array( 'indent' => true,
                                                   'name' => $item['name'],
                                                   'value' => $item['name'],
                                                   );
                            }
                        }
                    }                    
                }
                break;
            
            default:
                foreach( $values as $value )
                {
                    $return[] = array( 'indent' => false,
                                       'name' => $value,
                                       'value' => $value );
                }
                break;
        }
        return $return;
        
    }
    
    protected static function relatedParameters()
    {
        $default = array(
            'tipo_evento' => 'Tipologia',
            'materia' => 'Materia',
            'io_sono' => 'Destinatari',
            'iniziativa' => 'Manifestazione',                     
            'circoscrizione' => 'Circoscrizione'
        );
        return OpenPAINI::variable( 'CalendarSettings', 'RelationFilters', $default );
    }
    
    public static function defaultParameters()
    {
        $default = array(
            'query' => '',
            'day' => date( self::DAY_IDENTIFIER_FORMAT ),
            'month' => date( self::MONTH_IDENTIFIER_FORMAT ),
            'year' => date( self::YEAR_IDENTIFIER_FORMAT ),            
            'current_timestamp' => time(),            
            'current_day' => date( self::DAY_IDENTIFIER_FORMAT ),
            'current_month' => date( self::MONTH_IDENTIFIER_FORMAT ),
            'current_year' => date( self::YEAR_IDENTIFIER_FORMAT ),
            'interval' => 'P1M',
            'offset' => 0,            
            'filter' => false,
            'fields_to_return' => array(
                'attr_from_time_dt',
                'attr_to_time_dt',                
            )
        );
        $related = array_fill_keys( self::relatedParameters(), false );
        return array_merge( $default, $related );
    }
    
    public static function DateIntervalString( DateInterval $delta )
    {
        //Read all date-parts there are not 0
        $date = array_filter( array( 'Y' => $delta->y,
                                     'M' => $delta->m,
                                     'D' => $delta->d ) );
        
        //Read all time-parts there are not 0
        $time = array_filter( array( 'H' => $delta->h,
                                     'M' => $delta->i,
                                     'S' => $delta->s ) );
    
        //Convert each part to spec-Strings
        foreach ( $date as $key => &$value ) $value = $value.$key;
        foreach ( $time as $key => &$value ) $value = $value.$key;
    
        //Create date spec-string
        $spec = 'P' . implode('', $date);            
        //add time spec-string
        if ( count( $time ) > 0 )
            $spec .= 'T' . implode( '', $time );
        return $spec;        
    }   
    
    function attributes()
    {
        return array_keys( $this->data );
    }
    
    function hasAttribute( $key )
    {
        return isset( $this->data[$key] );
    }
    
    function attribute( $key )
    {
        if ( $this->hasAttribute( $key ) )
        {
            return $this->data[$key];
        }
        eZDebug::writeNotice( "Attribute $key does not exist", __METHOD__ );
        return null;
    }
}
