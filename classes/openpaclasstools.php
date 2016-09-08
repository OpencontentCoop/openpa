<?php

class OpenPAClassTools
{
    const ACTION_UPDATE_CLASS = 'openpa_update_class';

    const NOTICE = 0;
    const WARNING = 1;
    const ERROR = 2;

    public static $remoteUrl = 'http://openpa.opencontent.it/openpa/classdefinition/';

    public $EditLanguage = 'ita-IT';

    protected $id;

    protected $identifier;

    /**
     * @var eZContentClass
     */
    protected $currentClass;

    /**
     * @var eZContentClassAttribute[]
     */
    protected $currentAttributes = array();

    protected $options;

    protected $remoteClass;

    protected $notifications = array();

    protected $data;

    /** @var eZContentClassAttribute[] */
    protected $extraContentObjectAttributes = array();

    protected $extraContentObjectAttributesDetails = array();

    protected $properties = array(
        'contentobject_name'            => 'ContentObjectName',
        'serialized_name_list'          => 'SerializedNameList',
        'serialized_description_list'   => 'SerializedDescriptionList',
        'url_alias_name'                => 'URLAliasName',
        'always_available'              => 'AlwaysAvailable',
        'sort_field'                    => 'SortField',
        'sort_order'                    => 'SortOrder',
        'is_container'                  => 'IsContainer'
    );

    protected $fields = array(
        'serialized_description_list'   => 'SerializedDescriptionList',
        'serialized_name_list'          => 'SerializedNameList',
        'data_type_string'              => 'DataTypeString',
        'placement'                     => 'Position',
        'is_searchable'                 => 'IsSearchable',
        'is_required'                   => 'IsRequired',
        'is_information_collector'      => 'IsInformationCollector',
        'can_translate'                 => 'CanTranslate',
        'data_int1'                     => 'DataInt1',
        'data_int2'                     => 'DataInt2',
        'data_int3'                     => 'DataInt3',
        'data_int4'                     => 'DataInt4',
        'data_float1'                   => 'DataFloat1',
        'data_float2'                   => 'DataFloat2',
        'data_float3'                   => 'DataFloat3',
        'data_float4'                   => 'DataFloat4',
        'data_text1'                    => 'DataText1',
        'data_text2'                    => 'DataText2',
        'data_text3'                    => 'DataText3',
        'data_text4'                    => 'DataText4',
        'data_text5'                    => 'DataText5',
        'category'                      => 'Category'
    );

    protected $propertiesNotificationLevel = array(
        'contentobject_name'            => self::NOTICE,
        'serialized_name_list'          => self::NOTICE,
        'serialized_description_list'   => self::NOTICE,
        'url_alias_name'                => self::NOTICE,
        'always_available'              => self::WARNING,
        'sort_field'                    => self::NOTICE,
        'sort_order'                    => self::NOTICE,
        'is_container'                  => self::WARNING
    );

    protected $fieldsNotificationLevel = array(
        'serialized_name_list'          => self::NOTICE,
        'serialized_description_list'   => self::NOTICE,
        'data_type_string'              => self::ERROR,
        'placement'                     => self::NOTICE,
        'is_searchable'                 => self::WARNING,
        'is_required'                   => self::WARNING,
        'is_information_collector'      => self::WARNING,
        'can_translate'                 => self::NOTICE,
        'data_int1'                     => self::NOTICE,
        'data_int2'                     => self::NOTICE,
        'data_int3'                     => self::NOTICE,
        'data_int4'                     => self::NOTICE,
        'data_float1'                   => self::NOTICE,
        'data_float2'                   => self::NOTICE,
        'data_float3'                   => self::NOTICE,
        'data_float4'                   => self::NOTICE,
        'data_text1'                    => self::NOTICE,
        'data_text2'                    => self::NOTICE,
        'data_text3'                    => self::NOTICE,
        'data_text4'                    => self::NOTICE,
        'data_text5'                    => self::NOTICE,
        'category'                      => self::NOTICE
    );

