<?php

class OpenPaFunctionCollection
{

    protected static $topmenu;
    protected static $home;
    protected static $headerImageStyle;
    protected static $headerLogoStyle;
    protected static $headerLogo;

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

    protected static function dipendentiClassIdentifiers()
    {
        $returnData = array();
        $classIdentifiers = array( 'user', 'dipendente', 'personale', 'consulente' );
        foreach( $classIdentifiers as $classIdentifier )
            if ( eZContentClass::classIDByIdentifier( $classIdentifier ) )
                $returnData[] = $classIdentifier;
        return $returnData;
    }

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
        if ( $handler instanceof OpenPAObjectHandler && $handler->hasContent() )
        {
            $virtualParameters = $handler->attribute( 'content_virtual' )->attribute( 'folder' );
            if ( $virtualParameters )
            {
                $classes = (array) $virtualParameters['classes'];
                if ( $class_filter_type == 'include' )
                {
                    $classes = array_intersect( $class_filter_array, $virtualParameters['classes'] );
                }
                elseif ( $class_filter_type == 'exclude' && empty( $virtualParameters['classes'] ) )
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
                $classes = (array) $virtualParameters['classes'];
                if ( $class_filter_type == 'include' )
                {
                    $classes = array_intersect( $class_filter_array, $virtualParameters['classes'] );
                }
                elseif ( $class_filter_type == 'exclude' && empty( $virtualParameters['classes'] ) )
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
        $dipendentiIdPriority = array();
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
                                                       eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ),
                                                       eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'UserRootNode' ) );
            }
            $params['SearchContentClassID'] = array( 'ruolo' );
            if ( $struttura )
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('struttura_di_riferimento', 'id') . ':' . $struttura );
            elseif( $dipendente )
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('utente', 'id') . ':' . $dipendente );

            $search = self::search( $params );

            $result = array();
            foreach( $search['SearchResult'] as $item )
            {
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $item->attribute( 'data_map' );
                if ( isset( $dataMap['utente'] )
                     && $dataMap['utente'] instanceof eZContentObjectAttribute
                     && $dataMap['utente']->hasContent())
                {
                    $result[] = $item;
                }
            }

        }
        elseif( $subtree )
        {
            $data = array();

            $dipendentiSenzaRuoloIds = array();
            $params = OpenPaFunctionCollection::$params;
            $params['SearchContentClassID'] = self::dipendentiClassIdentifiers();
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
                /** @var array|eZFindResultNode $item */
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
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('utente', 'path') . ':' . $nodeId );

            if ( $struttura )
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('struttura_di_riferimento', 'id') . ':' . $struttura );

            $params['AsObjects'] = true;
            $search = OpenPaFunctionCollection::search( $params );
            if ( $search['SearchCount'] > 0 )
            {
                $idsData = array();
                foreach( $search['SearchResult'] as $item )
                {
                    $users = array();
                    /** @var eZContentObjectAttribute[] $dataMap */
                    $dataMap = $item->attribute( 'data_map' );
                    if ( isset( $dataMap['utente'] ) && $dataMap['utente'] instanceof eZContentObjectAttribute )
                    {
                        $users = explode( '-', $dataMap['utente']->toString() );
                    }
                    if (count($users) > 0) {
                        if (isset( $idsData[$item->attribute('name')] )) {
                            $idsData[$item->attribute('name')] = array_merge(
                                $idsData[$item->attribute('name')],
                                $users
                            );
                        } else {
                            $idsData[$item->attribute('name')] = $users;
                        }
                        $idsData[$item->attribute('name')] = array_unique($idsData[$item->attribute('name')]);
                    }
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
                        /**
                         * @var string $name
                         * @var eZContentObject[] $objects
                         */
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
                        /**
                         * @var string $name
                         * @var eZContentObject[] $objects
                         */
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
                        /** @var eZContentObject $a */
                        /** @var eZContentObject $b */
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
                $filterNomi[] = array( OpenPASolr::generateSolrField( 'titolo', 'string' ) . ':"' . $nome . '"');
            }
            $params['Filter'][] = $filterNomi;
            $params['AsObjects'] = false;
            $search = self::search( $params );
            $nodes = array();
            foreach( $search['SearchResult'] as $item )
            {
                $submetaUtenteMainNodeField = OpenPASolr::generateSolrSubMetaField('utente', 'main_node_id');
                if ( isset( $item['fields'][$submetaUtenteMainNodeField][0] ) )
                {
                    $nodes[] = $item['fields'][$submetaUtenteMainNodeField][0];
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
        $params['SearchContentClassID'] = self::dipendentiClassIdentifiers();
        $params['SortBy'] = array( 'name' => 'asc' );
        if ( $struttura instanceof eZContentObjectTreeNode )
        {
            if ( $struttura->attribute( 'class_identifier' ) == 'struttura' )
            {
                $params['Filter'] = array('or');
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('struttura', 'id') . ":" . $struttura->attribute( 'contentobject_id' ) );
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField('altra_struttura', 'id') . ":" . $struttura->attribute( 'contentobject_id' ) );
            }
            else
            {
                $params['Filter'][] = array( OpenPASolr::generateSolrSubMetaField($struttura->attribute( 'class_identifier' ), 'id') . ":" . $struttura->attribute( 'contentobject_id' ) );
            }
        }
        $search = self::search( $params );
        return array( 'result' => $search['SearchResult'] );
    }

    public static function fetchHeaderImageStyle()
    {
        if (self::$headerImageStyle === null) {
            self::$headerImageStyle = OpenPAPageData::getHeaderImageStyleCache()->processCache(
                function ($file) {
                    $content = include($file);
                    return $content;
                },
                function () {
                    eZDebug::writeNotice("Regenerate header_image cache", 'OpenPaFunctionCollection::fetchHeaderImageStyle');
                    $result = false;
                    $image = OpenPaFunctionCollection::fetchHeaderImage();
                    if ($image) {
                        $result = "background:url(/{$image['full_path']}) no-repeat center center !important; width:{$image['width']}px; height:{$image['height']}px";
                    }
                    return array(
                        'content' => $result,
                        'scope' => 'cache',
                        'datatype' => 'php',
                        'store' => true
                    );
                }
            );
        }
        return array( 'result' => self::$headerImageStyle );
    }

    public static function fetchFooterNotes()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode
             && $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            /** @var eZContentObjectAttribute[] $dataMap */
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
            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $homePage->attribute( 'data_map' );
            if ( isset( $dataMap['link_nel_footer'] )
                 && $dataMap['link_nel_footer'] instanceof eZContentObjectAttribute
                 && $dataMap['link_nel_footer']->attribute( 'has_content' )
                 && $dataMap['link_nel_footer']->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
            {
                $content = $dataMap['link_nel_footer']->attribute( 'content' );
                foreach( $content['relation_list'] as $item )
                {
                    if ( isset( $item['node_id'] ) )
                    {
                        $node = eZContentObjectTreeNode::fetch( (int)$item['node_id'] );
                        if ( $node instanceof eZContentObjectTreeNode )
                        {
                            $nodes[] = $node;
                        }
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
        if (self::$headerLogoStyle === null) {
            self::$headerLogoStyle = OpenPAPageData::getHeaderLogoStyleCache()->processCache(
                function ($file) {
                    $content = include($file);
                    return $content;
                },
                function () {
                    eZDebug::writeNotice("Regenerate header_logo cache", 'OpenPaFunctionCollection::fetchHeaderLogoStyle');
                    $homePage = OpenPaFunctionCollection::fetchHome();
                    if ($homePage->attribute('class_identifier') == 'homepage') {
                        $headerObject = $homePage->attribute('object');
                        if ($headerObject instanceof eZContentObject) {
                            /** @var eZContentObjectAttribute[] $dataMap */
                            $dataMap = $headerObject->attribute('data_map');
                            if (isset($dataMap['logo'])
                                && $dataMap['logo'] instanceof eZContentObjectAttribute
                                && $dataMap['logo']->attribute('has_content')) {
                                $result = OpenPaFunctionCollection::getLogoCssStyle($dataMap['logo'], 'header_logo');
                            }
                        }
                    } else {
                        $headerObject = eZContentObject::fetchByRemoteID(self::$remoteLogo);
                        if ($headerObject instanceof eZContentObject) {
                            /** @var eZContentObjectAttribute[] $dataMap */
                            $dataMap = $headerObject->attribute('data_map');
                            if (isset($dataMap['image'])
                                && $dataMap['image'] instanceof eZContentObjectAttribute
                                && $dataMap['image']->attribute('has_content')) {
                                $result = OpenPaFunctionCollection::getLogoCssStyle($dataMap['image'], 'header_logo');
                            }
                        }
                    }

                    if (empty($result)) {
                        $result = '';
                    }

                    return array(
                        'content' => $result,
                        'scope' => 'cache',
                        'datatype' => 'php',
                        'store' => true
                    );
                }
            );
        }
        return array( 'result' => self::$headerLogoStyle );
    }

    public static function fetchReverseRelatedObjectClassFacets( $object, $classFilterType, $classFilterArray, $sortBy, $subTree )
    {
        $resultData = array();
        if ( $object instanceof eZContentObject )
        {
            /** @var eZContentObjectAttribute[] $ezobjectrelationlist */
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
                    $query = OpenPASolr::generateSolrSubMetaField($value['attribute_identifier'], 'id') .":\"{$object->attribute( 'id' )}\" AND " . eZSolr::getMetaFieldName( 'contentclass_id' ) . ":{$value['class_id']}";
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
                            /** @var eZContentObject $userObject */
                            $userObject = eZUser::currentUser()->attribute( 'contentobject' );
                            foreach ( $userObject->attribute( 'parent_nodes' ) as $groupID )
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
                //$fq[] = eZSolr::getMetaFieldName( 'path' ) . ":" . $contentINI->variable( 'NodeSettings', 'RootNode' );
            }
            else
            {
                $subTreeFilter = array( 'or' );
                foreach( $subTree as $subTreeNodeId )
                {
                    $subTreeFilter[] = eZSolr::getMetaFieldName( 'path' ) . ":" . $subTreeNodeId;
                }
                $fq[] = $subTreeFilter;
            }

            $fq[] = '(' . eZSolr::getMetaFieldName( 'installation_id' ) . ':' . eZSolr::installationID() . ' AND ' . eZSolr::getMetaFieldName( 'is_invisible' ) . ':false)';
            //$fq[] = eZSolr::getMetaFieldName( 'language_code' ) . ':' . $currentLanguage;

            $params = array( 'q' => '*:*',
                             'rows' => 0,
                             'json.nl' => 'arrarr',
                             'facet' => 'true',
                             'facet.field' => array( eZSolr::getMetaFieldName( 'class_identifier' ), eZSolr::getMetaFieldName( 'class_name' ) ),
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
                        (array)$siteINI->variable( 'RegionalSettings', 'SiteLanguageList' ),
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

    /**
     * @param OpenPATempletizable[] $a
     * @param OpenPATempletizable[] $b
     *
     * @return int
     */
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
                /** @var eZFindResultNode[] $searchResults */
                $searchResults = $search['SearchResult'];
                return $searchResults[0]->attribute( 'node_id' );
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
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] )
                     && $dataMap['image'] instanceof eZContentObjectAttribute
                     && $dataMap['image']->attribute( 'has_content' ) )
                {
                    /** @var eZImageAliasHandler $content */
                    $content = $dataMap['image']->attribute( 'content' );
                    $result = $content->attribute( 'header_banner' );
                }
            }
        }
        else
        {
            $headerObject = eZContentObject::fetchByRemoteID( self::$remoteHeader );
            if ( $headerObject instanceof eZContentObject )
            {
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['image'] )
                     && $dataMap['image'] instanceof eZContentObjectAttribute
                     && $dataMap['image']->attribute( 'has_content' ) )
                {
                    /** @var eZImageAliasHandler $content */
                    $content = $dataMap['image']->attribute( 'content' );
                    $result = $content->attribute( 'header_banner' );
                }
            }
        }
        return $result;
    }

    public static function fetchHeaderLogo()
    {
        if (self::$headerLogo === null) {
            self::$headerLogo = OpenPAPageData::getHeaderLogoCache()->processCache(
                function ($file) {
                    $content = include($file);
                    return $content;
                },
                function () {
                    eZDebug::writeNotice("Regenerate header_logo cache", 'OpenPaFunctionCollection::fetchHeaderLogo');
                    $result = array();
                    $homePage = OpenPaFunctionCollection::fetchHome();
                    if ($homePage->attribute('class_identifier') == 'homepage') {
                        $headerObject = $homePage->attribute('object');
                        if ($headerObject instanceof eZContentObject) {
                            /** @var eZContentObjectAttribute[] $dataMap */
                            $dataMap = $headerObject->attribute('data_map');
                            if (isset($dataMap['logo'])
                                && $dataMap['logo'] instanceof eZContentObjectAttribute
                                && $dataMap['logo']->attribute('has_content')) {
                                /** @var eZImageAliasHandler $content */
                                $content = $dataMap['logo']->attribute('content');
                                $result = $content->attribute('header_logo');
                            }
                        }
                    } else {
                        $headerObject = eZContentObject::fetchByRemoteID(OpenPaFunctionCollection::$remoteLogo);
                        if ($headerObject instanceof eZContentObject) {
                            /** @var eZContentObjectAttribute[] $dataMap */
                            $dataMap = $headerObject->attribute('data_map');
                            if (isset($dataMap['image'])
                                && $dataMap['image'] instanceof eZContentObjectAttribute
                                && $dataMap['image']->attribute('has_content')) {
                                /** @var eZImageAliasHandler $content */
                                $content = $dataMap['image']->attribute('content');
                                $result = $content->attribute('header_logo');
                            }
                        }
                    }

                    if (empty($result)) {
                        $result = array('url' => false);
                    }

                    return array(
                        'content' => $result,
                        'scope' => 'cache',
                        'datatype' => 'php',
                        'store' => true
                    );
                }
            );
        }

        return self::$headerLogo;
    }

    public static function fetchStemma()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
        {
            $headerObject = $homePage->attribute( 'object' );
            if ( $headerObject instanceof eZContentObject )
            {
                $dataMap = $headerObject->attribute( 'data_map' );
                if ( isset( $dataMap['stemma'] ) && $dataMap['stemma'] instanceof eZContentObjectAttribute && $dataMap['stemma']->attribute( 'has_content' ) )
                {
                    $result = $dataMap['stemma']->attribute( 'content' )->attribute( 'original' );
                }
            }
        }
        return $result;
    }

    protected static function getLogoCssStyle( eZContentObjectAttribute $attribute, $alias )
    {
        /** @var eZImageAliasHandler $content */
        $content = $attribute->attribute( 'content' );
        $image = $content->attribute( $alias );
        $width = $image['width']  . 'px';
        $height = $image['height'] . 'px';
        $additionalStyle = 'padding:0;';
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
                $additionalStyle .= "margin-top: " . ( $headerImage['height'] - $image['height'] ) / 2 . "px;";
            }

            if ( $image['width'] >= $headerImage['width'] || $image['width'] == '1000' )
            {
                $additionalStyle .= "margin-left:0;";
            }

        }
        else
        {
            if( $image['height'] == '200' )
            {
                $additionalStyle .= "margin-top:0;";
            }
            if ( $image['width'] == '1000' )
            {
                $additionalStyle .= "margin-left:0;";
            }
        }
        return "display: block;text-indent: -9999px;background:url(/{$image['full_path']}) no-repeat center center; width:{$width}; height:{$height};{$additionalStyle}";
    }

    public static function fetchHomepage()
    {
        return array( 'result' => self::fetchHome() );
    }

    public static function fetchHome()
    {
        if ( self::$home == null )
        {
            $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            if ( eZINI::instance( 'content.ini' )->hasVariable( 'NodeSettings', 'HomepageNode' ) )
            {
                $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'HomepageNode' );
            }
            if (is_numeric($rootNodeId)){
                self::$home = eZContentObjectTreeNode::fetch( $rootNodeId );
            }else{
                self::$home = eZContentObjectTreeNode::fetchByRemoteID( $rootNodeId );
            }

            if (!self::$home instanceof eZContentObjectTreeNode){
                throw new RuntimeException("Home node not found");
            }
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
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $homePage->attribute( 'data_map' );
                if ( isset( $dataMap['link_al_menu_orizzontale'] )
                     && $dataMap['link_al_menu_orizzontale'] instanceof eZContentObjectAttribute
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
                /** @var eZContentObjectTreeNode[] $nodes */
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

    public static function fetchRecaptchaHTML()
    {
        require_once 'extension/openpa/lib/recaptchalib.php';
        $ini = eZINI::instance( 'ezcomments.ini' );
        $publicKey = $ini->variable( 'RecaptchaSetting', 'PublicKey' );
        $useSSL = false;
        if( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] )
        {
            $useSSL = true;
        }
        return array( 'result' => recaptcha_get_html( $publicKey ), null, $useSSL );
    }

    protected static function getNode( $parentNodeId )
    {
        $parentNode = $parentNodeId;
        if ( !$parentNode instanceof eZContentObjectTreeNode )
        {
            $parentNode = eZContentObjectTreeNode::fetch( $parentNodeId );
        }
        return $parentNode instanceof eZContentObjectTreeNode ? $parentNode : null;
    }

    protected static function getChildrenClasses( $parentNodeId )
    {
        $childrenClassTypes = array();
        if ( $parentNode = self::getNode( $parentNodeId ) )
        {
            // ricavo gli identifiers delle classi e le classi
            $childrenClassTypes = array();
            $childrenClassesParamers = array(
                'SearchSubTreeArray'=> array( $parentNode->attribute( 'node_id' ) ),
                'SearchLimit' => 1,
                'Filter' => array( '-' . eZSolr::getMetaFieldName( 'id', 'filter' ) . ':' . $parentNode->attribute( 'contentobject_id' ) ),
                'AsObjects' => false,
                'Facet' => array(
                    array(
                        'field' => eZSolr::getMetaFieldName( 'class_identifier', 'facet' ),
                        'name' => 'class_identifier',
                        'limit' => 200
                    )
                )
            );
            $solr = new eZSolr();
            $search = $solr->search( '', $childrenClassesParamers );
            if ( $search['SearchCount'] > 0 )
            {
                /** @var ezfSearchResultInfo $searchExtras */
                $searchExtras = $search['SearchExtras'];
                $facets = $searchExtras->attribute( 'facet_fields' );
                $childrenClassTypes = $facets[0]['nameList'];
            }
            if ( !empty( $childrenClassTypes ) )
            {
                $childrenClassTypes = (array) eZContentClass::fetchList( 0, true, false, null, null, $childrenClassTypes );
            }
        }
        return $childrenClassTypes;
    }

    public static function fetchMapMarkers( $parentNodeId, $childrenClassIdentifiers )
    {
        foreach( $childrenClassIdentifiers as $key => $value )
        {
            if ( empty( $value ) ) unset( $childrenClassIdentifiers[$key] );
        }
        $sortBy = array( 'name' => 'asc' );

        $result = array();

        if ( $parentNode = self::getNode( $parentNodeId ) )
        {
            if ( !empty( $childrenClassIdentifiers ) )
            {
                $childrenClassTypes = (array) eZContentClass::fetchList( 0, true, false, null, null, $childrenClassIdentifiers );
            }
            else
            {
                $childrenClassTypes = self::getChildrenClasses( $parentNodeId );
            }

            // ricavo gli attributi delle classi
            $geoAttributes = array();
            $classIds = array();
            foreach ( $childrenClassTypes as $classType )
            {
                if ( $classType instanceof eZContentClass )
                {
                    $classIds[] = $classType->attribute( 'id' );
                    $geoAttributes = array_merge( $geoAttributes,
                        eZContentClassAttribute::fetchFilteredList( array( 'contentclass_id' => $classType->attribute( 'id' ),
                                                                           'version' => $classType->attribute( 'version' ),
                                                                           'data_type_string' => 'ezgmaplocation' ) )
                    );
                }
            }

            if ( count( $geoAttributes ) )
            {
                $typeNames = array(
                    OpenPASolr::generateSolrSubField( 'tipo_luogo', 'name', 'string'),
                    OpenPASolr::generateSolrSubField( 'tipo_servizio_sul_territorio', 'name', 'string'),
                    eZSolr::getMetaFieldName( 'class_identifier' ),
                    eZSolr::getMetaFieldName( 'class_name' ),
                );

                // imposto i filtri di ricerca
                $geoFields = $geoFieldsNames = $geoFieldsFilters = array();
                foreach( $geoAttributes as $geoAttribute )
                {
                    if ( $geoAttribute instanceof eZContentClassAttribute )
                    {
                        //$geoFieldsFilters[] = "attr_{$geoAttribute->attribute( 'identifier' )}_t:['' TO *]";
                        $geoFields[$geoAttribute->attribute( 'identifier' )] = $geoAttribute->attribute( 'name' );
                        $geoFieldsNames[] = OpenPASolr::generateSolrSubField( $geoAttribute->attribute( 'identifier' ), 'coordinates', 'geopoint');
                    }
                }

                $geoFieldsFilters = array_unique( $geoFieldsFilters );
                $geoFields = array_unique( $geoFields );
                $geoFieldsNames = array_unique( $geoFieldsNames );

                if ( count( $geoFieldsFilters ) > 1 )
                {
                    array_unshift( $geoFieldsFilters, 'or' );
                    $geoFieldsFilters = array( $geoFieldsFilters );
                }
                $childrenParameters = array(
                    'SearchSubTreeArray'=> array( $parentNode->attribute( 'node_id' ) ),
                    'Filter' => array_merge( array( '-' . eZSolr::getMetaFieldName( 'id', 'filter' ) . ':' . $parentNode->attribute( 'contentobject_id' ) ), $geoFieldsFilters ),
                    'SearchContentClassID' => $classIds,
                    'SearchLimit' => 1000,
                    'AsObjects' => false,
                    'SortBy' => $sortBy,
                    'FieldsToReturn' => array_merge( $geoFieldsNames, $typeNames )
                );

                // cerco i figli
                $solr = new OpenPASolr();
                $children = $solr->search( '', $childrenParameters );
                if ( $children['SearchCount'] > 0 )
                {
                    foreach( $children['SearchResult'] as $item )
                    {
                        $type = null;
                        foreach( $typeNames as $typeName )
                        {
                            if ( isset( $item['fields'][$typeName] ) )
                            {
                                if ( is_array( $item['fields'][$typeName] ) )
                                {
                                    $type = $item['fields'][$typeName][0];
                                }
                                else
                                {
                                    $type = $item['fields'][$typeName];
                                }
                                break;
                            }
                        }
                        foreach( $geoFieldsNames as $geoFieldsName )
                        {
                            @list( $longitude, $latitude ) = explode( ',', $item['fields'][$geoFieldsName][0] );
                            if( (floatval( $latitude ) != 0.0) and (floatval( $longitude ) != 0.0))
                            {
                                $href = isset( $item['main_url_alias'] ) ? $item['main_url_alias'] : $item['main_url_alias_ms'];
                                $classIdentifier = isset( $item['class_identifier'] ) ? $item['class_identifier'] : $item['meta_class_identifier_ms'];
                                $className = isset( $item['class_name'] ) ? $item['class_name'] : $item['meta_class_name_ms'];
                                eZURI::transformURI( $href, false, 'full' );

                                $popup = isset( $item['name'] ) ? $item['name'] : $item['name_t'];
                                $id = isset( $item['id'] ) ? $item['id'] : $item['id_si'];

                                $result[] = array(
                                    'id' => $id,
                                    'type' => $type,
                                    'class' => $classIdentifier,
                                    'className' => $className,
                                    'lat' => floatval( $latitude ),
                                    'lon' => floatval( $longitude ),
                                    'lng' => floatval( $longitude ),
                                    'popupMsg' => $popup,
                                    'title' => $popup,
                                    'description' => "<h3><a href='{$href}'>{$popup}</a></h3>", //@todo
                                    'urlAlias' => $href
                                );
                            }
                        }
                    }
                }
            }
        }
        return array( 'result' => $result );
    }

}
