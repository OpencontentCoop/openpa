<?php

class OpenPaFunctionCollection
{

    protected static $topmenu;
    protected static $home;

    public static $remoteHeader = 'OpenPaHeader';
    public static $remoteLogo = 'OpenPaLogo';
    public static $remoteRoles = 'OpenPaRuoli';

    public static $params = array(
        'SearchOffset' => 0,
        'SearchLimit' => 1000,
        'Facet' => null,
        'SortBy' => null,
        'Filter' => null,
        'SearchContentClassID' => null,
        'SearchSectionID' => null,
        'SearchSubTreeArray' => null,
        'AsObjects' => null,
        'SpellCheck' => null,
        'IgnoreVisibility' => null,
        'Limitation' => null,
        'BoostFunctions' => null,
        'QueryHandler' => 'ezpublish',
        'EnableElevation' => true,
        'ForceElevation' => true,
        'SearchDate' => null,
        'DistributedSearch' => null,
        'FieldsToReturn' => null,
        'SearchResultClustering' => null,
        'ExtendedAttributeFilter' => array()
    );
    
    public static function search( $params, $query = '' )
    {
        $solrSearch = new eZSolr();
        return $solrSearch->search( $query, $params );
    }

    static public function fetchObjectTree( $parentNodeID, $sortBy, $onlyTranslated, $language, $offset, $limit, $depth, $depthOperator,
                                            $classID, $attribute_filter, $extended_attribute_filter, $class_filter_type, $class_filter_array,
                                            $groupBy, $mainNodeOnly, $ignoreVisibility, $limitation, $asObject, $objectNameFilter, $loadDataMap = null )
    {
        $parentNode = OpenPABase::fetchNode( $parentNodeID );
        $handler = OpenPAObjectHandler::instanceFromObject( $parentNode );
        if ( $handler instanceof OpenPAObjectHandler )
        {            
            $virtualParameters = $handler->attribute( 'content_virtual' )->attribute( 'folder' );
            if ( $virtualParameters )
            {
                
                if ( $class_filter_type == 'include' )
                {
                    $classes = array_intersect( $class_filter_array, $virtualParameters['classes'] );
                }
                elseif ( $class_filter_type == 'exclude' )
                {
                    $classes = array_diff( $virtualParameters['classes'], $class_filter_array );
                }                
                if ( empty( $classes ) )
                {
                    return array( 'result' => array() );
                }
                
                $params = array(
                    'SearchSubTreeArray' => $virtualParameters['subtree'],
                    'SearchOffset' => $offset,
                    'SearchLimit' => $limit ? $limit : 50,
                    'SearchContentClassID' => $classes,
                    'SortBy' => $virtualParameters['sort'],
                    'Limitation' => $limitation
                );
                $search = self::search( $params );
                return array( 'result' => $search['SearchResult'] );
            }
        }

        return eZContentFunctionCollection::fetchObjectTree( $parentNodeID, $sortBy, $onlyTranslated, $language, $offset, $limit, $depth, $depthOperator,
            $classID, $attribute_filter, $extended_attribute_filter, $class_filter_type, $class_filter_array,
            $groupBy, $mainNodeOnly, $ignoreVisibility, $limitation, $asObject, $objectNameFilter, $loadDataMap );
    }

    static public function fetchObjectTreeCount( $parentNodeID, $onlyTranslated, $language, $class_filter_type, $class_filter_array,
                                                 $attributeFilter, $depth, $depthOperator,
                                                 $ignoreVisibility, $limitation, $mainNodeOnly, $extendedAttributeFilter, $objectNameFilter )
    {
        $parentNode = OpenPABase::fetchNode( $parentNodeID );
        $handler = OpenPAObjectHandler::instanceFromObject( $parentNode );
        if ( $handler instanceof OpenPAObjectHandler )
        {
            $virtualParameters = $handler->attribute( 'content_virtual' )->attribute( 'folder' );
            
            if ( $virtualParameters )
            {
                if ( $class_filter_type == 'include' )
                {
                    $classes = array_intersect( $class_filter_array, $virtualParameters['classes'] );
                }
                elseif ( $class_filter_type == 'exclude' )
                {
                    $classes = array_diff( $virtualParameters['classes'], $class_filter_array );
                }                
                if ( empty( $classes ) )
                {
                    return array( 'result' => 0 );
                }
                $params = array(
                    'SearchSubTreeArray' => $virtualParameters['subtree'],
                    'SearchLimit' => 1,
                    'SearchContentClassID' => $virtualParameters['classes'],
                    'SortBy' => $virtualParameters['sort']
                );                
                $search = self::search( $params );
                return array( 'result' => $search['SearchCount'] );
            }
        }

        return eZContentFunctionCollection::fetchObjectTreeCount( $parentNodeID, $onlyTranslated, $language, $class_filter_type, $class_filter_array,
            $attributeFilter, $depth, $depthOperator,
            $ignoreVisibility, $limitation, $mainNodeOnly, $extendedAttributeFilter, $objectNameFilter );
    }

