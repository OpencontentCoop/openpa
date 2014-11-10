<?php

class OpenPATempletizable
{
    protected $data = array();

    protected $fnData = array();
    
    public function attributes()
    {
        $keys = array_merge( array_keys( $this->data ), array_keys( $this->fnData ) );
        return $keys;
    }
    
    public function hasAttribute( $key )
    {
        return in_array( $key, $this->attributes() );
    }
    
    public function attribute( $key )
    {
        if ( isset( $this->data[$key] ) )
        {
            return $this->data[$key];
        }
        elseif ( isset( $this->fnData[$key] ) )
        {
            return $this->{$this->fnData[$key]}();
        }
        eZDebug::writeNotice( "Attribute $key does not exist", get_called_class() );
        return false;
    }
    
    public function __construct( $data )
    {
        $this->data = $data;
    }
    
}