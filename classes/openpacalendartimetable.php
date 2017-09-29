<?php

class OpenPACalendarTimeTable
{
    /**
     * @param eZContentObjectTreeNode $node
     * @param array $parameters
     * @param string $timetableAttributeIdentifier
     *
     * @return OpenPACalendarItem[]
     */
    public static function getEvents( eZContentObjectTreeNode $node, array $parameters, $timetableAttributeIdentifier = 'timetable' )
    {
        $events = array();
        $base = array(
            'name' => $node->attribute( 'name' ),
            'main_node_id' => $node->attribute( 'main_node_id' ),
            'main_url_alias' => $node->attribute( 'url_alias' ),
            'fields' => array(
                OpenPASolr::generateSolrField('from_time','date') => 0,
                OpenPASolr::generateSolrField('to_time','date') => 0
            )
        );

        try
        {
            $startDate = new DateTime( 'now', OpenPACalendarData::timezone() );
            $startDate->setDate(
                date( 'Y', $parameters['search_from_timestamp'] ),
                date( 'n', $parameters['search_from_timestamp'] ),
                date( 'j', $parameters['search_from_timestamp'] )
            );
            if ( isset( $parameters['search_to_timestamp']) )
            {
                $endDate = new DateTime( 'now', OpenPACalendarData::timezone() );
                $endDate->setDate(
                    date( 'Y', $parameters['search_to_timestamp'] ),
                    date( 'n', $parameters['search_to_timestamp'] ),
                    date( 'j', $parameters['search_to_timestamp'] )
                );
            }
            elseif ( isset( $parameters['interval']) )
            {
                $endDate = clone $startDate;
                $endDate->add( new DateInterval( $parameters['interval'] ) );
            }
            else
            {
                throw new Exception( "Specify search_to_timestamp or interval parameter" );
            }

            $byDayInterval = new DateInterval( 'P1D' );
            /** @var DateTime[] $byDayPeriod */
            $byDayPeriod = new DatePeriod( $startDate, $byDayInterval, $endDate );
            
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $node->attribute( 'data_map' );
            $minTimeStamp = isset( $dataMap['timetable_from_time'] ) && $dataMap['timetable_from_time']->hasContent() ? $dataMap['timetable_from_time']->toString() : 0;
            $maxTimeStamp = isset( $dataMap['timetable_to_time'] ) && $dataMap['timetable_to_time']->hasContent() ? $dataMap['timetable_to_time']->toString() : 0;
            $minTime = new DateTime( 'now', OpenPACalendarData::timezone() );
            $minTime->setTimestamp( $minTimeStamp );
            $minTime->setTime( 0, 0 );
            $maxTime = new DateTime( 'now', OpenPACalendarData::timezone() );
            $maxTime->setTimestamp( $maxTimeStamp );
            $maxTime->setTime( 23, 59 );

            $timeTable = self::getTimeTableFromNode( $node, $timetableAttributeIdentifier );
            foreach( $byDayPeriod as $date )
            {                
                if ( $date >= $minTime && $date <= $maxTime )
                {
                    $weekDay = $date->format( 'w' );
                    if ( isset( $timeTable[$weekDay] ) )
                    {
                        foreach( $timeTable[$weekDay] as $value )
                        {
                            $newEvent = $base;
                            $date->setTime( $value['from_time']['hour'], $value['from_time']['minute'] );
                            $newEvent['fields'][OpenPASolr::generateSolrField('from_time','date')] = $date->format( 'Y-m-d\TH:i:s\Z' );
                            $date->setTime( $value['to_time']['hour'], $value['to_time']['minute'] );
                            $newEvent['fields'][OpenPASolr::generateSolrField('to_time','date')] = $date->format( 'Y-m-d\TH:i:s\Z' );
                            $item = OpenPACalendarItem::fromEzfindResultArray( $newEvent );
                            $events[] = $item;
                        }
                    }
                }
            }
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
        }
        return $events;
    }

    public static function getTimeTableFromNode( eZContentObjectTreeNode $node, $timetableAttributeIdentifier = 'timetable' )
    {
        $dataMap = $node->attribute( 'data_map' );
        if ( isset( $dataMap[$timetableAttributeIdentifier] )
             && $dataMap[$timetableAttributeIdentifier] instanceof eZContentObjectAttribute
             && $dataMap[$timetableAttributeIdentifier]->attribute( 'has_content' ))
        {
            $timeTableContent = $dataMap[$timetableAttributeIdentifier]->attribute( 'content' )->attribute( 'matrix' );
            $timeTable = array();
            foreach( $timeTableContent['columns']['sequential'] as $column )
            {
                foreach( $column['rows'] as $row )
                {
                    $parts = explode( '-', $row );
                    if ( count( $parts ) == 2 )
                    {
                        $fromParts = explode( ':', $parts[0] );
                        if ( count( $fromParts ) != 2 ) $fromParts = explode( '.', $parts[0] );

                        $toParts = explode( ':', $parts[1] );
                        if ( count( $toParts ) != 2 ) $toParts = explode( '.', $parts[1] );

                        if ( count( $fromParts ) == 2 && count( $toParts ) == 2 )
                        {
                            if ( !isset( $timeTable[$column['identifier']] ) )
                            {
                                $timeTable[$column['identifier']] = array();
                            }
                            $timeTable[$column['identifier']][] = array(
                                'from_time' => array( 'hour' => trim( $fromParts[0] ), 'minute' => trim( $fromParts[1] ) ),
                                'to_time' => array( 'hour' => trim( $toParts[0] ), 'minute' => trim( $toParts[1] ) ),
                            );
                        }
                    }
                }
            }
            return $timeTable;
        }
        return array();
    }
} 