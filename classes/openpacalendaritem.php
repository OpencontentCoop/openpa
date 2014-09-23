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
    }
    
    protected function __construct( $data )
    {
        $this->data = $data;
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
        $this->fields = $this->data['fields'];
        
        if ( isset( $this->fields['attr_from_time_dt'] ) )
        {
            $fromDate = self::getDateTime( $this->fields['attr_from_time_dt'] );
            if ( !$fromDate instanceof DateTime )
            {
                throw new Exception( "Value of 'attr_from_time_dt' not a valid date" );
            }
            $this->data['fromDateTime'] = $fromDate;
            $this->data['from'] = $fromDate->getTimestamp();
            $this->data['identifier'] = $fromDate->format( OpenPACalendarData::FULLDAY_IDENTIFIER_FORMAT );
        }
        else
        {
            throw new Exception( "Key 'attr_from_time_dt' not found" );
        }
        
        if ( isset( $this->fields['attr_to_time_dt'] ) )
        {
            $toDate = self::getDateTime( $this->fields['attr_to_time_dt'] );
            if ( !$toDate instanceof DateTime )
            {
                throw new Exception( "Param 'attr_to_time_dt' is not a valid date" );
            }
            if ( $toDate->getTimestamp() == 0 ) // workarpund in caso di eventi (importati) senza data di termine
            {
                $toDate = clone $this->data['fromDateTime'];
                $toDate->add( new DateInterval('PT1H') );
            }

            $this->data['toDateTime'] = $toDate;            
            $this->data['to'] = $toDate->getTimestamp();
        }
        else
        {
            //throw new Exception( "Key 'attr_to_time_dt' not found" );
            $toDate = clone $this->data['fromDateTime'];
            $toDate->add( new DateInterval('PT1H') );
            $this->data['toDateTime'] = $toDate;            
            $this->data['to'] = $toDate->getTimestamp();
        }
        
        $this->data['duration'] = $this->data['to'] - $this->data['from'];
        
        $this->isValid = $this->isValid();
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