    /**
     * OpenPAClassTools constructor.
     *
     * @param int|string $id
     * @param bool $createIfNotExists
     * @param array $options
     *
     * @throws Exception
     */
    function __construct( $id, $createIfNotExists = false, $options = array() )
    {
        $this->notifications = array( self::ERROR => array(),
                                      self::WARNING => array(),
                                      self::NOTICE => array() );
        $class = eZContentClass::fetch( intval( $id ) );
        $this->options = $options;
        $this->data = new stdClass();
        if ( !$class instanceof eZContentClass )
        {
            $class = eZContentClass::fetchByIdentifier( $id );
        }
        if ( !$class instanceof eZContentClass )
        {
            if ( !$createIfNotExists )
            {
                throw new Exception( "Classe $id non trovata" );
            }
            else
            {
                if ( !is_numeric( $id ) )
                {
                    OpenPALog::warning( "Creazione della classe $id" );
                    $class = $this->createNew( $id );
                    if ( !$class instanceof eZContentClass )
                    {
                        throw new Exception( "Fallita la creazione della classe $id" );
                    }
                }
                else
                {
                    throw new Exception( "Per creare automaticamente una nuova classe è necessario fornire l'identificativo e non l'id numerico" );
                }
            }
        }
        $this->currentClass = $class;
        $this->id = $this->currentClass->attribute( 'id' );
        $this->identifier = $this->currentClass->attribute( 'identifier' );
    }

    public function isValid()
    {
        $this->compare();
        if ( count( $this->data->missingAttributes ) > 0 )
        {
            return false;
        }
        if ( $this->getData()->hasError )
        {
            return false;
        }
        return true;
    }

    public function getData()
    {
        $this->data->hasError = false;
        $this->data->errors = array();
        if ( !empty( $this->notifications[self::ERROR] ) )
        {
            $this->data->hasError = true;
            $this->data->errors = $this->notifications[self::ERROR];
        }
        $this->data->warnings = array();
        $this->data->hasWarning = false;
        if ( !empty( $this->notifications[self::WARNING] ) )
        {
            $this->data->hasWarning = true;
            $this->data->warnings = $this->notifications[self::WARNING];
        }
        $this->data->notices = array();
        $this->data->hasNotice = false;
        if ( !empty( $this->notifications[self::NOTICE] ) )
        {
            $this->data->hasNotice = true;
            $this->data->notices = $this->notifications[self::NOTICE];
        }
        $this->data->hasDiffAttributes = false;
        if ( !empty( $this->data->diffAttributes ) )
        {
            $this->data->hasDiffAttributes = true;
        }
        $this->data->hasDiffProperties = false;
        if ( !empty( $this->data->diffProperties ) )
        {
            $this->data->hasDiffProperties = true;
        }
        $this->data->hasMissingAttributes = false;
        if ( !empty( $this->data->missingAttributes ) )
        {
            $this->data->hasMissingAttributes = true;
        }
        $this->data->hasExtraAttributes = false;
        $this->data->extraDetails = array();
        if ( !empty( $this->data->extraAttributes ) )
        {
            $this->data->hasExtraAttributes = true;
            $this->data->extraDetails = $this->extraContentObjectAttributesDetails;
        }

        return $this->data;
    }

    public function compare()
    {
        $this->compareProperties();
        $this->compareAttributes();
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function getRemote()
    {
        if ( $this->remoteClass == null )
        {
            $this->remoteClass = self::fetchRemoteByIdentifier( $this->identifier );
        }
        return $this->remoteClass;
    }

    /**
     * @return eZContentClass
     */
    public function getLocale()
    {
        return $this->currentClass;
    }

    /**
     * @param bool $force
     * @param bool $removeExtras
     *
     * @throws Exception
     */
    public function sync( $force = false, $removeExtras = false )
    {
        $this->preSync();

        if ( $this->getData()->hasError && !$force )
        {
            throw new Exception( "La classe contiene campi che ne impediscono la sincronizzazione automatica" );
        }

        $remote = $this->getRemote();
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }

        if ( $force && $this->getData()->hasError )
        {
            foreach( $this->getData()->errors as $identifier => $value )
            {
                if ( !$this->currentAttributes[$identifier] instanceof eZContentClassAttribute )
                {
                    throw new Exception( 'Errore forzando la sincronizzazione' );
                }
                foreach( $remote->DataMap[0] as $originalAttribute )
                {
                    if ( $originalAttribute->Identifier == $identifier )
                    {
                        ezpEvent::getInstance()->notify( 'openpa/switch_class_attribute', array( $this->currentAttributes[$identifier], $originalAttribute ) );
                        if ( $value == 'data_type_string' )
                        {
                            $this->currentAttributes[$identifier]->setAttribute( 'data_type_string', $originalAttribute->DataTypeString );
                            $this->currentAttributes[$identifier]->store();
                            $this->changeContentObjectAttributeDataTypeString( $this->currentAttributes[$identifier], $originalAttribute->DataTypeString );
                            unset( $this->notifications[self::ERROR][$originalAttribute->Identifier][$value] );
                        }
                        else
                        {
                            $this->data->missingAttributes[] = $originalAttribute;
                            $this->currentClass->removeAttributes( array( $this->currentAttributes[$identifier] ) );
                            unset( $this->currentAttributes[$identifier] );
                        }
                        break;
                    }
                }
            }
        }

        $attributes = array();

        foreach( $this->properties as $identifier => $remoteProperty )
        {
            if ( !$this->propertyIsEqual( $identifier, $remote->{ $remoteProperty }, $this->currentClass->attribute( $identifier ) ) )
            {
                $this->currentClass->setAttribute( $identifier,  $remote->{ $remoteProperty } );
                if ( $identifier == 'serialized_name_list' )
                {
                    $nameList = new eZContentClassNameList();
                    $nameList->initFromSerializedList( $remote->{ $remoteProperty } );
                    $this->currentClass->NameList = $nameList;
                }
                elseif ( $identifier == 'serialized_description_list' )
                {
                    $descriptionList = new eZSerializedObjectNameList();
                    $descriptionList->initFromSerializedList( $remote->{ $remoteProperty } );
                    $this->currentClass->DescriptionList = $descriptionList;
                }
            }
        }

        foreach( $this->getData()->missingAttributes as $originalAttribute )
        {
            $add = $this->addAttribute( $originalAttribute );
            if ( $add )
            {
                $attributes[] = $add;
            }
        }
        foreach( $remote->DataMap[0] as $originalAttribute )
        {
            if ( isset( $this->currentAttributes[$originalAttribute->Identifier] ) )
            {
                $modified = $this->syncAttribute( $originalAttribute, $this->currentAttributes[$originalAttribute->Identifier] );
                if ( $modified )
                {
                    $attributes[] = $modified;
                }
            }
        }
        if ( !$removeExtras )
        {
            foreach( $this->extraContentObjectAttributes as $extra )
            {
                $extra->setAttribute( 'placement', count( $attributes ) + 1 );
                $attributes[] = $extra;
            }
        }

        $this->storeClass( $attributes );

        $this->syncGroups();

    }

