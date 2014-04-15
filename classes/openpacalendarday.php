<?php

class OpenPACalendarDay implements ArrayAccess
{    
    public $identifier;
    public $dayStartDateTime;
    public $dayStartTimestamp;
    public $dayEndDateTime;
    public $dayEndTimestamp;
    public $day;
    public $month;
    public $year;
    public $urlSuffix;
    public $isToday;
    public $isTomorrow;
    public $isInWeek;
    public $isInMonth;
    protected $container;
    
    function __construct( $identifier )
    {
        $format = OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT;
        $this->identifier = $identifier;
        $dateTime = DateTime::createFromFormat( $format, $identifier, OpenPACalendarData::timezone() );
        if ( !$dateTime instanceof DateTime )
        {            
            throw new Exception( "$identifier in format '$format' is not a valid DateTime" );
        }
        $this->day = $dateTime->setTime( 0, 0, 0 )->format( 'j' );
        $this->month = $dateTime->setTime( 0, 0, 0 )->format( 'n' );
        $this->year = $dateTime->setTime( 0, 0, 0 )->format( 'Y' );
        $this->urlSuffix = "/(day)/{$this->day}/(month)/{$this->month}/(year)/{$this->year}";
        $this->dayStartDateTime = clone $dateTime;
        $this->dayStartDateTime->setTime( 0, 0, 0 );
        $this->dayStartTimestamp = $this->dayStartDateTime->format( 'U' );
        
        $today = mktime( 0, 0, 0, date( 'n' ), date( 'j' ), date( 'Y' ) );
        $tomorrow = mktime( 0, 0, 0, date( 'n' ), date( 'j' ) + 1, date( 'Y' ) );
        $this->isToday = $this->dayStartTimestamp == $today;
        $this->isTomorrow = $this->dayStartTimestamp == $tomorrow;
        $this->isInWeek = date( 'W', $this->dayStartTimestamp ) == date( 'W', $today );
        $this->isInMonth = date( 'n', $this->dayStartTimestamp ) == date( 'n', $today );
        $this->dayEndDateTime = clone $dateTime;
        $this->dayEndDateTime->setTime( 23, 59, 59 );
        $this->dayEndTimestamp = $this->dayEndDateTime->format( 'U' );
        $this->container = array();
    }
    
    function addEvents( array $events )
    {
        foreach( $events as $event )
        {
            if  (
                    ( $event->attribute( 'fromDateTime' ) <= $this->dayStartDateTime                 
                      && $event->attribute( 'toDateTime' ) >= $this->dayEndDateTime )
                 
                ||  ( $event->attribute( 'fromDateTime' ) >= $this->dayStartDateTime                 
                      && $event->attribute( 'toDateTime' ) <= $this->dayEndDateTime )
                
                ||  ( $event->attribute( 'fromDateTime' ) >= $this->dayStartDateTime
                      && $event->attribute( 'fromDateTime' ) <= $this->dayEndDateTime )
                
                ||  ( $event->attribute( 'toDateTime' ) >= $this->dayStartDateTime
                      && $event->attribute( 'toDateTime' ) <= $this->dayEndDateTime )
                
                )
            {                
                $this->add( $event );
            }
        }        
    }
    
    function add( OpenPACalendarItem $event )
    {
        if ( $event->isValid() )
        {
            $this->container[] = $event;
        }
    }
    
    //@dev
    function sort()
    {        
        usort( $this->container, array( "OpenPACalendarDay", "sortByDuration" ) );
    }
    //@dev
    static function sortByDuration( $a, $b )
    {
        if ( $a->attribute( 'duration' ) == $b->attribute( 'duration' ) )
        {
            return 0;
        }
        return ( $a->attribute( 'duration' ) > $b->attribute( 'duration' ) ) ? +1 : -1;
    }
    
    public function attributes()
    {
        return array(
            'identifier',
            'start',
            'end',
            'events',
            'count',
            'day',
            'month',
            'year',
            'uri_suffix',
            'is_today',
            'is_tomorrow',
            'is_in_week',
            'is_in_month'            
        );
    }
    
    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }
    
    public function attribute( $key )
    {
        if ( $this->hasAttribute( $key ) )
        {
            switch( $key )
            {
                case 'identifier':
                    return $this->identifier;
                    break;
                case 'start':
                    return $this->dayStartTimestamp;
                    break;
                case 'end':
                    return $this->dayEndTimestamp;
                    break;
                case 'day':
                    return $this->day;
                    break;
                case 'month':
                    return $this->month;
                    break;
                case 'year':
                    return $this->year;
                    break;
                case 'uri_suffix':
                    return $this->urlSuffix;
                    break;
                case 'is_today':
                    return $this->isToday;
                    break;
                case 'is_tomorrow':
                    return $this->isTomorrow;
                    break;
                case 'is_in_week':
                    return $this->isInWeek;
                    break;
                case 'is_in_month':
                    return $this->isInMonth;
                    break;  
                case 'events':
                    return $this->container;
                    break;
                case 'count':
                    return count( $this->container );
                    break;
            }
        }
        eZDebug::writeNotice( "Attribute $key does not exist" );
    }
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
    
}