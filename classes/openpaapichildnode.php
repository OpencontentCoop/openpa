<?php
class OpenPAApiChildNode implements ArrayAccess
{
    protected $container;
    
    protected $apiNode;
    
    protected $children;

    
    public function __construct( $item )
    {
        $this->container = $item;
    }
    
    public function __get( $name )
    {
        if ( isset( $this->container[$name] ) )
        {
            return $this->container[$name];
        }
        return false;
    }
    
    public function getChildren()
    {        
        if ( $this->children == null )
        {
            $this->children = array();
            $treeUrl = rtrim( $this->link, '/' ) . '/list'; 
            $children = json_decode( eZHTTPTool::getDataByURL( $treeUrl ), true );            
            foreach( $children['childrenNodes'] as $item )
            {
                $this->children[] = new OpenPAApiChildNode( $item );
            }
        } 
        return $this->children;
    }
    
    public function getApiNode()
    {
        if ( $this->apiNode == NULL )
        {
            $this->apiNode = OpenPAApiNode::fromLink( $this->container['link'] );
        }
        return $this->apiNode;
    }
    
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }
    
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
}