    /**
     * @param string $identifier
     *
     * @return eZContentClass
     * @throws Exception
     */
    protected function createNew( $identifier )
    {
        $remote = self::fetchRemoteByIdentifier( $identifier );
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        $this->syncAllGroups( $remote );

        $classGroup = false;
        foreach ( $remote->InGroups as $group )
        {
            $classGroup = eZContentClassGroup::fetchByName( $group->GroupName );
            if ( $classGroup instanceof eZContentClassGroup )
            {
                break;
            }
        }
        if ( !$classGroup instanceof eZContentClassGroup )
        {
            throw new Exception( 'Errore creando la nuova classe' );
        }
        $db = eZDB::instance();
        $db->begin();
        $options = array(
            'serialized_name_list' => $remote->SerializedNameList,
            'serialized_description_list' => $remote->SerializedDescriptionList
        );
        $user = eZUser::currentUser();
        $userID = $user->attribute( 'contentobject_id' );
        $class = eZContentClass::create( $userID, $options, $this->EditLanguage );
        $class->setName( 'New automatic', $this->EditLanguage );
        $class->store();

        $editLanguageID = eZContentLanguage::idByLocale( $this->EditLanguage );
        $class->setAlwaysAvailableLanguageID( $editLanguageID );
        $ClassID = $class->attribute( 'id' );
        $ClassVersion = $class->attribute( 'version' );

        $ingroup = eZContentClassClassGroup::create( $ClassID,
            $ClassVersion,
            $classGroup->attribute( 'id' ),
            $classGroup->attribute( 'name' ) );
        $ingroup->store();
        $class->setAttribute( 'identifier',  $identifier );
        foreach( $this->properties as $identifier => $remoteProperty )
        {
            $class->setAttribute( $identifier,  $remote->{ $remoteProperty } );
        }
        $class->storeVersioned( array(), eZContentClass::VERSION_STATUS_DEFINED );
        $db->commit();
        return $class;
    }

    /**
     * @param string $identifier
     *
     * @return stdClass
     * @throws Exception
     */
    protected static function fetchRemoteByIdentifier( $identifier )
    {
        $remoteUrl = OpenPAINI::variable( 'NetworkSettings', 'PrototypeUrl', self::$remoteUrl );
        $currentUrl = 'http://' . eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $originalRepositoryUrl = $remoteUrl . $identifier;

        $repository = parse_url( $originalRepositoryUrl );
        $locale = parse_url( $currentUrl );

        if ( $repository['host'] != $locale['host'] )
        {
            $original = json_decode( eZHTTPTool::getDataByURL( $originalRepositoryUrl ) );
            if ( isset( $original->error ) )
            {
                throw new Exception( $original->error );
            }
            return $original;
        }
        throw new Exception( "Server remoto e server locale coincidono" );
    }

