<?php
class OpenPAObjectHandler
{
    const FILTER_HALT = 0;
    const FILTER_CONTINUE = 1;

    /**
     * @var OpenPAObjectHandler[]
     */
    protected static $instances = array();
        
    protected $attributeCaches = array();

    /**
     * @var eZContentObject|null
     */
    protected $contentObject;

    /**
     * @var eZContentObjectTreeNode|null
     */
    protected $contentNode;

    /**
     * @var OpenPAObjectHandlerServiceInterface[]
     */
    protected $services = array();

    /**
     * @var OpenPAAttributeHandler[]
     */
    protected $attributesHandlers;
    
    /**
     * @var string[]
     */
    protected $attributesIdentifiers;

    /**
     * @var array
     */
    public $currentPathNodeIds = array();

    /**
     * @var int
     */
    public $currentNodeId = 0;

    /**
     * @var int
     */
    public $currentMainNodeId = 0;

    /**
     * @var int|null
     */
    public $currentObjectId = 0;

    /**
     * @var string
     */
    public $currentClassIdentifier;

    /**
     * @var eZUser
     */
    public $currentUser;

    /**
     * @var string
     */
    public $currentUserHashString;
    
    /**
     * @var OCClassExtraParametersManager
     */
    public $extraParametersManager;

    public static function instanceFromObject( $object = null )
    {
        if ( $object instanceof eZContentObjectTreeNode )
        {
            return self::instanceFromContentObject( $object->attribute( 'object' ), $object );
        }
        elseif ( $object instanceof eZContentObject )
        {
            return self::instanceFromContentObject( $object, $object->attribute( 'main_node' ) );
        }
        elseif ( $object instanceof eZPageBlock )
        {
            return self::blockHandler( $object );
        }
        return new OpenPAObjectHandler();
    }

    public static function instanceFromContentObject( eZContentObject $object = null, eZContentObjectTreeNode $node = null )
    {        
        //@todo caricare la classe estesa specifica per l'oggetto di riferimento
        if ( $object instanceof eZContentObject )
        {
            if ( !isset( self::$instances[$object->attribute('id')] ) )
            {
                self::$instances[$object->attribute('id')] = new OpenPAObjectHandler( $object );
            }
            self::$instances[$object->attribute('id')]->setCurrentNode( $node );
            return self::$instances[$object->attribute('id')];
        }
        return new OpenPAObjectHandler();
    }

