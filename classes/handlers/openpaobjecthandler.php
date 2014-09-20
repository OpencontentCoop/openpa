<?php
class OpenPAObjectHandler
{
    /**
     * @var OpenPAObjectHandler[]
     */
    protected static $instances = array();

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
    public $attributesHandlers = array();

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

    public static function instanceFromObject( $object = null )
    {
        if ( $object instanceof eZContentObjectTreeNode )
        {
            return self::instanceFromContentObject( $object->attribute( 'object' ), $object );
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
        if ( $this->contentNode === null )
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
                $this->contentNode = $this->contentObject->attribute( 'main_node' );
                $this->currentNodeId = $this->contentNode->attribute( 'node_id' );
                $this->currentMainNodeId = $this->currentNodeId;
            }

            if ( $this->contentNode !== null )
            {
                $pathArray = explode( '/', $this->contentNode->attribute( 'path_string' ) );
                foreach( $pathArray as $nodeId )
                {
                    if ( $nodeId != ''
                         && $nodeId != 1
                         && $nodeId != eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' )
                         && strpos( eZINI::instance()->variable( 'SiteSettings', 'IndexPage' ), $nodeId ) === false
                    )
                    {
                        $this->currentPathNodeIds[] = $nodeId;
                    }
                }
            }
        }
    }

    public function getContentNode()
    {
        return $this->contentNode;
    }

    public function getContentObject()
    {
        return $this->contentObject;
    }

    protected function __construct( $object = null )
    {
        if ( $object instanceof eZContentObject )
        {
            $this->contentObject = $object;
            $this->currentObjectId = $this->contentObject->attribute( 'id' );
            $this->currentClassIdentifier = $this->contentObject->attribute( 'class_identifier' );
            $dataMap = $this->contentObject->attribute( 'data_map' );
            foreach( $dataMap as $identifier => $attribute )
            {
                $this->attributesHandlers[$identifier] = $this->attributeHandler( $attribute );
            }
        }
        $availableServices = OpenPAINI::variable( 'ObjectHandlerServices', 'Services', array() );
        foreach( $availableServices as $serviceId => $className )
        {
            if ( class_exists( $className ) )
            {
                $check = new ReflectionClass( $className );
                if ( $check->isSubclassOf( 'OpenPATempletizable' )
                     && $check->implementsInterface( 'OpenPAObjectHandlerServiceInterface' ))
                {
                    $this->services[$serviceId] = new $className;
                    $this->services[$serviceId]->setIdentifier( $serviceId );
                    $this->services[$serviceId]->setContainer( $this );
                }
                else
                {
                    eZDebug::writeError( "Service $serviceId does not implement OpenPAObjectHandlerServiceInterface and/or does not extend OpenPATempletizable", __METHOD__ );
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
        return array_keys( $this->services );
    }

    public function hasAttribute( $key )
    {
        return in_array( $key, array_keys( $this->services ) );
    }

    /**
     * @param $key
     *
     * @return OpenPATempletizable
     */
    public function attribute( $key )
    {
        if ( $this->hasAttribute( $key ) )
        {
            return $this->services[$key]->data();
        }
        eZDebug::writeNotice( "Attribute $key does not exist", __METHOD__ );
        return false;
    }

    public function attributeHandler( eZContentObjectAttribute $attribute )
    {
        $class = 'OpenPAAttributeHandler';
        $parameters = array();
        $attributeHandlersList = OpenPAINI::variable( 'AttributeHandlers', 'Handlers', array() );
        $currentType = $attribute->attribute( 'data_type_string' );
        $currentClassIdentifier = $this->currentClassIdentifier;
        $currentAttributeIdentifier = $attribute->attribute( 'contentclass_attribute_identifier' );
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


    public function flush()
    {
        if ( $this->contentObject instanceof eZContentObject )
        {
            $objectID = $this->contentObject->attribute( 'id' );

            /** @var eZSolr $eZSolr */
            $eZSolr = eZSearch::getEngine();
            $eZSolr->addObject( $this->contentObject, false );
            $eZSolr->commit();
            
            eZContentCacheManager::clearContentCacheIfNeeded( $objectID );
            
            $this->contentObject->resetDataMap();
            eZContentObject::clearCache( array( $objectID ) );            
        }
    }
}