    /**
     * @param stdClass $remote
     *
     * @throws Exception
     */
    protected function compareProperties( $remote = null )
    {
        if ( $remote === null )
        {
            $remote = $this->getRemote();
        }
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        $locale = $this->currentClass;
        $this->data->diffProperties = array();

        foreach ( $this->properties as $localeIdentifier => $remoteProperty )
        {
            if (!$this->propertyIsEqual($localeIdentifier, $remote->{$remoteProperty}, $locale->attribute($localeIdentifier)))
            {
                $this->notifications[$this->propertiesNotificationLevel[$localeIdentifier]]['properties'][$localeIdentifier] = $remoteProperty;
                $this->data->diffProperties[] =  array(
                    'field_name' => $localeIdentifier,
                    'locale_value' => $this->formatValue( $localeIdentifier, $locale->attribute( $localeIdentifier ) ),
                    'remote_value' => $this->formatValue( $localeIdentifier, $remote->{ $remoteProperty } )
                );
            }
        }

        /** @var eZContentClassClassGroup[] $localGroups */
        $localGroups = $locale->fetchGroupList();
        $localGroupsNames = array();
        $remoteGroupsNames = array();
        foreach ( $localGroups as $group )
        {
            $localGroupsNames[] = $group->attribute( 'group_name' );
        }
        foreach ( $remote->InGroups as $group )
        {
            $remoteGroupsNames[] = $group->GroupName;
        }
        $diff = array_diff( $remoteGroupsNames, $localGroupsNames );
        if ( !empty( $diff ) )
        {
            $this->data->diffProperties[] =  array(
                'field_name' => 'class_group',
                'locale_value' => implode( ', ', $localGroupsNames ),
                'remote_value' => implode( ', ', $remoteGroupsNames )
            );
        }
    }

    /**
     * @param string $propertyIdentifier
     * @param string $remoteProperty
     * @param string $localeProperty
     *
     * @return bool
     */
    protected function propertyIsEqual( $propertyIdentifier, $remoteProperty, $localeProperty )
    {
        if ($propertyIdentifier == 'serialized_description_list'){
            if (($remoteProperty == 'a:0:{}' && $localeProperty == null)
                || ($localeProperty == 'a:0:{}' && $remoteProperty == null) ){
                return true;
            }
        }
        return $remoteProperty == $localeProperty;
    }

    /**
     * @param stdClass $remote
     *
     * @throws Exception
     */
    protected function compareAttributes( $remote = null )
    {
        $missingInLocale = array();
        if ( $remote === null )
        {
            $remote = $this->getRemote();
        }
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        $locale = $this->currentClass;
        $localeDataMap = $locale->attribute( 'data_map' );
        foreach( $remote->DataMap[0] as $originalAttribute )
        {
            if ( !isset( $localeDataMap[$originalAttribute->Identifier] ) )
            {
                $missingInLocale[$originalAttribute->Identifier] = $originalAttribute;
            }
        }

        $existInOriginal = array();
        $missingInOriginal = array();
        $this->data->diffAttributes = array();
        if ( !empty( $localeDataMap ) )
        {
            foreach( $localeDataMap as $identifier => $attribute )
            {
                foreach( $remote->DataMap[0] as $originalAttribute )
                {
                    if ( $originalAttribute->Identifier == $identifier )
                    {
                        $existInOriginal[] = $identifier;
                        $this->compareAttribute( $originalAttribute );
                        break;
                    }
                }
            }
            foreach( $localeDataMap as $identifier => $attribute )
            {
                if ( !in_array( $identifier, $existInOriginal ) )
                {
                    $missingInOriginal[] = json_decode( json_encode( $attribute ) );
                    $this->extraContentObjectAttributes[] = $attribute;
                    /** @var eZContentObjectAttribute[] $contentAttributes */
                    $contentAttributes = array(); //eZContentObjectAttribute::fetchSameClassAttributeIDList( $attribute->attribute( 'id' ), true );
                    $contents = array();
                    foreach( $contentAttributes as $contentAttribute )
                    {
                        if ( $contentAttribute->attribute( 'has_content' ) )
                        {
                            $contents[$contentAttribute->attribute( 'id' )] = $contentAttribute->attribute( 'contentobject_id' );
                        }
                    }
                    $this->extraContentObjectAttributesDetails[$identifier] = array( 'count' => count( $contents ),
                                                                                     'objects' => $contents );

                }
            }
        }

        $this->data->missingAttributes = $missingInLocale;
        $this->data->extraAttributes = $missingInOriginal;
    }