    public static function fetchCalendarioEventi( $calendar, $params )
    {
        try
        {
            $data = new OpenPACalendarData( $calendar );
            $data->setParameters( $params );
            $data->fetch();
            return array( 'result' => $data->data );    
        }
        catch( Exception $e )
        {
            eZDebug::writeError( $e->getMessage(), __METHOD__ );
            return array( 'result' => array() );    
        }
        
    }

    public static function fetchRuoli( $struttura, $dipendente, $subtree, $roleNameType, $roleNamesArray )
    {
        $result = array();
        if ( empty( $subtree) )
        {
            $params = self::$params;        
            $parentObject = eZContentObject::fetchByRemoteID( self::$remoteRoles );
            if ( $parentObject instanceof eZContentObject )
            {
                $params['SearchSubTreeArray'] = array( $parentObject->attribute( 'main_node_id' ) );
            }
            else
            {
                $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ),
                                                       eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ) );
            }
            $params['SearchContentClassID'] = array( 'ruolo' );                
            if ( $struttura )
                $params['Filter'][] = array( 'submeta_struttura_di_riferimento___id_si:' . $struttura );
            elseif( $dipendente )
                $params['Filter'][] = array( 'submeta_utente___id_si:' . $dipendente );
                    
            $search = self::search( $params );
            $result = $search['SearchResult'];
        }
        elseif( $subtree )
        {            
            $data = array();
            
            $dipendentiSenzaRuoloIds = array();
            $params = OpenPaFunctionCollection::$params;
            $params['SearchContentClassID'] = array( 'dipendente' );
            $params['SearchSubTreeArray'] = $subtree;
            if ( count( $subtree ) > 1 )
            {
                $params['AsObjects'] = false;
                $params['FieldsToReturn'] = class_exists( 'ObjectHandlerServiceContentVirtual' ) ? array( ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY ) : null;
            }
            else
            {
                $params['AsObjects'] = true;
            }
            $search = OpenPaFunctionCollection::search( $params );
            if ( $search['SearchCount'] > 0 )
            {                
                foreach( $search['SearchResult'] as $item )
                {                                        
                    if ( count( $subtree ) > 1 )
                    {
                        $id = $item['id_si'];
                        $priority = class_exists( 'ObjectHandlerServiceContentVirtual' ) ? $item['fields'][ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY] : 0;
                    }
                    else
                    {
                        $id = $item->attribute( 'contentobject_id' );
                        $priority = $item->attribute( 'priority' );
                    }
                    $dipendentiSenzaRuoloIds[$id] = $id;
                    $dipendentiIdPriority[$id] = $priority;
                }
            }            
            $params = OpenPaFunctionCollection::$params;
            $parentObject = eZContentObject::fetchByRemoteID( OpenPaFunctionCollection::$remoteRoles );
            if ( $parentObject instanceof eZContentObject )
            {
                $params['SearchSubTreeArray'] = array( $parentObject->attribute( 'main_node_id' ) );
            }
            else
            {
                $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ),
                                                       eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ) );
            }
            $params['SearchContentClassID'] = array( 'ruolo' );    
            foreach( $subtree as $nodeId )
                $params['Filter'][] = array( 'submeta_utente___path_si:' . $nodeId );
            $params['AsObjects'] = true;
            $search = OpenPaFunctionCollection::search( $params );
            if ( $search['SearchCount'] > 0 )
            {
                $idsData = array();
                foreach( $search['SearchResult'] as $item )
                {
                    $users = array();
                    $dataMap = $item->attribute( 'data_map' );                
                    if ( isset( $dataMap['utente'] ) && $dataMap['utente'] instanceof eZContentObjectAttribute )
                    {
                        $users = explode( '-', $dataMap['utente']->toString() );
                    }
                    if ( isset( $idsData[$item->attribute( 'name' )] ) )
                    {
                        $idsData[$item->attribute( 'name' )] = array_merge( $idsData[$item->attribute( 'name' )], $users );
                    }
                    else
                    {
                        $idsData[$item->attribute( 'name' )] = $users;   
                    }
                    $idsData[$item->attribute( 'name' )] = array_unique( $idsData[$item->attribute( 'name' )] );
                }
                
                foreach( $idsData as $ruolo => $ids )
                {
                    $data[$ruolo] = array();
                    foreach( $ids as $id )
                    {
                        if ( isset( $dipendentiSenzaRuoloIds[$id] ) )
                        {
                            unset( $dipendentiSenzaRuoloIds[$id] );
                        }
                        $object = eZContentObject::fetch( $id );
                        if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
                        {
                            $data[$ruolo][$object->attribute('name')] = $object;
                        }
                        ksort( $data[$ruolo] );
                    }                    
                }
                $data['SenzaRuolo'] = array();
                foreach( $dipendentiSenzaRuoloIds as $id )
                {
                    $object = eZContentObject::fetch( $id );
                    if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
                    {
                        $data['SenzaRuolo'][$object->attribute('name')] = $object;
                    }
                    ksort( $data['SenzaRuolo'] );
                }
                
                if ( $roleNameType )
                {
                    $filterData = array();
                    if ( $roleNameType == 'include' )
                    {
                        foreach( $data as $name => $objects )
                        {
                            if ( in_array( $name, $roleNamesArray ) )
                            {
                                foreach( $objects as $object )
                                {
                                    $filterData[$object->attribute( 'id' )] = $object;
                                }
                            }
                        }
                    }
                    elseif ( $roleNameType == 'exclude' )
                    {
                        foreach( $data as $name => $objects )
                        {
                            if ( !in_array( $name, $roleNamesArray ) )
                            {
                                foreach( $objects as $object )
                                {
                                    $filterData[$object->attribute( 'id' )] = $object;
                                }
                            }
                        }
                    }                   
                    
                    $data = $filterData;                    
                    uasort( $data, function( $a, $b ) use ( $dipendentiIdPriority ) {
                        $aPriority = isset( $dipendentiIdPriority[$a->attribute( 'id' )] ) ? $dipendentiIdPriority[$a->attribute( 'id' )] : 0;
                        $bPriority = isset( $dipendentiIdPriority[$b->attribute( 'id' )] ) ? $dipendentiIdPriority[$b->attribute( 'id' )] : 0;
                        if ( $aPriority == $bPriority ) {
                            $return = 0;
                        }
                        else
                        {
                            $return = ( $aPriority < $bPriority ) ? 1 : -1;
                        }
                        return $return;
                    });                    
                }
            }
            
            $result = $data;
        }
        return array( 'result' => $result );
    }
    
    public static function fetchNomiRuoliDirigenziali()
    {
        $nomi = array( 'Segretario generale', 'Dirigente generale', 'Dirigente di Servizio', 'Responsabile di Servizio' );
        return array( 'result' => $nomi );
    }
    
    public static function fetchDirigenti()
    {
        $result = array();
        $nomi = self::fetchNomiRuoliDirigenziali();        
        if ( count( $nomi['result'] ) > 0 )
        {
            $params = self::$params;
            $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ),
                                                   eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ) );
            $params['SearchContentClassID'] = array( 'ruolo' );
            $filterNomi = array( 'or' );
            foreach( $nomi['result'] as $nome )
            {                
                $filterNomi[] = array( 'attr_titolo_s:"' . $nome . '"');
            }
            $params['Filter'][] = $filterNomi;
            $params['AsObjects'] = false;
            $search = self::search( $params );
            $nodes = array();            
            foreach( $search['SearchResult'] as $item )
            {
                if ( isset( $item['fields']['submeta_utente___main_node_id_si'][0] ) )
                {
                    $nodes[] = $item['fields']['submeta_utente___main_node_id_si'][0];
                }
            }            
            $result = eZContentObjectTreeNode::fetch( $nodes );
        }
        return array( 'result' => $result );
    }
    
    public static function fetchAree()
    {
        $params = self::$params;
        $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
        $params['SearchContentClassID'] = array( 'area' );
        $params['SortBy'] = array( 'name' => 'asc' );
        $search = self::search( $params );        
        return array( 'result' => $search['SearchResult'] );
    }
    
    public static function fetchServizi()
    {
        $params = self::$params;
        $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
        $params['SearchContentClassID'] = array( 'servizio' );
        $params['SortBy'] = array( 'name' => 'asc' );
        $search = self::search( $params );        
        return array( 'result' => $search['SearchResult'] );
    }
    
    public static function fetchUffici()
    {
        $params = self::$params;
        $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
        $params['SearchContentClassID'] = array( 'ufficio' );
        $params['SortBy'] = array( 'name' => 'asc' );
        $search = self::search( $params );        
        return array( 'result' => $search['SearchResult'] );
    }    

    public static function fetchDipendenti( $struttura, $subtree )
    {
        $params = self::$params;
        if ( is_array( $subtree ) && !empty( $subtree ) )
        {
            foreach( $subtree as $index => $item )
            {
                if ( empty( $item ) )
                {
                    unset( $subtree[$index] );
                }
            }
            if ( empty( $subtree ) )
            {
                return array( 'result' => array() );
            }
            $params['SearchSubTreeArray'] = $subtree;
        }
        else
        {
            $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );   
        }        
        $params['SearchContentClassID'] = array( 'dipendente', 'personale' );
        $params['SortBy'] = array( 'name' => 'asc' );
        if ( $struttura instanceof eZContentObjectTreeNode )
        {
            if ( $struttura->attribute( 'class_identifier' ) == 'struttura' )
            {
                $params['Filter'][] = array( "submeta_struttura___id_si:" . $struttura->attribute( 'contentobject_id' ) );
                $params['Filter'][] = array( "submeta_altra_struttura___id_si:" . $struttura->attribute( 'contentobject_id' ) );
            }
            else
            {
                $params['Filter'][] = array( "submeta_" . $struttura->attribute( 'class_identifier' ) . "___id_si:" . $struttura->attribute( 'contentobject_id' ) );
            }
        }
        $search = self::search( $params );        
        return array( 'result' => $search['SearchResult'] );
    }

    public static function fetchHeaderImageStyle()
    {
        $result = false;
        $image = self::fetchHeaderImage();        
        if ( $image )
        {
            $result = "background:url(/{$image['full_path']}) no-repeat center center !important; width:{$image['width']}px; height:{$image['height']}px";                
        }
        return array( 'result' => $result );
    }
    
    public static function fetchFooterNotes()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode
             && $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $dataMap = $homePage->attribute( 'data_map' );
            if ( isset( $dataMap['note_footer'] ) && $dataMap['note_footer'] instanceof eZContentObjectAttribute && $dataMap['note_footer']->attribute( 'has_content' ) )
            {
                $result = $dataMap['note_footer'];                
            }
        }
        return array( 'result' => $result );
    }
        
    public static function fetchFooterLinks()
    {
        $nodes = array();
        $homePage = self::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode
             && $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $dataMap = $homePage->attribute( 'data_map' );
            if ( isset( $dataMap['link_nel_footer'] ) && $dataMap['link_nel_footer'] instanceof eZContentObjectAttribute && $dataMap['link_nel_footer']->attribute( 'has_content' ) )
            {
                $content = $dataMap['link_nel_footer']->attribute( 'content' );                
                foreach( $content['relation_list'] as $item )
                {
                    if ( isset( $item['node_id'] ) )
                    {
                        $nodes[] = eZContentObjectTreeNode::fetch( $item['node_id'] );
                    }
                }
            }
        }
        else
        {
            $links = array();
            $links[] = OpenPAINI::variable( 'LinkSpeciali', 'NodoCredits', false );
            $links[] = OpenPAINI::variable( 'LinkSpeciali', 'NodoNoteLegali', false );
            $links[] = OpenPAINI::variable( 'LinkSpeciali', 'NodoPrivacy', false );
            $links[] = OpenPAINI::variable( 'LinkSpeciali', 'NodoDichiarazione', false );
            $links[] = self::fetchTrasparenza();
            foreach( $links as $link )
            {
                if ( $link )
                {
                    $nodes[] = eZContentObjectTreeNode::fetch( $link );
                }
            }
        }        
        return array( 'result' => $nodes );
    }
    
    public static function fetchHeaderLogoStyle()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $headerObject = $homePage->attribute( 'object' );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['logo'] ) && $dataMap['logo'] instanceof eZContentObjectAttribute && $dataMap['logo']->attribute( 'has_content' ) )
                {
                    $result = self::getLogoCssStyle( $dataMap['logo'], 'header_logo' );
                }
            }
        }
        else
        {
            $headerObject = eZContentObject::fetchByRemoteID( self::$remoteLogo );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] ) && $dataMap['image'] instanceof eZContentObjectAttribute && $dataMap['image']->attribute( 'has_content' ) )
                {
                    $result = self::getLogoCssStyle( $dataMap['image'], 'header_logo' );                    
                }
            }
        }
        return array( 'result' => $result );
    }
    
    public static function fetchReverseRelatedObjectClassFacets( $object, $classFilterType, $classFilterArray, $sortBy, $subTree )
    {
        $resultData = array();
        if ( $object instanceof eZContentObject )
        {
            $ezobjectrelationlist = eZContentClassAttribute::fetchFilteredList( array( 'data_type_string' => 'ezobjectrelationlist') );
            $attributes = array();
            foreach( $ezobjectrelationlist as $attribute )
            {
                $attributeContent = $attribute->content();
                if ( !empty( $attributeContent['class_constraint_list'] ) )
                {					
                    if ( in_array( $object->attribute( 'class_identifier' ), $attributeContent['class_constraint_list']  ) )
                    {
                        $class = eZContentClass::fetch( $attribute->attribute('contentclass_id') );
                        $classIdentifier = eZContentClass::classIdentifierByID( $attribute->attribute('contentclass_id') );
                        $attributes[$classIdentifier][] = array(
                            'class_id' => $attribute->attribute('contentclass_id'),
                            'class_identifier' => $classIdentifier,
                            'class_name' => $class->attribute('name'),
                            'attribute_identifier' => $attribute->attribute('identifier'),
                            'attribute_name' => $attribute->attribute('name'),
                            'class_constraint_list' => $attributeContent['class_constraint_list']
                        );
                    }
                }
            }
            
            $contentINI = eZINI::instance( 'content.ini' );
            $findINI = eZINI::instance( 'ezfind.ini' );
            $solrINI = eZINI::instance( 'solr.ini' );
            $siteINI = eZINI::instance();
            
            $languages = $siteINI->variable( 'RegionalSettings', 'SiteLanguageList' );
            $currentLanguage = $languages[0];
            
            $facetQueryData = array();
            $facetQuery = array();
            $fq = array();
            //$attributeFilter = array( 'or' );
            $resultData = array();
            
            foreach( $attributes as $classIdentifier => $values )
            {
                foreach( $values as $value )
                {
                    $query = "subattr_{$value['attribute_identifier']}___name____s:\"{$object->attribute( 'name' )}\" AND meta_contentclass_id_si:{$value['class_id']}";
                    $facetQuery[$query] = $query;
                    $facetQueryData[$query] = $value;
                    //$attributeFilter[] = "submeta_servizio___id_si:" . $object->attribute( 'id' );
                }
            }
            
            //if ( !empty( $attributeFilter ) )
            //{
            //    $fq[] = '(' . implode( ' OR ', $attributeFilter ) . ')';
            //}
            
            $policies = array();
            $accessResult = eZUser::currentUser()->hasAccessTo( 'content', 'read' );
            if ( !in_array( $accessResult['accessWord'], array( 'yes', 'no' ) ) )
            {
                $policies = $accessResult['policies'];
            }
            
            
            $limitationHash = array(
                'Class'        => eZSolr::getMetaFieldName( 'contentclass_id' ),
                'Section'      => eZSolr::getMetaFieldName( 'section_id' ),
                'User_Section' => eZSolr::getMetaFieldName( 'section_id' ),
                'Subtree'      => eZSolr::getMetaFieldName( 'path_string' ),
                'User_Subtree' => eZSolr::getMetaFieldName( 'path_string' ),
                'Node'         => eZSolr::getMetaFieldName( 'main_node_id' ),
                'Owner'        => eZSolr::getMetaFieldName( 'owner_id' ),
                'Group'        => eZSolr::getMetaFieldName( 'owner_group_id' ),
                'ObjectStates' => eZSolr::getMetaFieldName( 'object_states' ) );
            
            $filterQueryPolicies = array();
            
            // policies are concatenated with OR
            foreach ( $policies as $limitationList )
            {
                // policy limitations are concatenated with AND
                // except for locations policity limitations, concatenated with OR
                $filterQueryPolicyLimitations = array();
                $policyLimitationsOnLocations = array();
            
                foreach ( $limitationList as $limitationType => $limitationValues )
                {
                    // limitation values of one type in a policy are concatenated with OR
                    $filterQueryPolicyLimitationParts = array();
            
                    switch ( $limitationType )
                    {
                        case 'User_Subtree':
                        case 'Subtree':
                        {
                            foreach ( $limitationValues as $limitationValue )
                            {
                                $pathString = trim( $limitationValue, '/' );
                                $pathArray = explode( '/', $pathString );
                                // we only take the last node ID in the path identification string
                                $subtreeNodeID = array_pop( $pathArray );
                                $policyLimitationsOnLocations[] = eZSolr::getMetaFieldName( 'path' ) . ':' . $subtreeNodeID;                    
                            }
                        } break;
            
                        case 'Node':
                        {
                            foreach ( $limitationValues as $limitationValue )
                            {
                                $pathString = trim( $limitationValue, '/' );
                                $pathArray = explode( '/', $pathString );
                                // we only take the last node ID in the path identification string
                                $nodeID = array_pop( $pathArray );
                                $policyLimitationsOnLocations[] = $limitationHash[$limitationType] . ':' . $nodeID;                    
                            }
                        } break;
            
                        case 'Group':
                        {
                            foreach ( eZUser::currentUser()->attribute( 'contentobject' )->attribute( 'parent_nodes' ) as $groupID )
                            {
                                $filterQueryPolicyLimitationParts[] = $limitationHash[$limitationType] . ':' . $groupID;
                            }
                        } break;
            
                        case 'Owner':
                        {
                            $filterQueryPolicyLimitationParts[] = $limitationHash[$limitationType] . ':' . eZUser::currentUser()->attribute ( 'contentobject_id' );
                        } break;
            
                        case 'Class':
                        case 'Section':
                        case 'User_Section':
                        {
                            foreach ( $limitationValues as $limitationValue )
                            {
                                $filterQueryPolicyLimitationParts[] = $limitationHash[$limitationType] . ':' . $limitationValue;
                            }
                        } break;
            
                        default :
                        {
                            //hacky, object state limitations reference the state group name in their
                            //limitation
                            //hence the following match on substring
            
                            if ( strpos( $limitationType, 'StateGroup' ) !== false )
                            {
                                foreach ( $limitationValues as $limitationValue )
                                {
                                    $filterQueryPolicyLimitationParts[] = $limitationHash['ObjectStates'] . ':' . $limitationValue;
                                }
                            }
                            else
                            {
                                eZDebug::writeDebug( $limitationType, __METHOD__ . ' unknown limitation type: ' . $limitationType );
                                continue;
                            }
                        }
                    }
            
                    if ( !empty( $filterQueryPolicyLimitationParts ) )
                        $filterQueryPolicyLimitations[] = '( ' . implode( ' OR ', $filterQueryPolicyLimitationParts ) . ' )';
                }
            
                // Policy limitations on locations (node and/or subtree) need to be concatenated with OR
                // unlike the other types of limitation
                if ( !empty( $policyLimitationsOnLocations ) )
                {
                    $filterQueryPolicyLimitations[] = '( ' . implode( ' OR ', $policyLimitationsOnLocations ) . ')';
                }
            
                if ( !empty( $filterQueryPolicyLimitations ) )
                {
                    $filterQueryPolicies[] = '( ' . implode( ' AND ', $filterQueryPolicyLimitations ) . ')';
                }
            }
            
            if ( !empty( $filterQueryPolicies ) )
            {
                $fq[] = implode( ' OR ', $filterQueryPolicies );
            }
            
            if ( !$subTree )
            {
                $fq[] = "meta_path_si:" . $contentINI->variable( 'NodeSettings', 'RootNode' );    
            }
            else
            {
                $subTreeFilter = array( 'or' );
                foreach( $subTree as $subTreeNodeId )
                {
                    $subTreeFilter[] = "meta_path_si:" . $subTreeNodeId;
                }
                $fq[] = $subTreeFilter;
            }
            
            $fq[] = '(' . eZSolr::getMetaFieldName( 'installation_id' ) . ':' . eZSolr::installationID() . ' AND ' . eZSolr::getMetaFieldName( 'is_invisible' ) . ':false)';
            //$fq[] = eZSolr::getMetaFieldName( 'language_code' ) . ':' . $currentLanguage;
            
            $result = array();        
            $limit = 100;
            
            $params = array( 'q' => '*:*',
                             'rows' => 0,
                             'json.nl' => 'arrarr',
                             'facet' => 'true',
                             'facet.field' => array( 'meta_class_identifier_ms', 'meta_class_name_ms' ),
                             'facet.query' => array_values( $facetQuery ),
                             'facet.limit' => 1000,
                             'facet.method' => 'fc',
                             'facet.mincount' => 1 );
            
            if ( $findINI->variable( 'LanguageSearch', 'MultiCore' ) == 'enabled' )
            {
               $languageMapping = $findINI->variable( 'LanguageSearch','LanguagesCoresMap' );
               $shardMapping = $solrINI->variable( 'SolrBase', 'Shards' );
               $fullSolrURI = $shardMapping[$languageMapping[$currentLanguage]];
            }
            else
            {
                $fullSolrURI = $solrINI->variable( 'SolrBase', 'SearchServerURI' );
                // Autocomplete search should be done in current language and fallback languages
                $validLanguages = array_unique(
                    array_merge(
                        $siteINI->variable( 'RegionalSettings', 'SiteLanguageList' ),
                        array( $currentLanguage )
                    )
                );
                $fq[] = eZSolr::getMetaFieldName( 'language_code' ) . ':(' . implode( ' OR ', $validLanguages ) . ')';        
            }
            
            $params['fq'] = $fq;
            
            $solrBase = new eZSolrBase( $fullSolrURI );
            $result = $solrBase->rawSolrRequest( '/select', $params, 'json' );
        
        
            if ( isset( $result['facet_counts'] ) )
            {
                foreach( $result['facet_counts']['facet_queries'] as $query => $value )
                {
                    if ( isset( $facetQueryData[$query] ) && $value > 0 )
                    {                
                        if ( $classFilterType == 'include' && in_array( $facetQueryData[$query]['class_identifier'], $classFilterArray ) )
                        {
                            $do = true;
                        }
                        elseif ( $classFilterType == 'exclude' && in_array( $facetQueryData[$query]['class_identifier'], $classFilterArray ) )
                        {
                            $do = false;
                        }
                        else
                        {
                            $do = true;
                        }
                        
                        if ( $do )
                        {
                            $facetQueryData[$query]['value'] = $value;
                            $facetQueryData[$query]['query'] = $query;
                            $resultData[$facetQueryData[$query]['class_name']][] = new OpenPATempletizable( $facetQueryData[$query] );                            
                        }
                    }
                }
            }
            if ( $sortBy == 'alpha' )
            {
                ksort( $resultData );
            }
            else
            {
                usort( $resultData, array( 'OpenPaFunctionCollection', 'sortHashByValue' ) );
            }            
        }
        return array( 'result' => $resultData );
    }
    
    protected static function sortHashByValue( $a, $b )
    {
        $aValue = 0;
        foreach( $a as $item )
        {
            $aValue += $item->attribute( 'value' );
        }
        $bValue = 0;
        foreach( $b as $item )
        {
            $bValue += $item->attribute( 'value' );
        }
        return ( $aValue > $bValue ) ? -1 : 1;
    }
    
    // fetch non richiamabili da template (manca il  array(result => ...))
    // @todo renderle protected??
    
    public static function fetchTrasparenza()
    {
        if ( eZContentClass::fetchByIdentifier( 'trasparenza', false ) )
        {
            $params = self::$params;
            $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
            $params['SearchContentClassID'] = array( 'trasparenza' );
            $params['SearchLimit'] = 1;
            $params['AsObjects'] = true;
            $search = self::search( $params );        
            if ( $search['SearchCount'] > 0 )
            {
                return $search['SearchResult'][0]->attribute( 'node_id' );
            }
        }
        return false;
    }
    
    public static function fetchHeaderImage()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode
             && $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $headerObject = $homePage->attribute( 'object' );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] ) && $dataMap['image'] instanceof eZContentObjectAttribute && $dataMap['image']->attribute( 'has_content' ) )
                {
                    $result = $dataMap['image']->attribute( 'content' )->attribute( 'header_banner' );                
                }
            }
        }
        else
        {
            $headerObject = eZContentObject::fetchByRemoteID( self::$remoteHeader );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] ) && $dataMap['image'] instanceof eZContentObjectAttribute && $dataMap['image']->attribute( 'has_content' ) )
                {
                    $result = $dataMap['image']->attribute( 'content' )->attribute( 'header_banner' );                    
                }
            }
        }
        return $result;
    }

    public static function fetchHeaderLogo()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $headerObject = $homePage->attribute( 'object' );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['logo'] ) && $dataMap['logo'] instanceof eZContentObjectAttribute && $dataMap['logo']->attribute( 'has_content' ) )
                {
                    $result = $dataMap['logo']->attribute( 'content' )->attribute( 'header_logo' );
                }
            }
        }
        else
        {
            $headerObject = eZContentObject::fetchByRemoteID( self::$remoteLogo );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] ) && $dataMap['image'] instanceof eZContentObjectAttribute && $dataMap['image']->attribute( 'has_content' ) )
                {
                    $result = $dataMap['image']->attribute( 'content' )->attribute( 'header_logo' );
                }
            }
        }
        return $result;
    }
    
    protected static function getLogoCssStyle( eZContentObjectAttribute $attribute, $alias )
    {
        $image = $attribute->attribute( 'content' )->attribute( $alias );
        $width = $image['width']  . 'px';
        $height = $image['height'] . 'px';
        $additionaStyle = 'padding:0;';
        $headerImage = self::fetchHeaderImage();
        if ( is_array( $headerImage ) )
        {
            if ( $image['height'] > $headerImage['height'] )
            {
                $height = $headerImage['height'] . 'px';
                //$width = 'auto';
            }
            else
            {
                $additionaStyle .= "margin-top: " . ( $headerImage['height'] - $image['height'] ) / 2 . "px;";
            }
            
            if ( $image['width'] >= $headerImage['width'] || $image['width'] == '1000' )
            {
                $additionaStyle .= "margin-left:0;";
            }
            
        }
        else
        {
            if( $image['height'] == '200' )
            {
                $additionaStyle .= "margin-top:0;";
            }
            if ( $image['width'] == '1000' )
            {
                $additionaStyle .= "margin-left:0;";
            }
        }
        return "display: block;text-indent: -9999px;background:url(/{$image['full_path']}) no-repeat center center; width:{$width}; height:{$height};{$additionaStyle}"; 
    }

    
    public static function fetchHomepage()
    {
        return array( 'result' => self::fetchHome() );
    }
    
    public static function fetchHome()
    {
        if ( self::$home == null )
        {                        
            self::$home = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );            
        }
        return self::$home;
    }
    
    public static function fetchTopMenuNodes()
    {
        if ( self::$topmenu == null )
        {            
            $homePage = self::fetchHome();
            if ( $homePage instanceof eZContentObjectTreeNode && $homePage->attribute( 'class_identifier' ) == 'homepage' )
            {
                $dataMap = $homePage->attribute( 'data_map' );
                if ( isset( $dataMap['link_al_menu_orizzontale'] ) && $dataMap['link_al_menu_orizzontale'] instanceof eZContentObjectAttribute
                     && $dataMap['link_al_menu_orizzontale']->attribute( 'has_content' ) )
                {
                    self::$topmenu = array();
                    $content = $dataMap['link_al_menu_orizzontale']->attribute( 'content' );
                    foreach( $content['relation_list'] as $item )
                    {
                        if ( isset( $item['node_id'] ) )
                        {
                            self::$topmenu[] = $item['node_id'];
                        }
                    }
                }
            }

            // non usare qui OpenPAINI perchè questa funzione è un filtro di OpenPAIINI::filter
            if ( eZINI::instance( 'openpa.ini' )->hasVariable( 'TopMenu', 'NodiCustomMenu' ) )
            {
                $fromIni = eZINI::instance( 'openpa.ini' )->variable( 'TopMenu', 'NodiCustomMenu' );
            }
            if ( empty( self::$topmenu ) && !empty( $fromIni ) )
            {
                self::$topmenu = $fromIni;
            }
            if ( empty( self::$topmenu ) && $homePage instanceof eZContentObjectTreeNode )
            {                
                $nodes = eZFunctionHandler::execute(
                    'content',
                    'list',
                    array(
                         'parent_node_id' => $homePage->attribute( 'node_id' ),
                         'sort_by' => $homePage->attribute( 'sort_array' ),
                         'class_filter_type' => 'include',
                         'load_data_map' => false,
                         'class_filter_array' => OpenPAINI::variable( 'TopMenu', 'IdentificatoriMenu' ),
                         'limit' => OpenPAINI::variable( 'TopMenu', 'LimitePrimoLivello', 4 )
                    )
                );
                if ( count( $nodes ) )
                {
                    foreach( $nodes as $node )
                    {                        
                        self::$topmenu[] = $node->attribute( 'node_id' );
                    }
                }
            }
        }
        return self::$topmenu;
    }    
    
}

?>
