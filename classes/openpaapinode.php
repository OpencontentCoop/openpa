<?php
class OpenPAApiNode implements ArrayAccess
{
    protected $container;

    public static function fromLink( $url )
    {
        $data = json_decode( eZHTTPTool::getDataByURL( $url ), true );
        if ( $data )
        {
            return new self( $data );
        }
        return false;
    }
    
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
    
    public function searchLocal( $useRemote = true, $parentNode = false )
    {
        $object = null;

        if ( $useRemote )
        {
            $object = eZContentObject::fetchByRemoteID( $this->metadata['objectRemoteId'] );
        }
        else
        {
            if ( !$parentNode )
            {
                $parentNode = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            }
            
            $params = array(
                'SearchLimit' => 1,
                'Filter' => null,
                'SearchContentClassID' => array( $this->metadata['classIdentifier']  ),
                'SearchSubTreeArray' => array( $parentNode ),
                'Limitation' => array()
            );        
            $solrSearch = new eZSolr();
            $search = $solrSearch->search( '"' . $this->metadata['objectName'] . '"', $params );
            if ( $search['SearchCount'] > 0 )
            {
                $resultNode = $search['SearchResult'][0];
                $object = eZContentObject::fetch( $resultNode->attribute( 'contentobject_id' ) );
            }
            else
            {
                //OpenPALog::warning( var_export( $search, 1 ) );
            }
        }
        
        return $object;
    }
    
    public function compareWithContentObject( eZContentObject $object = null )
    {
        if ( !$object instanceof eZContentObject )
        {
            throw new Exception( 'Oggetto non trovato' );
        }
        if ( $this->metadata['classIdentifier'] !== $object->attribute( 'class_identifier' ) )
        {
            throw new Exception( "L'oggetto con remote id {$object->attribute( 'class_identifier' )} è di classe diversa rispetto all'oggetto remoto" );
        }
        $classTool = new OpenPAClassTools( $object->attribute( 'class_identifier' ) );        
        if ( !$classTool->isValid() )
        {
            throw new Exception( "La classe {$object->attribute( 'class_identifier' )} non ha passato la validazione" );
        }
    }
    
    public function createContentObject( $parentNodeID, $localRemoteIdPrefix = '' )
    {
        if ( eZContentObject::fetchByRemoteID( $this->metadata['objectRemoteId'] ) )
        {
            throw new Exception( "L'oggetto con remote \"{$this->metadata['objectRemoteId']}\" esiste già in questa installazione" );            
        }
        
        $searchEngine = new eZSolr();
        $searchParams = array( 'SearchContentClassID' => $this->metadata['classIdentifier'],
                               'SearchLimit' => 1,
                               'Filter' => array( 'or', 'meta_name_t:' . $this->metadata['objectName'] ),
                               'SearchSubTreeArray' => array( $parentNodeID ) );

        $search = $searchEngine->search( '', $searchParams);
        if ( $search['SearchCount'] > 0 )
        {
            throw new Exception( "Sembra che esista già un oggetto con nome \"{$this->metadata['objectName']}\" in {$parentNodeID}" );              
        }        
        
        $params                     = array();        
        $params['class_identifier'] = $this->metadata['classIdentifier'];
        $params['remote_id']        = $localRemoteIdPrefix . $this->metadata['objectRemoteId'];
        $params['parent_node_id']   = $parentNodeID;
        $params['attributes']       = $this->getAttributesStringArray();
        return eZContentFunctions::createAndPublishObject( $params );
    }
    
    public function updateLocalRemoteId( eZContentObject $object = null, $localRemoteIdPrefix = null )
    {
        if ( $localRemoteIdPrefix !== null )
        {
            $remoteId = $localRemoteIdPrefix . $this->metadata['objectRemoteId'];            
            if ( $object->attribute( 'remote_id' ) != $remoteId )
            {
                $this->compareWithContentObject( $object );
                $object->setAttribute( 'remote_id', $remoteId );
                $object->store();
                return true;
            }            
        }
        return false;
    }
    
    public function updateContentObject( eZContentObject $object = null )
    {
        $this->compareWithContentObject( $object );
        $params = array();        
        $params['attributes'] = $this->getAttributesStringArray();
        $newObject = eZContentFunctions::updateAndPublishObject( $object, $params );
        if ( !$newObject )
        {
            throw new Exception( "Errore sincronizzando l'oggetto" );
        }
        return $newObject;
    }
    
    protected function getAttributesStringArray()
    {
        $attributeList = array();
        foreach( $this->fields as $identifier => $fieldArray )
        {
            switch( $fieldArray['type'] )
            {
                case 'ezxmltext':
                    $attributeList[$identifier] = SQLIContentUtils::getRichContent( $fieldArray['value'] );
                    break;
                case 'ezbinaryfile':
                case 'ezimage':
                    $attributeList[$identifier] = SQLIContentUtils::getRemoteFile( $fieldArray['value'] );
                    break;
                default:
                    $attributeList[$identifier] = $fieldArray['string_value'];
                    break;
            }            
        }
        return $attributeList;
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