    public function setCurrentNode( eZContentObjectTreeNode $node = null )
    {
        if ( $this->contentNode === null && $this->contentObject instanceof eZContentObject )
        {
            if ( $node instanceof eZContentObjectTreeNode )
            {
                $this->contentNode = $node;
                $this->currentNodeId = $this->contentNode->attribute( 'node_id' );
                $this->currentMainNodeId = $this->currentNodeId;
                if ( $this->currentNodeId != $this->contentObject->attribute( 'main_node_id' ) )
                {
                    $this->currentMainNodeId = $this->contentObject->attribute( 'main_node_id' );
                }
            }
            elseif ( $this->contentObject instanceof eZContentObject )
            {
                $mainNode = $this->contentObject->attribute( 'main_node' );
                if ( $mainNode instanceof eZContentObjectTreeNode )
                {
                    $this->contentNode = $mainNode;
                    $this->currentNodeId = $this->contentNode->attribute( 'node_id' );
                    $this->currentMainNodeId = $this->currentNodeId;
                }
            }

            if ( $this->contentNode !== null )
            {
                $pathArray = explode( '/', $this->contentNode->attribute( 'path_string' ) );
                $start = false;
                foreach( $pathArray as $nodeId )
                {
                    
                    $do = true;
                    if ( $nodeId == eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) )
                    {
                        $start = true;
                    }
                    if ( $nodeId == ''
                         || $nodeId == 1                         
                         || $nodeId == eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' )
                         || strpos( eZINI::instance()->variable( 'SiteSettings', 'IndexPage' ), $nodeId ) !== false
                    )
                    {
                        $do = false;                        
                    }
                    if ( $start && $do )
                    {
                        $this->currentPathNodeIds[] = $nodeId;
                    }
                    
                    //if ( $nodeId != ''
                    //     && $nodeId != 1
                    //     && $nodeId != eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' )
                    //     && strpos( eZINI::instance()->variable( 'SiteSettings', 'IndexPage' ), $nodeId ) === false
                    //)
                    //{
                    //    $this->currentPathNodeIds[] = $nodeId;
                    //}
                }
                //eZDebug::writeNotice($this->currentNodeId . ' ' . var_export( $this->currentPathNodeIds,1));
            }
        }        
    }

    public function getContentNode()
    {
        return $this->contentNode;
    }

    public function hasContentNode()
    {
        return $this->contentNode instanceof eZContentObjectTreeNode;
    }

    public function getContentObject()
    {
        return $this->contentObject;
    }

    public function hasContentObject()
    {
        return $this->contentObject instanceof eZContentObject;
    }

    public function hasContent()
    {
        return $this->hasContentObject() && $this->hasContentNode();
    }
    
    public function __get($var)
    {
        if ($var == 'attributesHandlers')
        {
            return $this->getAttributesHandlers();
        }
    }
    
    protected function getAttributesHandlers( $key = null )
    {
        if ($this->attributesHandlers === null && $this->contentObject instanceof eZContentObject)
        {
            $dataMap = $this->contentObject->attribute( 'data_map' );            
            foreach( $dataMap as $identifier => $attribute )
            {
                $this->attributesHandlers[$identifier] = $this->attributeHandler( $attribute, $identifier );                
            }            
        }        
        if ( $key )
            return isset( $this->attributesHandlers[$key] ) ? $this->attributesHandlers[$key] : null;
        return $this->attributesHandlers;
    }
    
    function contentObjectAttributeIdentifiers()
    {
        if ($this->attributesIdentifiers === null && $this->contentObject instanceof eZContentObject)
        {
            $db = eZDB::instance();
            $version = $this->contentObject->CurrentVersion;
            $language = $this->contentObject->CurrentLanguage;
    
            $versionText = "AND ezcontentobject_attribute.version = '$version'";
            $languageText = "AND  ezcontentobject_attribute.language_code = '$language'";        
            
            
            $query = "SELECT ezcontentclass_attribute.identifier as identifier FROM
                        ezcontentobject_attribute, ezcontentclass_attribute, ezcontentobject_version
                      WHERE
                        ezcontentclass_attribute.version = '0' AND
                        ezcontentclass_attribute.id = ezcontentobject_attribute.contentclassattribute_id AND
                        ezcontentobject_version.contentobject_id = '{$this->contentObject->ID}' AND
                        ezcontentobject_version.version = '$version' AND
                        ezcontentobject_attribute.contentobject_id = '{$this->contentObject->ID}' $versionText $languageText                  
                      ORDER BY
                        ezcontentclass_attribute.placement ASC,
                        ezcontentobject_attribute.language_code ASC";
    
            $this->attributesIdentifiers = array();
            $attributeArray = $db->arrayQuery( $query );
            foreach($attributeArray as $row)
            {
                $this->attributesIdentifiers[] = $row['identifier'];
            }
        }
        return $this->attributesIdentifiers;
    }

    protected function __construct( $object = null )
    {
        if ( $object instanceof eZContentObject )
        {
            $this->contentObject = $object;
            $this->currentObjectId = $this->contentObject->attribute( 'id' );
            $this->currentClassIdentifier = $this->contentObject->attribute( 'class_identifier' );                     
            if ( class_exists( 'OCClassExtraParametersManager' ) )
            {
                $this->extraParametersManager = OCClassExtraParametersManager::instance( $this->contentObject->attribute( 'content_class' ) );
            }            
        }        
        $availableServices = OpenPAINI::variable( 'ObjectHandlerServices', 'Services', array() );
        foreach( $availableServices as $serviceId => $className )
        {
            if ( class_exists( $className ) )
            {
                $check = new ReflectionClass( $className );
                if ( $check->isSubclassOf( 'ObjectHandlerServiceBase' ) )
                {
                    $this->services[$serviceId] = new $className;
                    $this->services[$serviceId]->setIdentifier( $serviceId );
                    $this->services[$serviceId]->setContainer( $this );
                }
                else
                {
                    eZDebug::writeError( "Service $serviceId does not extend ObjectHandlerServiceBase", __METHOD__ );
                }
            }
            else
            {
                eZDebug::writeError( "Class $className not found", __METHOD__ );
            }
        }

        $this->currentUser = eZUser::currentUser();
        $this->currentUserHashString = implode( ',' , $this->currentUser->attribute( 'role_id_list' ) ) . implode( ',' , $this->currentUser->attribute( 'limited_assignment_value_list' ) );
    }

    public function attributes()
    {
        return array_merge( array_keys( $this->services ), (array)$this->contentObjectAttributeIdentifiers() );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, array_merge( array_keys( $this->services ), (array)$this->contentObjectAttributeIdentifiers() ) );
    }

    /**
     * @param $key
     *
     * @return OpenPATempletizable
     */
    public function attribute( $key )
    {
        if (!isset( $this->attributeCaches[$key] ))
        {
            if ( isset( $this->services[$key] ) )
            {                
                $this->attributeCaches[$key] = $this->services[$key]->data();
            }
            elseif ( in_array( $key, $this->contentObjectAttributeIdentifiers() ) )
            {                            
                $this->attributeCaches[$key] = $this->getAttributesHandlers( $key );
            }
            else
            {
                eZDebug::writeNotice( "Service or AttributeHandler $key does not exist", __METHOD__ );
                $this->attributeCaches[$key] = false;
            }            
        }
        
        return $this->attributeCaches[$key];
    }

    /**
     * @param $key
     *
     * @return OpenPATempletizable|OpenPAObjectHandlerServiceInterface
     */
    public function service( $key )
    {
        if ( isset( $this->services[$key] ) )
        {                        
            return $this->services[$key]->data();
        }
        eZDebug::writeNotice( "Service $key does not exist", __METHOD__ );
        return false;
    }

    /**
     * @param string $className
     *
     * @return OpenPATempletizable|OpenPAObjectHandlerServiceInterface
     */
    public function serviceByClassName( $className )
    {
        foreach( $this->services as $key => $service )
        {
            if ( get_class( $service ) == $className )
            {
                return $service;
            }
        }
        eZDebug::writeNotice( "Service by $className does not exist", __METHOD__ );
        return false;
    }

    public static function blockHandler( eZPageBlock $block )
    {
        $class = 'OpenPABlockHandler';
        $parameters = array();
        $blockHandlersList = OpenPAINI::variable( 'BlockHandlers', 'Handlers', array() );
        $currentType = $block->attribute( 'type' );
        $currentView = $block->attribute( 'view' );
        foreach( $blockHandlersList as $parameters => $className )
        {
            $parameters = explode( '/', $parameters );
            $type = $parameters[0];
            $view = $parameters[1];
            if ( ( $type == '*' || $type == $currentType )
                 && ( $view == '*' || $view == $currentView ) )
            {
                $class = $className;
            }
        }
        return new $class( $block, $parameters );
    }

    public function attributeHandler( eZContentObjectAttribute $attribute, $identifier = false )
    {
        $class = 'OpenPAAttributeHandler';
        $parameters = array();
        $attributeHandlersList = OpenPAINI::variable( 'AttributeHandlers', 'Handlers', array() );
        $currentType = $attribute->attribute( 'data_type_string' );
        $currentClassIdentifier = $this->currentClassIdentifier;
        $currentAttributeIdentifier = $identifier != false ? $identifier : $attribute->attribute( 'contentclass_attribute_identifier' );
        foreach( $attributeHandlersList as $parameters => $className )
        {
            $parameters = explode( '/', $parameters );
            $type = $parameters[0];
            $classIdentifier = $parameters[1];
            $attributeIdentifier = $parameters[2];
            if ( ( $type == '*' || $type == $currentType )
                 && ( $classIdentifier == '*' || $classIdentifier == $currentClassIdentifier )
                 && ( $attributeIdentifier == '*' || $attributeIdentifier == $currentAttributeIdentifier ) )
            {
                $class = $className;
            }
        }
        return new $class( $attribute, $parameters );
    }

    public function flush( $index = true )
    {
        if ( $this->contentObject instanceof eZContentObject )
        {
            /*
            $eZSolr = eZSearch::getEngine();
            $eZSolr->addObject( $this->contentObject, false );
            $eZSolr->commit();
             */
            if ( $index )
            {
                $this->addPendingIndex();
            }
            eZContentCacheManager::clearContentCacheIfNeeded( $this->currentObjectId );
            $this->contentObject->resetDataMap();
            eZContentObject::clearCache( array( $this->currentObjectId ) );
            unset( self::$instances[$this->currentObjectId] );
        }
    }

    public function filter( $filterIdentifier, $action )
    {
        $result = true;
        foreach( $this->services as $id => $service )
        {            
            $result = $service->filter( $filterIdentifier, $action );            
            if ( $result == self::FILTER_HALT  )
            {
                return false;
            }
        }
        return $result;
    }

    public function addPendingIndex()
    {
        eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '{$this->currentObjectId}' )" );
    }
}
