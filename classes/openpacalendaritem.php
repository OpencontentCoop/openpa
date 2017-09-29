<?php

class OpenPACalendarItem
{
    protected $data, $fields, $isValid;
    protected $node = null;
    protected $object = null;    
    
    public static function fromEzfindResultArray( array $row )
    {
        $new = new self( $row );
        try
        {            
            $new->parseEzfindResultArray();            
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
            eZDebug::writeNotice( $row, __METHOD__ );            
        }        
        return $new;
    }
        
    public function isValid()
    {
        if ( $this->data['toDateTime'] >= $this->data['fromDateTime'] 
             && isset( $this->data['identifier'] ) )
        {
            return true;
        }
        return false;
    }
    
    public function attributes()
    {
        $keys = array_keys( $this->data );
        $keys[] = 'node';
        $keys[] = 'object';        
        return $keys;
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
                case 'object':
                    return $this->getObject();
                    break;
                case 'node':                    
                    return $this->getNode();
                    break;
                default:
                    return $this->data[$key];
            }
        }
        eZDebug::writeNotice( "Attribute $key does not exist" );
        return null;
    }
    
    protected function __construct( $data )
    {        
        $this->data = $data;
        $this->data['fake_to_time'] = false;
    }
    
    protected static function getDateTime( $string )
    {
        // '%Y-%m-%dT%H:%M:%SZ' -> Y-m-d\TH:i:s\Z
        $date = DateTime::createFromFormat( 'Y-m-d\TH:i:s\Z', $string, OpenPACalendarData::timezone() );
        return $date;
    }
    
    protected function parseEzfindResultArray()
    {        
        if ( !isset( $this->data['name'] ) )
        {
            $this->data['name'] = $this->data['name_t'];
        }
        
        if ( !isset( $this->data['main_node_id'] ) )
        {
            $this->data['main_node_id'] = $this->data['main_node_id_si'];
        }
        
        if ( !isset( $this->data['main_url_alias'] ) )
        {
            $this->data['main_url_alias'] = $this->data['main_url_alias_ms'];
        }
        
        if ( !isset( $this->data['fields'] ) )
        {
            throw new Exception( "Param 'fields' not found in solr row" );
        }
        foreach( $this->data['fields'] as $key => $value )
        {
            if ( empty( $value ) )
            {
                unset( $this->data['fields'][$key] );
            }
        }
        $this->fields = $this->data['fields'];
        
        if ( isset( $this->fields[eZSolr::getMetaFieldName('main_url_alias')] ) )
        {
            $this->data['main_url_alias'] = $this->fields[eZSolr::getMetaFieldName('main_url_alias')];
        }
        
        if ( isset( $this->fields[OpenPASolr::generateSolrField('from_time','date')] ) )
        {
            $fromDate = self::getDateTime( $this->fields[OpenPASolr::generateSolrField('from_time','date')] );
            if ( !$fromDate instanceof DateTime )
            {
                throw new Exception( "Value of 'from_time' not a valid date" );
            }
            $this->data['fromDateTime'] = $fromDate;
            $this->data['from'] = $fromDate->getTimestamp();
            $this->data['identifier'] = $fromDate->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );
        }
        else
        {
            throw new Exception( "Key 'from_time' not found" );
        }
        
        if ( isset( $this->fields[OpenPASolr::generateSolrField('to_time','date')] ) )
        {
            $toDate = self::getDateTime( $this->fields[OpenPASolr::generateSolrField('to_time','date')] );
            if ( !$toDate instanceof DateTime )
            {
                throw new Exception( "Param 'to_time' is not a valid date" );
            }
            if ( $toDate->getTimestamp() == 0 ) // workarpund in caso di eventi (importati) senza data di termine
            {                
                $toDate = $this->fakeToTime( $this->data['fromDateTime'] );
            }
        }
        else
        {
            $toDate = $this->fakeToTime( $this->data['fromDateTime'] );
        }
        $this->data['toDateTime'] = $toDate;            
        $this->data['to'] = $toDate->getTimestamp();
        
        $this->data['duration'] = $this->data['to'] - $this->data['from'];
        
        $this->isValid = $this->isValid();
    }
    
    protected function fakeToTime( DateTime $from )
    {
        /** @var DateTime $toDate */
        $toDate = clone $from;
        $interval = new DateInterval( OpenPAINI::variable( 'Calendar', 'FakeToTimeInterval', 'PT1H' ) );
        if ( !$interval instanceof DateInterval )
        {
            throw new Exception( OpenPAINI::variable( 'Calendar', 'FakeToTimeInterval', 'PT1H' ) . " is not a valid DateInterval: check openpa.ini[Calendar]/FakeToTimeInterval" );
        }
        $toDate->add( $interval );
        $this->data['fake_to_time'] = true;
        return $toDate;
    }
    
    protected function getObject()
    {
        if ( null === $this->object )
        {
            $this->getNode();
            if ( $this->node instanceof eZContentObjectTreeNode )
            {
                return $this->node->attribute( 'object' );
            }
        }
        return $this->object;
    }
    
    protected function getNode()
    {
        if ( null === $this->node )
        {
            $this->node = eZContentObjectTreeNode::fetch( $this->data['main_node_id'] );
        }
        return $this->node;
    }    
    
}