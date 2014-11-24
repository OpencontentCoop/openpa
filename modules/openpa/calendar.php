<?php
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$nodeID = $Params['NodeID'];

$parameters = array();
$redirectSuffix = '';
$today = new DateTime();

eZDebug::writeNotice( $_GET, __FILE__ );

if ( $http->hasGetVariable( 'UrlAlias' ) )
{
    $redirect = $http->getVariable( 'UrlAlias' );
}
else
{
    $node = eZContentObjectTreeNode::fetch( $nodeID );
    if ( $node instanceof eZContentObjectTreeNode )
    {
        $redirect = $node->attribute( 'url_alias' );
    }
}

if ( $http->hasGetVariable( 'ViewCalendarButton' ) )
{
    $parameters['view'] = 'calendar';
    unset( $_GET['View'] );
}
if ( $http->hasGetVariable( 'ViewProgramButton' ) )
{
    $parameters['view'] = 'program';
    $redirectSuffix = "#day-" . $today->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );
    unset( $_GET['View'] );    
}

if ( $http->hasGetVariable( 'View' ) )
{
    $parameters['view'] = $http->getVariable( 'View' );
}

if ( $http->hasGetVariable( 'TodayButton' ) )
{
    //unset( $_GET['SearchDate'] );
    unset( $_GET );
}

if ( $http->hasGetVariable( 'Query' ) )
{
    $query = $http->getVariable( 'Query' );
    if ( !empty( $query ) && $query != 'Cerca testo' ) //@todo workaround per errore ie su jquery.placeholder.js
    {
        $parameters['query'] = $query;
    }
}

if ( $http->hasGetVariable( 'AddIntervalButton' ) )
{
    $currentInterval = $http->hasGetVariable( 'CurrentInterval' ) ? $http->getVariable( 'CurrentInterval' ) : 'P1M'; // intervallo vale solo per la vista program
    
    $currentInterval = str_replace( 'P', '', $currentInterval );
    $currentInterval = str_replace( 'M', '', $currentInterval );
    $currentInterval++;
    $parameters['interval'] = "P{$currentInterval}M";
}

if ( $http->hasGetVariable( 'SearchDate' ) )
{
    $dateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchDate' ) , OpenPACalendarData::timezone() );
    if ( $dateTime instanceof DateTime )
    {
        $parameters['day'] = $dateTime->format( OpenPACalendarData::DAY_IDENTIFIER_FORMAT );
        $parameters['month'] = $dateTime->format( OpenPACalendarData::MONTH_IDENTIFIER_FORMAT );
        $parameters['year'] = $dateTime->format( OpenPACalendarData::YEAR_IDENTIFIER_FORMAT );
        $dateTime->setTime( 0, 0 );
        if ( isset( $parameters['view'] ) && $parameters['view'] == 'program' )
        {
            $redirectSuffix = "#day-" . $dateTime->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );
        }
        if ( $http->hasGetVariable( 'SearchEndDate' ) )
        {
            $endDateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchEndDate' ) , OpenPACalendarData::timezone() );
            $endDateTime->setTime( 23, 59 );
            $interval = $dateTime->diff( $endDateTime );            
            if ( $interval instanceof DateInterval )
            {
                $parameters['interval'] = OpenPACalendarData::DateIntervalString( $interval );
            }
        }
    }
}

if ( $http->hasGetVariable( 'NextMonthCalendarButton' ) )
{
    $dateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchDate' ) , OpenPACalendarData::timezone() );
    if ( $dateTime instanceof DateTime )
    {
        $dateTime->add( new DateInterval('P1M') );
        $parameters['day'] = $dateTime->format( OpenPACalendarData::DAY_IDENTIFIER_FORMAT );
        $parameters['month'] = $dateTime->format( OpenPACalendarData::MONTH_IDENTIFIER_FORMAT );
        $parameters['year'] = $dateTime->format( OpenPACalendarData::YEAR_IDENTIFIER_FORMAT );
    }
}
if ( $http->hasGetVariable( 'PrevMonthCalendarButton' ) )
{
    $dateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchDate' ) , OpenPACalendarData::timezone() );
    if ( $dateTime instanceof DateTime )
    {
        $dateTime->sub( new DateInterval('P1M') );
        $parameters['day'] = $dateTime->format( OpenPACalendarData::DAY_IDENTIFIER_FORMAT );
        $parameters['month'] = $dateTime->format( OpenPACalendarData::MONTH_IDENTIFIER_FORMAT );
        $parameters['year'] = $dateTime->format( OpenPACalendarData::YEAR_IDENTIFIER_FORMAT );
    }
}

if ( $http->hasGetVariable( 'NextDayCalendarButton' ) )
{
    $dateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchDate' ) , OpenPACalendarData::timezone() );
    if ( $dateTime instanceof DateTime )
    {
        $dateTime->add( new DateInterval('P1D') );
        $parameters['day'] = $dateTime->format( OpenPACalendarData::DAY_IDENTIFIER_FORMAT );
        $parameters['month'] = $dateTime->format( OpenPACalendarData::MONTH_IDENTIFIER_FORMAT );
        $parameters['year'] = $dateTime->format( OpenPACalendarData::YEAR_IDENTIFIER_FORMAT );
    }
}
if ( $http->hasGetVariable( 'PrevDayCalendarButton' ) )
{
    $dateTime = DateTime::createFromFormat( OpenPACalendarData::PICKER_DATE_FORMAT, $http->getVariable( 'SearchDate' ) , OpenPACalendarData::timezone() );
    if ( $dateTime instanceof DateTime )
    {
        $dateTime->sub( new DateInterval('P1D') );
        $parameters['day'] = $dateTime->format( OpenPACalendarData::DAY_IDENTIFIER_FORMAT );
        $parameters['month'] = $dateTime->format( OpenPACalendarData::MONTH_IDENTIFIER_FORMAT );
        $parameters['year'] = $dateTime->format( OpenPACalendarData::YEAR_IDENTIFIER_FORMAT );
    }
}

if ( $http->hasGetVariable( 'SearchBlockButton' ) )
{    
    $parameters['interval'] = $http->hasGetVariable( 'SearchBlockInterval' ) ? $http->getVariable( 'SearchBlockInterval' ) : 'P1M'; // intervallo per la ricerca dal blocco: 1 anno di default
}

foreach( array_keys( OpenPACalendarData::defaultParameters() ) as $key )
{
    if ( $http->hasGetVariable( $key ) )
    {
        $value = $http->getVariable( $key );
        if ( !empty( $value ) )
        {
            $parameters[$key] = $value;
        }
    }
}

$redirect = rtrim( $redirect, '/' );
foreach( $parameters as $key => $value )
{
    $redirect .= "/({$key})/{$value}";
}

$module->redirectTo( $redirect . $redirectSuffix );

?>