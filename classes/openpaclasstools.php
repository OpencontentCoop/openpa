<?php

class OpenPAClassTools
{
    const NOTICE = 0;
    const WARNING = 1;
    const ERROR = 2;

    public static $remoteUrl = 'http://openpa.opencontent.it/openpa/classdefinition/';
    
    public $EditLanguage = 'ita-IT';
    
    protected $id, $identifier;
    protected $currentClass;
    protected $options;
    protected $remoteClass;
    protected $notifications = array();
    protected $data;
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
    
    protected $notificationLevel = array(
        'serialized_name_list'          => self::WARNING,
        'serialized_description_list'   => self::WARNING,
        'data_type_string'              => self::ERROR,
        'placement'                     => self::WARNING,
        'is_searchable'                 => self::WARNING,
        'is_required'                   => self::WARNING,
        'is_information_collector'      => self::WARNING,
        'can_translate'                 => self::WARNING,
        'data_int1'                     => self::WARNING,
        'data_int2'                     => self::WARNING,
        'data_int3'                     => self::WARNING,
        'data_int4'                     => self::WARNING,
        'data_float1'                   => self::WARNING,
        'data_float2'                   => self::WARNING,
        'data_float3'                   => self::WARNING,
        'data_float4'                   => self::WARNING,
        'data_text1'                    => self::WARNING,
        'data_text2'                    => self::WARNING,
        'data_text3'                    => self::WARNING,
        'data_text4'                    => self::WARNING,
        'data_text5'                    => self::WARNING,                
        'category'                      => self::WARNING                
    );
    
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
        
    public function getRemote()
    {
        if ( $this->remoteClass == null )
        {
            $this->remoteClass = self::fetchRemoteByIdentifier( $this->identifier );
        }
        return $this->remoteClass;
    }
    
    public function getLocale()
    {                
        return $this->currentClass;
    }

    public function sync( $force = false, $removeExtras = false )
    {                        
        $modified = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_MODIFIED );
        if ( is_object( $modified ) )
        {
            throw new Exception( "Classe bloccata in modifica" );
        }
        $this->compare();        
        if ( $this->getData()->hasError && !$force )
        {
            throw new Exception( "La classe contiene campi che ne impediscono la sincronizzazione automatica" );
        }

        $temporary = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_TEMPORARY );
        
        if ( !is_object( $temporary ) or $temporary->attribute( 'id' ) == null )
        {
            $temporary = eZContentClass::fetch( $this->id, true, eZContentClass::VERSION_STATUS_DEFINED );
            if( $temporary === null ) // Class does not exist
            {
                throw new Exception( "La classe non esiste" );
            }
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
        
        $localeAttributes = array();
        foreach( $this->currentClass->fetchAttributes() as $attribute )
        {
            $attribute->setAttribute( 'version', eZContentClass::VERSION_STATUS_TEMPORARY );
            $localeAttributes[$attribute->attribute('identifier')] = $attribute;
        }
        $this->currentClass->setAttribute( 'version', eZContentClass::VERSION_STATUS_TEMPORARY );        
        
        $remote = $this->getRemote();
        if ( $remote === null )
        {
            throw new Exception( "Classe remota non trovata" );
        }
        
        if ( $force && $this->getData()->hasError )
        {
            foreach( $this->getData()->errors as $identifier => $value )
            {
                if ( !$localeAttributes[$identifier] instanceof eZContentClassAttribute )
                {
                    throw new Exception( 'Errore forzando la sincronizzazione' );
                }
                foreach( $remote->DataMap[0] as $originalAttribute )
                {
                    if ( $originalAttribute->Identifier == $identifier )
                    {                        
                        ezpEvent::getInstance()->notify( 'openpa/switch_class_attribute', array( $localeAttributes[$identifier], $originalAttribute ) );
                        if ( $value == 'data_type_string' )
                        {
                            $localeAttributes[$identifier]->setAttribute( 'data_type_string', $originalAttribute->DataTypeString );
                            $localeAttributes[$identifier]->store();
                            $this->changeContentObjectAttributeDataTypeString( $localeAttributes[$identifier], $originalAttribute->DataTypeString );
                            unset( $this->notifications[self::ERROR][$originalAttribute->Identifier] );
                        }
                        else
                        {
                            $this->data->missingAttributes[] = $originalAttribute;
                            $this->currentClass->removeAttributes( array( $localeAttributes[$identifier] ) );
                            unset( $localeAttributes[$identifier] );
                        }                        
                        break;
                    }
                }                
            }
        }
        
        $attributes = array();
                   
        foreach( $this->properties as $identifier => $remoteProperty )
        {
            if ( $remote->{ $remoteProperty } != $this->currentClass->attribute( $identifier ) )
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
            if ( isset( $localeAttributes[$originalAttribute->Identifier] ) )
            {
                $modified = $this->syncAttribute( $originalAttribute, $localeAttributes[$originalAttribute->Identifier] );
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
        
        $this->syncGroups();
    }
    
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
            if ( $remote->{ $remoteProperty } != $locale->attribute( $localeIdentifier ) )
            {
                $this->data->diffProperties[] =  array(
                    'field_name' => $localeIdentifier,
                    'locale_value' => htmlentities( $locale->attribute( $localeIdentifier ) ),
                    'remote_value' => htmlentities( $remote->{ $remoteProperty } )
                );
            }
        }
        
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
                'field_name' => 'Gruppi di classi',
                'locale_value' => implode( ', ', $localGroupsNames ),
                'remote_value' => implode( ', ', $remoteGroupsNames )
            ); 
        }        
    }
    
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
                    $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $attribute->attribute( 'id' ), true );
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
                if ( $localeAttribute->attribute( $localeIdentifier ) != $originalAttribute->{ $remoteProperty } )
                {
                    $this->notifications[$this->notificationLevel[$localeIdentifier]][$id] = $localeIdentifier;
                    $detail = false;
                    $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $localeAttribute->attribute( 'id' ), true );
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
                        'locale_value' =>  htmlentities( $localeAttribute->attribute( $localeIdentifier ) ),
                        'remote_value' =>  htmlentities( $originalAttribute->{ $remoteProperty } ),
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
                if ( $localeAttribute->attribute( $localeIdentifier ) != $originalAttribute->{ $remoteProperty } )
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
    
    protected function syncAllGroups( $remote = null)
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
    
    protected function changeContentObjectAttributeDataTypeString( $localeAttribute, $newDataTypeString )
    {
        $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList( $localeAttribute->attribute( 'id' ), true );
        foreach( $contentAttributes as $attribute )
        {
            $attribute->setAttribute( 'data_type_string', $newDataTypeString );
            $attribute->store();
        }
    }

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
    
}