    /**
     * @param stdClass $originalAttribute
     */
    protected function compareAttribute( $originalAttribute )
    {
        $class = $this->currentClass;
        $localeAttribute = $class->fetchAttributeByIdentifier( $originalAttribute->Identifier );
        if( $localeAttribute instanceof eZContentClassAttribute )
        {
            $id = $localeAttribute->attribute( 'identifier' );
            $this->data->diffAttributes[$id] = array();
            ezpEvent::getInstance()->notify( 'openpa/pre_compare_class_attribute', array( $localeAttribute, $originalAttribute ) );

            foreach( $this->fields as $localeIdentifier => $remoteProperty )
            {
                if ( !$this->propertyIsEqual($localeIdentifier, $localeAttribute->attribute( $localeIdentifier ), $originalAttribute->{ $remoteProperty } ))
                {
                    $this->notifications[$this->fieldsNotificationLevel[$localeIdentifier]][$id][$localeIdentifier] = $remoteProperty;
                    $detail = false;
                    /** @var eZContentObjectAttribute[] $contentAttributes */
                    $contentAttributes = array(); //eZContentObjectAttribute::fetchSameClassAttributeIDList( $localeAttribute->attribute( 'id' ), true );
                    $contents = array();
                    foreach( $contentAttributes as $contentAttribute )
                    {
                        if ( $contentAttribute->attribute( 'has_content' ) )
                        {
                            $contents[$contentAttribute->attribute( 'id' )] = $contentAttribute->attribute( 'contentobject_id' );
                        }
                    }
                    $detail = array( 'count' => count( $contents ),
                                     'objects' => $contents );
                    $this->data->diffAttributes[$id][] = array(
                        'field_name' => $localeIdentifier,
                        'locale_value' =>  $this->formatValue( $localeIdentifier, $localeAttribute->attribute( $localeIdentifier ) ),
                        'remote_value' =>  $this->formatValue( $localeIdentifier, $originalAttribute->{ $remoteProperty } ),
                        'detail' => $detail
                    );
                }
            }
            if ( empty( $this->data->diffAttributes[$id] ) )
            {
                unset( $this->data->diffAttributes[$id] );
            }

            ezpEvent::getInstance()->notify( 'openpa/post_compare_class_attribute', array( $localeAttribute, $originalAttribute ) );
        }
    }

    private function formatValue( $identifier, $value )
    {
        if (strpos( $value, '<?xml' ) !== false )
        {
            $xml = simplexml_load_string( $value, 'SimpleXMLElement', LIBXML_NOCDATA );
            $array = json_decode( json_encode( $xml ), true );
            $value = $this->arrayToList( $array );
        }
        if (strpos( $value, 'a:' ) !== false )
        {
            $value = $this->arrayToList( unserialize($value) );
        }
        return $value;
    }

    private function arrayToList($element)
    {
        $data = "<ul style='text-align:left;list-style: outside none none;padding-left:5px;margin:0'>";
        foreach ( $element as $key => $value )
        {
            $data .= "<li style='background:none'>";
            $data .= is_numeric( $key ) ? '' : $key . ': ';
            $data .= is_array( $value ) ? $this->arrayToList( $value ) : $value;
            $data .= "</li>";
        }
        $data .= "</ul>";
        return $data;
    }

    /**
     * @param stdClass $originalAttribute
     * @param eZContentClassAttribute $localeAttribute
     *
     * @return bool
     */
    protected function syncAttribute( $originalAttribute, $localeAttribute )
    {
        if( $localeAttribute instanceof eZContentClassAttribute )
        {
            if ( array_key_exists( $originalAttribute->Identifier, $this->notifications[self::ERROR] ) )
            {
                return $localeAttribute;
            }
            $isModified = false;
            ezpEvent::getInstance()->notify( 'openpa/pre_sync_class_attribute', array( $localeAttribute, $originalAttribute ) );

            foreach( $this->fields as $localeIdentifier => $remoteProperty )
            {
                if ( !$this->propertyIsEqual($localeIdentifier, $localeAttribute->attribute( $localeIdentifier ), $originalAttribute->{ $remoteProperty } ))
                {
                    $localeAttribute->setAttribute( $localeIdentifier, $originalAttribute->{ $remoteProperty } );
                    if ( $localeIdentifier == 'serialized_name_list' )
                    {
                        $nameList = new eZSerializedObjectNameList();
                        $nameList->initFromSerializedList(  $originalAttribute->SerializedNameList );
                        $localeAttribute->NameList = $nameList;
                    }
                    elseif ( $localeIdentifier == 'serialized_description_list' )
                    {
                        $descriptionList = new eZSerializedObjectNameList();
                        $descriptionList->initFromSerializedList( $originalAttribute->SerializedDescriptionList );
                        $localeAttribute->DescriptionList = $descriptionList;
                    }
                    $isModified = true;
                }
            }

            $localeAttribute->store();

            ezpEvent::getInstance()->notify( 'openpa/post_sync_class_attribute', array( $localeAttribute, $originalAttribute ) );

            return $localeAttribute;
        }
        return false;
    }

    /**
     * @param stdClass $originalAttribute
     *
     * @return bool|eZContentClassAttribute
     */
    protected function addAttribute( $originalAttribute )
    {
        $class = $this->currentClass;
        $ClassID = $class->ID;
        $localeAttributes = $class->fetchAttributes();
        $placement = count( $localeAttributes );

        if( !$class->fetchAttributeByIdentifier( $originalAttribute->Identifier ) )
        {
            $localeAttribute = eZContentClassAttribute::create(
                $class->attribute( 'id' ),
                $originalAttribute->DataTypeString,
                array(  'version' => eZContentClass::VERSION_STATUS_TEMPORARY,
                        'identifier' => $originalAttribute->Identifier,
                        'serialized_name_list' => $originalAttribute->SerializedNameList,
                        'serialized_description_list' => $originalAttribute->SerializedDescriptionList,
                        'category' => $originalAttribute->Category,
                        'serialized_data_text' => $originalAttribute->SerializedDataText,
                        'is_required' => $originalAttribute->IsRequired,
                        'is_searchable' => $originalAttribute->IsSearchable,
                        'is_information_collector' => $originalAttribute->IsInformationCollector,
                        'can_translate' => $originalAttribute->CanTranslate,
                        'placement' => ++$placement ),
                $this->EditLanguage
            );
            foreach( $this->fields as $localeIdentifier => $remoteProperty )
            {
                $localeAttribute->setAttribute( $localeIdentifier, $originalAttribute->{ $remoteProperty } );
            }
            $store = $localeAttribute->store();
            return $localeAttribute;
        }
        return false;
    }

    /**
     * @param stdClass|null $remote
     *
     * @throws Exception
     */
    protected function syncAllGroups( stdClass $remote = null)
    {
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        $remoteGroups = $remote->AllGroups;
        foreach( $remoteGroups as $group )
        {
            $name = $group->Name;
            if ( eZContentClassGroup::fetchByName( $name ) == null )
            {
                $user = eZUser::currentUser();
                $userID = $user->attribute( "contentobject_id" );
                $classgroup = eZContentClassGroup::create( $userID );
                $classgroup->setAttribute( "name", $name );
                $classgroup->store();
            }
        }
    }

    protected function syncGroups()
    {
        $remote = $this->getRemote();
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        $this->syncAllGroups( $remote );
        $locale = $this->currentClass;
        /** @var eZContentClassClassGroup[] $localGroups */
        $localGroups = $locale->fetchGroupList();
        $localGroupsNames = array();
        $remoteGroupsNames = array();
        foreach ( $localGroups as $group )
        {
            $classGroup = eZContentClassGroup::fetchByName( $group->attribute( 'group_name' ) );
            if ( $classGroup )
            {
                eZContentClassClassGroup::removeGroup( $this->currentClass->attribute( 'id' ),
                    $this->currentClass->attribute( 'version' ),
                    $classGroup->attribute( 'id' ) );
            }
        }
        foreach ( $remote->InGroups as $group )
        {
            $classGroup = eZContentClassGroup::fetchByName( $group->GroupName );
            if ( $classGroup )
            {
                $ingroup = eZContentClassClassGroup::create( $this->currentClass->attribute( 'id' ),
                    $this->currentClass->attribute( 'version' ),
                    $classGroup->attribute( 'id' ),
                    $classGroup->attribute( 'name' ) );
                $ingroup->store();
            }
        }
        //$groups = $this->currentClass->attribute( 'ingroup_list' );
        //if ( count( $groups ) == 0 )
        //{
        //    //@todo
        //}
    }

    /**
     * @param eZContentClassAttribute $localeAttribute
     * @param string $newDataTypeString
     */
    protected function changeContentObjectAttributeDataTypeString( $localeAttribute, $newDataTypeString )
    {
        $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $localeAttribute->attribute( 'id' ), true );
        foreach( $contentAttributes as $attribute )
        {
            $attribute->setAttribute( 'data_type_string', $newDataTypeString );
            $attribute->store();
        }
    }

    /**
     * @param string[] $destinationClassIdentifiers
     *
     * @throws Exception
     */
    public static function installClasses( $destinationClassIdentifiers )
    {
        $data = array();
        foreach ( $destinationClassIdentifiers as $destinationClassIdentifier  )
        {
            $destinationClass = eZContentClass::fetchByIdentifier( $destinationClassIdentifier );
            if ( !$destinationClass instanceof eZContentClass )
            {
                //throw new Exception( "Classe $destinationClassId non trovata" );
                $tool = new OpenPAClassTools( $destinationClassIdentifier, true );
                $tool->compare();
                $tool->sync();
                $destinationClass = eZContentClass::fetchByIdentifier( $destinationClassIdentifier );
            }

            if ( !$destinationClass instanceof eZContentClass )
            {
                throw new Exception( "Classe $destinationClassIdentifier non trovata" );
            }
            else
            {
                $data[$destinationClassIdentifier] = $destinationClass;
            }
        }
    }

    private function storeClass( $attributes )
    {
        $this->currentClass->store( $attributes );
        $db = eZDB::instance();
        $db->begin();
        $unorderedParameters = array( 'Language' => $this->EditLanguage );
        if ( eZContentObject::fetchSameClassListCount( $this->id ) > 0 )
        {
            eZExtension::getHandlerClass( new ezpExtensionOptions( array( 'iniFile' => 'site.ini',
                                                                          'iniSection'   => 'ContentSettings',
                                                                          'iniVariable'  => 'ContentClassEditHandler' ) ) )
                       ->store( $this->currentClass, $attributes, $unorderedParameters );
        }
        else
        {
            $unorderedParameters['ScheduledScriptID'] = 0;
            $this->currentClass->storeVersioned( $attributes, eZContentClass::VERSION_STATUS_DEFINED );
        }

        $db->commit();

        $this->postSync();
    }

    public function syncSingleProperty( $identifier )
    {
        if ( isset( $this->properties[$identifier] ) )
        {
            if ( $this->remoteClass === null )
            {
                throw new Exception( "Classe remota non trovata" );
            }

            $remoteProperty = $this->properties[$identifier];
            if ( !$this->propertyIsEqual( $identifier, $this->remoteClass->{ $remoteProperty }, $this->currentClass->attribute( $identifier ) ) )
            {
                $this->preSync();

                $this->currentClass->setAttribute( $identifier,  $this->remoteClass->{ $remoteProperty } );
                if ( $identifier == 'serialized_name_list' )
                {
                    $nameList = new eZContentClassNameList();
                    $nameList->initFromSerializedList( $this->remoteClass->{ $remoteProperty } );
                    $this->currentClass->NameList = $nameList;
                }
                elseif ( $identifier == 'serialized_description_list' )
                {
                    $descriptionList = new eZSerializedObjectNameList();
                    $descriptionList->initFromSerializedList( $this->remoteClass->{ $remoteProperty } );
                    $this->currentClass->DescriptionList = $descriptionList;
                }

                $this->storeClass( $this->currentAttributes );
            }
        }
    }

    public function syncSingleAttribute( $fullIdentifier )
    {
        list( $identifier, $property ) = explode( '/', $fullIdentifier );

        if ( $property == 'placement' || $property == 'data_type_string' )
        {
            throw new Exception('Funzionalità non ancora disponibile');
        }

        $originalAttribute = false;

        foreach( $this->remoteClass->DataMap[0] as $attribute )
        {
            if ( $attribute->Identifier == $identifier )
            {
                /** @var stdClass $originalAttribute */
                $originalAttribute = $attribute;
                break;
            }
        }

        $remoteProperty = isset( $this->fields[$property] ) ? $this->fields[$property] : null;

        if ( $originalAttribute && $remoteProperty )
        {
            $this->preSync();

            $attributes = array();

            foreach( $this->currentAttributes as $attribute )
            {
                if ( $attribute->attribute('identifier') == $identifier )
                {
                    if ( !$this->propertyIsEqual($property, $attribute->attribute( $property ), $originalAttribute->{ $remoteProperty } ))
                    {
                        $attribute->setAttribute( $property, $originalAttribute->{ $remoteProperty } );
                        if ( $property == 'serialized_name_list' )
                        {
                            $nameList = new eZSerializedObjectNameList();
                            $nameList->initFromSerializedList(  $originalAttribute->SerializedNameList );
                            $attribute->NameList = $nameList;
                        }
                        elseif ( $property == 'serialized_description_list' )
                        {
                            $descriptionList = new eZSerializedObjectNameList();
                            $descriptionList->initFromSerializedList( $originalAttribute->SerializedDescriptionList );
                            $attribute->DescriptionList = $descriptionList;
                        }
                        $attribute->store();
                    }
                }
                $attributes[] = $attribute;
            }

            $this->storeClass( $attributes );
        }
        else
        {
            throw new Exception( "Attributo $fullIdentifier non trovato" );
        }
    }

    public function removeSingleAttribute( $identifier )
    {
        $this->preSync();

        $attributes = array();

        foreach( $this->currentAttributes as $attribute )
        {
            if ( $attribute->attribute('identifier') != $identifier )
            {
                $attributes[] = $attribute;
            }
        }
        $this->storeClass( $attributes );
    }

    public function addSingleAttribute( $identifier )
    {
        foreach( $this->getData()->missingAttributes as $originalAttributeIdentifier => $originalAttribute )
        {
            if ( $originalAttributeIdentifier == $identifier )
            {
                $this->preSync();

                $attributes = array();
                foreach( $this->currentAttributes as $attribute )
                {
                    if ( $attribute->attribute('identifier') == $identifier )
                    {
                        throw new Exception( "L'attributo $identifier è già presente nella classe" );
                    }
                    $attributes[] = $attribute;
                }
                $attributes[] = $this->addAttribute( $originalAttribute );

                $this->storeClass( $attributes );
            }
            break;
        }
    }

    protected function preSync()
    {
        $this->saveBackup();
        $modified = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_MODIFIED );
        if ( is_object( $modified ) )
        {
            throw new Exception( "Classe bloccata in modifica" );
        }
        $temporary = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_TEMPORARY );

        if ( !is_object( $temporary ) or $temporary->attribute( 'id' ) == null )
        {
            $temporary = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_DEFINED );
            if( $temporary === null ) // Class does not exist
            {
                throw new Exception( "La classe non esiste" );
            }
            /** @var eZContentClassClassGroup[] $classGroups */
            $classGroups = eZContentClassClassGroup::fetchGroupList( $this->id, eZContentClass::VERSION_STATUS_DEFINED );
            foreach ( $classGroups as $classGroup )
            {
                $groupID = $classGroup->attribute( 'group_id' );
                $groupName = $classGroup->attribute( 'group_name' );
                $ingroup = eZContentClassClassGroup::create( $this->id, eZContentClass::VERSION_STATUS_TEMPORARY, $groupID, $groupName );
                $ingroup->store();
            }
            if ( count( $classGroups ) > 0 )
            {
                $mainGroupID = $classGroups[0]->attribute( 'group_id' );
                $mainGroupName = $classGroups[0]->attribute( 'group_name' );
            }
        }
        else
        {
            $user = eZUser::currentUser();
            $contentIni = eZINI::instance( 'content.ini' );
            $timeOut = $contentIni->variable( 'ClassSettings', 'DraftTimeout' );

            /** @var eZContentClassClassGroup[] $groupList */
            $groupList = $temporary->fetchGroupList();
            if ( count( $groupList ) > 0 )
            {
                $mainGroupID = $groupList[0]->attribute( 'group_id' );
                $mainGroupName = $groupList[0]->attribute( 'group_name' );
            }

            if ( $temporary->attribute( 'modifier_id' ) != $user->attribute( 'contentobject_id' ) &&
                 $temporary->attribute( 'modified' ) + $timeOut > time() )
            {
                throw new Exception( "Modifica alla classe non permessa" );
            }
        }

        $this->compare();

        $this->currentAttributes = array();
        /** @var eZContentClassAttribute[] $currentClassAttributes */
        $currentClassAttributes = $this->currentClass->fetchAttributes();
        foreach( $currentClassAttributes as $attribute )
        {
            $attribute->setAttribute( 'version', eZContentClass::VERSION_STATUS_TEMPORARY );
            $this->currentAttributes[$attribute->attribute('identifier')] = $attribute;
        }
        $this->currentClass->setAttribute( 'version', eZContentClass::VERSION_STATUS_TEMPORARY );
    }

    protected function saveBackup()
    {
        $result = $this->getLocale();
        $result->attribute( 'data_map' );
        $result->fetchGroupList();
        $result->fetchAllGroups();
        $data = json_encode( $result );

        $fileName = $result->attribute( 'identifier' ) . '.' . time() . '.json';
        $filePath = eZDir::path( array( eZSys::storageDirectory(), 'openpa_class_backup', $fileName ) );
        $handler = eZClusterFileHandler::instance();
        $handler->fileStoreContents($filePath, $data);
    }

    protected function postSync()
    {
        $pendingItem = eZPendingActions::fetchObject( eZPendingActions::definition(), null, array(
            'action' => self::ACTION_UPDATE_CLASS,
            'param' => $this->currentClass->attribute( 'identifier' )
        ) );

        if ($pendingItem instanceof eZPendingActions)
        {
            $pendingItem->setAttribute('created', time());
        }
        else
        {
            $rowPending = array(
                'action'        => self::ACTION_UPDATE_CLASS,
                'created'       => time(),
                'param'         => $this->currentClass->attribute( 'identifier' )
            );
            $pendingItem = new eZPendingActions( $rowPending );
        }

        $pendingItem->store();
    }

}
