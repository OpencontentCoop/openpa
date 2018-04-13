<?php

use Opencontent\Opendata\Api\QueryLanguage\EzFind\QueryBuilder as EzFindQueryBuilder;
use Opencontent\Opendata\Api\ClassRepository;

class OpenPAOperator
{

    private $area_tematica_node = array();

    private static $currentObjectId;

    private static $trasparenzaRootNodeId;

    private static $searchData;

    function __construct()
    {
        $this->Operators= array(
            'openpaini',
            'get_main_style', 'has_main_style',
            'is_area_tematica', 'get_area_tematica_style',
            'is_dipendente',
            'openpa_shorten',
            'has_abstract', 'abstract',
            'rss_list',
            'materia_make_tree',
            'access_style',
            'unique',
            'find_first_parent',
            'current_object_id',
            'fix_dimension',
            'object_state_list',
            'site_identifier',
            'solr_field',
            'solr_meta_field',
            'solr_subfield',
            'solr_meta_subfield',
            'search_exclude_class_facets',
            'search_exclude_classes',
            'search_include_classes',
            'search_query',
            'strReplace',
            'organigramma',
            'trasparenza_root_node_id'
        );
    }

    function operatorList()
    {
        return $this->Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'openpaini' => array(
                'block' => array('type' => 'string', 'required' => true),
                'setting' => array('type' => 'string', 'required' => true),
                'default' => array('type' => 'mixed', 'required' => false, 'default' => false)
            ),
            'has_main_style' => array(
                'node' => array('type' => 'mixed', 'required' => true)
            ),
            'openpa_shorten' => array(
                'chars_to_keep' => array("type" => "integer", "required" => false, "default" => 80),
                'str_to_append' => array("type" => "string", "required" => false, "default" => "..."),
                'trim_type' => array("type" => "string", "required" => false, "default" => "right")
            ),
            'has_abstract' => array(
                'node' => array("type" => "integer", "required" => false, "default" => false)
            ),
            'abstract' => array(
                'node' => array("type" => "integer", "required" => false, "default" => false)
            ),
            'rss_list' => array(
                'fetchList' => array("type" => "string", "required" => true, "default" => 'export')
            ),
            'materia_make_tree' => array(
                'relation_list' => array("type" => "array", "required" => true, "default" => array())
            ),
            'find_first_parent' => array(
                'class' => array("type" => "mixed", "required" => true, "default" => null)
            ),
            'solr_field' => array(
                'identifier' => array("type" => "string", "required" => true),
                'type' => array("type" => "string", "required" => true)
            ),
            'solr_meta_field' => array(
                'identifier' => array("type" => "string", "required" => true)
            ),
            'solr_subfield' => array(
                'identifier' => array("type" => "string", "required" => true),
                'sub_identifier' => array("type" => "string", "required" => true),
                'type' => array("type" => "string", "required" => true)
            ),
            'solr_meta_subfield' => array(
                'identifier' => array("type" => "string", "required" => true),
                'sub_identifier' => array("type" => "string", "required" => true)
            ),
            'strReplace' => array(
                'var' => array ( 'type' => 'string', 'required' => true, 'default' => ''),
                'value' => array ( 'type' => 'array', 'required' => true,'default' => '' )
            ),
            'search_query' => array(
                'override' => array ( 'type' => 'mixed', 'required' => false, 'default' => array())
            ),
            'organigramma' => array(
                'root_object_id' => array ( 'type' => 'integer', 'required' => false, 'default' => null)
            )
        );
    }

    /**
     * @param eZTemplate $tpl
     * @param $operatorName
     * @param $operatorParameters
     * @param $rootNamespace
     * @param $currentNamespace
     * @param $operatorValue
     * @param $namedParameters
     *
     * @return array|bool|mixed|string
     */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        $ini = eZINI::instance( 'openpa.ini' );
        if ( $tpl->hasVariable('module_result') )
        {
           $moduleResult = $tpl->variable('module_result');
        }
        else
        {
            $moduleResult = array();
        }

        $viewmode = false;
        if ( isset( $moduleResult['content_info'] ) )
        {
            if ( isset( $moduleResult['content_info']['viewmode'] ) )
            {
                $viewmode = $moduleResult['content_info']['viewmode'];
            }
        }

        $path = ( isset( $moduleResult['path'] ) && is_array( $moduleResult['path'] ) ) ? $moduleResult['path'] : array();

        switch ( $operatorName )
        {
            case 'trasparenza_root_node_id':
            {
                $operatorValue = self::getTrasparenzaRootNodeId();
                break;
            }

            case 'organigramma':
            {
                $data = OpenPAOrganigrammaTools::instance()->tree($namedParameters['root_object_id']);
                $operatorValue = json_decode( json_encode( $data ), 1 );
                break;
            }

            case 'strReplace':
            {
                $variable = $namedParameters['var'];
                $value = @$namedParameters['value'];
                $operatorValue = str_replace($value[0],$value[1],$variable);
                break;
            }

            case 'search_exclude_class_facets':
            case 'search_exclude_classes':
            case 'search_include_classes':
            {
                $searchData = self::getSearchData();
                $excludeFacets = $searchData['exclude_facets'];
                $excludeClasses = $searchData['exclude_classes'];
                $includeClasses = $searchData['include_classes'];

                if ( $operatorName == 'search_exclude_class_facets' )
                {
                    $operatorValue = array(
                      'ids' => array_keys( $excludeFacets ),
                      'identifiers' => array_values( $excludeFacets )
                    );
                }
                if ( $operatorName == 'search_exclude_classes' )
                {
                    $operatorValue = array(
                      'ids' => array_keys( $excludeClasses ),
                      'identifiers' => array_values( $excludeClasses )
                    );
                }
                if ( $operatorName == 'search_include_classes' )
                {
                    $operatorValue = array(
                        'ids' => array_keys( $includeClasses ),
                        'identifiers' => array_values( $includeClasses )
                    );
                }

            } break;

            case 'search_query':
            {
                $operatorValue = null;
                $http = eZHTTPTool::instance();

                $queryArray = array();

                $sort = null;
                $order = 'desc';
                if ( $http->hasGetVariable( 'Sort' ) )
                {
                    $sort = $http->getVariable( 'Sort' );
                    if ( !empty( $sort ) )
                    {
                        $order = $http->hasGetVariable( 'Order' ) ? $http->getVariable( 'Order' ) : 'desc';
                    }
                }
                if ( !$sort && $http->hasGetVariable( 'SearchText' ) && !empty( $http->getVariable( 'SearchText' ) ) )
                {
                    $sort = 'score';
                }
                if ( !$sort )
                {
                    $sort = 'published';
                }

                if ( $http->hasGetVariable( 'SearchText' ) && !empty( $http->getVariable( 'SearchText' ) ) )
                {
                    $searchText = $http->getVariable( 'SearchText' );
                    $searchText = addslashes($searchText);
                    if ( $http->hasGetVariable( 'Logic' ) && $http->getVariable( 'Logic' ) == 'OR' )
                    {
                        $searchText = implode( ' OR ', explode( ' ', $searchText ) );
                    }
                    elseif ( $http->hasGetVariable( 'Logic' ) && $http->getVariable( 'Logic' ) == 'AND' )
                    {
                        $searchText = implode( ' AND ', explode( ' ', $searchText ) );
                    }
                    $queryArray[] = "q = '$searchText'";
                }

                $subtree = array();
                if ( $http->hasGetVariable( 'SubTreeArray' ) && !empty( $http->getVariable( 'SubTreeArray' ) ) )
                {
                    $subtree = (array)$http->getVariable( 'SubTreeArray' );
                }

                $rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
                if (empty($subtree) || (count($subtree) == 1 && $subtree[0] == $rootNodeId))
                {
                    $subtree = array($rootNodeId);
                    $trasparenzaRootNodeId = self::getTrasparenzaRootNodeId();
                    if ($trasparenzaRootNodeId){
                        $subtree[] = $trasparenzaRootNodeId;
                    }
                }
                $queryArray[] = 'subtree [' . implode( ',', $subtree ) . ']';

                $classList = array();
                if ( $http->hasGetVariable( 'ClassArray' ) && !empty( $http->getVariable( 'ClassArray' ) ) )
                {
                    $classIDList = $http->getVariable( 'ClassArray' );
                    $classList = array();
                    foreach( $classIDList as $id )
                    {
                        $identifier = eZContentClass::classIdentifierByID( $id );
                        if ( $identifier )
                        {
                            $classList[] = $identifier;
                        }
                    }
                    if ( !empty( $classList ) )
                    {
                        $queryArray[] = 'classes [' . implode( ',', $classList ) . ']';
                    }
                }

                if ( count( $classList ) == 1 && $http->hasGetVariable( 'Data' ) && !empty( $http->getVariable( 'Data' ) ) )
                {
                    try
                    {
                        $_GET['Sort'] = $sort;

                        $classRepository = new ClassRepository();
                        $class = (array) $classRepository->load( $classList[0] );
                        $fields = array();
                        foreach ( $class['fields'] as $field ){
                            $fields[$field['identifier']] = $field;
                        }

                        $data = $http->getVariable( 'Data' );
                        foreach( $data as $key => $values )
                        {
                            if ( $key == 'published' ){
                                $startDateTime = isset( $values[0] ) ? DateTime::createFromFormat('d-m-Y', $values[0], new DateTimeZone('Europe/Rome') ) : new DateTime();
                                $endDateTime = isset( $values[1] ) ? DateTime::createFromFormat('d-m-Y', $values[1], new DateTimeZone('Europe/Rome') ) : new DateTime();
                                if ( $startDateTime instanceof DateTime && $endDateTime instanceof DateTime )
                                {
                                    $queryArray[] = "published range [{$startDateTime->format('Y-m-d')},{$endDateTime->format('Y-m-d')}]";
                                }
                            }

                            if ( isset($fields[$key] ) ){
                                if ( in_array( $fields[$key]['dataType'], array( 'ezdate', 'ezdatetime' ) ) )
                                {
                                    $startDateTime = isset( $values[0] ) ? DateTime::createFromFormat('d-m-Y', $values[0], new DateTimeZone('Europe/Rome') ) : new DateTime();
                                    $endDateTime = isset( $values[1] ) ? DateTime::createFromFormat('d-m-Y', $values[1], new DateTimeZone('Europe/Rome') ) : new DateTime();
                                    if ( $startDateTime instanceof DateTime && $endDateTime instanceof DateTime )
                                    {
                                        $queryArray[] = "$key range [{$startDateTime->format('Y-m-d')},{$endDateTime->format('Y-m-d')}]";
                                    }
                                }
                                elseif ( in_array( $fields[$key]['dataType'], array( 'ezobjectrelationlist' ) ) )
                                {
                                    $stringValue = trim( implode(',', $values) );
                                    if ( !empty($stringValue) )
                                    {
                                        $queryArray[] = "{$key}.id in [{$stringValue}]";
                                    }
                                }
                                elseif ( in_array( $fields[$key]['dataType'], array( 'ezstring' ) ) )
                                {
                                    if ( !empty( $values ) )
                                        $queryArray[] = "{$key} = [{$values}]";
                                        //$queryArray[] = "{$key} = [\"{$values}\"]"; //@see Opencontent\Opendata\Api\QueryLanguage\EzFind\SentenceConverter::formatFilterValue
                                }
                                else
                                {
                                    if ( !empty( $values ) )
                                        $queryArray[] = "{$key} = [{$values}]";
                                }
                            }
                        }
                    }
                    catch( Exception $e )
                    {
                        eZDebug::writeError( $e->getMessage(), __METHOD__ . ':' . $operatorName );
                    }
                }

                if ( $http->hasGetVariable( 'Anno' ) && !empty( $http->getVariable( 'Anno' ) ) )
                {
                    $start = $http->getVariable( 'Anno' ) . '-01-01';
                    $end = $http->getVariable( 'Anno' ) . '-12-31';
                    $_GET['Data']['published'] = array( '01-01-'.$http->getVariable( 'Anno' ), '31-12-'.$http->getVariable( 'Anno' ) );
                    $queryArray[] = "published range [$start,$end]";
                }

                $queryArray[] = "sort [{$sort}=>{$order}]";

                $queryString = null;
                if ( !empty( $queryArray ) )
                {
                    $queryString = implode( ' and ', $queryArray );
                    eZDebugSetting::writeNotice( 'openpa-operators', $queryString, __METHOD__ );
                }

                $builder = new EzFindQueryBuilder();

                try
                {
                    if (!$queryString){
                        throw new Exception("Query string is null");
                    }

                    $override = array();
                    if ( is_array( $namedParameters['override'] ) )
                    {
                        $override = $namedParameters['override'];
                    }
                    elseif( is_string( $namedParameters['override'] ) )
                    {
                        $queryObject = $builder->instanceQuery( $namedParameters['override'] );
                        eZDebugSetting::writeNotice( 'openpa-operators', $namedParameters['override'], __METHOD__ );
                        $ezFindQueryObject = $queryObject->convert();
                        if ( $ezFindQueryObject instanceof ArrayObject )
                        {
                            $override = $ezFindQueryObject->getArrayCopy();
                        }
                    }

                    $queryObject = $builder->instanceQuery( $queryString );
                    $ezFindQueryObject = $queryObject->convert();
                    if ( $ezFindQueryObject instanceof ArrayObject )
                    {
                        $queryArray = $ezFindQueryObject->getArrayCopy();
                    }

                    $queryArray = array_merge( $queryArray, $override );
                    eZDebugSetting::writeNotice( 'openpa-operators', $queryArray, __METHOD__ );
                    $solr = new eZSolr();
                    $results = @$solr->search(
                        trim($queryArray['_query'], "'"),
                        $queryArray
                    );


                    $results['UriSuffix'] = '?' . http_build_query( $_GET );
                }
                catch( Exception $e )
                {
                    eZDebug::writeError( $e->getMessage(), __METHOD__ . ':' . $operatorName );
                    $results = null;
                }

                return $operatorValue = $results;
            } break;

            case 'solr_field':
            {
                return $operatorValue = OpenPASolr::generateSolrField( $namedParameters['identifier'], $namedParameters['type'] );
            } break;

            case 'solr_meta_field':
            {
                return $operatorValue = eZSolr::getMetaFieldName( $namedParameters['identifier'] );
            } break;

            case 'solr_subfield':
            {
                return $operatorValue = OpenPASolr::generateSolrSubField( $namedParameters['identifier'], $namedParameters['sub_identifier'], $namedParameters['type'] );
            } break;

            case 'solr_meta_subfield':
            {
                return $operatorValue = OpenPASolr::generateSolrSubMetaField( $namedParameters['identifier'], $namedParameters['sub_identifier'] );
            } break;

            case 'site_identifier':
            {
                return $operatorValue = OpenPABase::getCurrentSiteaccessIdentifier();
            } break;

            case 'object_state_list':
            {
                $list = array();
                foreach( eZContentObjectStateGroup::limitations() as $limitation )
                {
                    $groupName = str_replace( 'StateGroup_', '', $limitation['name'] );
                    $limitationValueList = call_user_func_array( array( $limitation['class'], $limitation['function'] ), $limitation['parameter'] );
                    foreach ( $limitationValueList as $limitationValue )
                    {
                        $list[$limitationValue['id']] = "({$groupName}) {$limitationValue['name']} ";
                    }
                }
                return $operatorValue = $list;
            } break;

            case 'fix_dimension':
            {
                $parts = explode( 'px', $operatorValue );
                $operatorValue = $parts[0];
            } break;

            case 'current_object_id':
            {
                $operatorValue = self::currentObjectId();
            } break;

            case 'find_first_parent':
            {
                $startNode = $operatorValue;
                $operatorValue = false;
                $class = is_array( $namedParameters['class'] ) ? $namedParameters['class'] : array( $namedParameters['class'] );
                if ( $startNode instanceof eZContentObjectTreeNode )
                {
                    $path = $startNode->attribute( 'path' );
                    $path = array_reverse( $path );
                    foreach( $class as $identifier )
                    {
                        foreach( $path as $item )
                        {
                            if ( $item->attribute( 'class_identifier' ) == $identifier )
                            {
                                $operatorValue = $item;
                                return true;
                            }
                        }
                    }
                }
            } break;

            case 'unique':
            {
                if ( is_array( $operatorValue ) )
                {
                    $operatorValue = array_unique( $operatorValue );
                } break;
            }

            case 'access_style':
            {
                $result = '';
                if ( $operatorValue instanceof eZContentObjectTreeNode )
                {
                    $anonymous = eZUser::fetch( eZUser::anonymousId() );
                    if ( $anonymous instanceof eZUser )
                    {
                        $tool = new OpenPAWhoCan( $operatorValue->attribute( 'object' ), 'read', $anonymous );
                        $can = $tool->run();
                        if ( $can !== true )
                        {
                            $result = 'no-sezioni_per_tutti';
                        }
                    }
                }
                $operatorValue = $result;
            } break;

            case 'materia_make_tree':
            {
                $items = $namedParameters['relation_list'];
                $materie = array();
                foreach( $items as $item )
                {
                    if ( $item['in_trash'] == false && $item['contentclass_identifier'] == 'materia' )
                    {
                        $materie[] = array( 'node_id' => $item['node_id'] );
                    }
                }
                foreach( $items as $item )
                {
                    if ( $item['in_trash'] == false && $item['contentclass_identifier'] == 'sotto_materia' )
                    {
                        foreach ( $materie as $index => $materia )
                        {
                            if ( $materia['node_id'] == $item['parent_node_id'] )
                            {
                                $materie[$index]['children_node_ids'][] = $item['node_id'];
                            }
                        }
                    }
                }
                return $operatorValue = $materie;
            } break;

            case 'rss_list':
            {
                $list = array();
                if ( $namedParameters['fetchList'] == 'export' )
                {
                    $exportArray = eZRSSExport::fetchList();
                    $list = array();
                    foreach( $exportArray as $export )
                    {
                        $list[$export->attribute( 'id' )] = $export;
                    }
                }
                elseif ( $namedParameters['fetchList'] == 'import' )
                {
                    $importArray = eZRSSImport::fetchList();
                    $list = array();
                    foreach( $importArray as $import )
                    {
                        $list[$import->attribute( 'id' )] = $import;
                    }
                }
                return $operatorValue = $list;
            } break;

            case 'has_main_style':
            {
                $style = false;

                $node = $namedParameters['node'];

                if ( is_numeric( $node ) )
                {
                    $node = OpenPABase::fetchNode( $node );
                }

                if ( $node instanceof eZContentObjectTreeNode )
                {
                    $mainStyles = $ini->hasVariable( 'Stili', 'Nodo_NomeStile' ) ? $ini->variable( 'Stili', 'Nodo_NomeStile' ) : array();
                    $pathArray = $node->attribute( 'path_array' );
                    foreach( $pathArray as $p )
                    {
                        if ( isset( $mainStyles[$p] ) )
                        {
                            $style = $mainStyles[$p];
                        }
                    }
                }

                $operatorValue = $style;

            } break;

            case 'get_main_style':
            {
                $style = 'no-main-style';

                if ( $viewmode && $viewmode !== 'full' )
                    return $operatorValue = $style;

                $mainStyles = array();
                $mainStylesTmp = $ini->hasVariable( 'Stili', 'Nodo_NomeStile' ) ? $ini->variable( 'Stili', 'Nodo_NomeStile' ) : array();
                foreach( $mainStylesTmp as $styleParts )
                {
                    $nodeStyle = explode( ';', $styleParts );
                    if ( isset( $nodeStyle[1] ) )
                    {
                        $mainStyles[$nodeStyle[0]] = $nodeStyle[1];
                    }
                }

                foreach ( $path as $key => $item )
                {
                    if ( isset( $item['node_id'] ) )
                    {

                        if ( isset( $mainStyles[ $item['node_id'] ] ) )
                        {
                            $style = $mainStyles[ $item['node_id'] ];
                        }

                    }
                }

                $areaStyle = array();

                if ( OpenPAINI::variable( 'AreeTematiche', 'UsaStileInMotoreRicerca', false ) == 'enabled' )
                {
                    $http = eZHTTPTool::instance();
                    if ( $http->hasGetVariable( 'SubTreeArray' ) )
                    {
                        $subTreeArray = $http->getVariable( 'SubTreeArray' );
                        if ( count( $subTreeArray ) == 1 )
                        {
                            $path[] = array( 'node_id' => $subTreeArray[0] );
                        }
                    }
                }

                foreach( $path as $p )
                {
                    if ( isset( $p['node_id'] ) )
                    {
                        $isAreaTematica = $this->get_area_tematica_node( $p['node_id'] );
                        if ( $isAreaTematica !== false )
                        {
                            if ( empty( $areaStyle ) )
                            {
                                $areaStyle[] = 'aree-tematiche';
                                $areaStyle[] = 'area_tematica';
                            }

                            $areaCustomStyle =  $this->get_area_tematica_style( $p['node_id'] );
                            if ( !empty( $areaCustomStyle ) )
                            {
                                $areaStyle[] = $areaCustomStyle;
                            }
                        }
                    }
                }

                if ( !empty( $areaStyle ) )
                {
                    $style = implode( ' ', $areaStyle );
                }

                $operatorValue = $style;
            } break;

            case 'is_area_tematica':
            {
                $result = false;
                if ( empty( $path ) )
                {
                    $path = array();
                    $currentNode = $tpl->variable( 'node' );
                    if ( $currentNode )
                    {
                        $pathArray = explode( '/', $currentNode->attribute( 'path_string' ) );
                        foreach( $pathArray as $p )
                        {
                            if ( $p != ''
                                 && $p != 1
                                 && $p != eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' )
                                 && strpos( eZINI::instance()->variable( 'SiteSettings', 'IndexPage' ), $p ) === false
                                 )
                            {
                                $path[] = array( 'node_id' => $p );
                            }
                        }

                    }
                }

                foreach ( $path as $key => $item )
                {
                    if ( isset( $item['node_id'] ) )
                    {
                        if ( $this->get_area_tematica_node( $item['node_id'] ) )
                        {

                            $result = $this->get_area_tematica_node( $item['node_id'] );
                            break;
                        }
                    }
                }

                if ( OpenPAINI::variable( 'AreeTematiche', 'UsaStileInMotoreRicerca', false ) == 'enabled' )
                {
                    $http = eZHTTPTool::instance();
                    if ( $http->hasGetVariable( 'SubTreeArray' ) )
                    {
                        $subTreeArray = $http->getVariable( 'SubTreeArray' );
                        if ( count( $subTreeArray ) == 1 )
                        {
                            $result = $this->get_area_tematica_node( $subTreeArray[0] );
                        }
                    }
                }

                $operatorValue = $result;

            } break;

            case 'get_area_tematica_style':
            {
                $result = false;
                if ( empty( $path ) )
                {
                    $path = array();
                    $currentNode = $tpl->variable( 'node' );
                    if ( $currentNode )
                    {
                        $pathArray = explode( '/', $currentNode->attribute( 'path_string' ) );
                        foreach( $pathArray as $p )
                        {
                            if ( $p != '' && $p != 1 )
                                $path[] = array( 'node_id' => $p );
                        }

                    }
                }

                foreach ( $path as $key => $item )
                {
                    if ( isset( $item['node_id'] ) )
                    {
                        if ( $this->get_area_tematica_node( $item['node_id'] ) )
                        {
                            $customStyle = $this->get_area_tematica_style( $item['node_id'] );
                            if ( !empty( $customStyle ) )
                            {
                                $result = 'aree/' . $customStyle . '.css';
                                break;
                            }
                        }
                    }
                }

                $operatorValue = $result;

            } break;

            case 'openpaini':
            {
                $result = OpenPAINI::variable( $namedParameters['block'], $namedParameters['setting'], $namedParameters['default'] );
                $operatorValue = $result;

            } break;

            case 'is_dipendente':
            {
                $currentUser = eZUser::currentUser();
                $gruppoDipendenti = $ini->hasVariable( 'ControlloUtenti', 'GruppoDipendenti' ) ? $ini->variable( 'ControlloUtenti', 'GruppoDipendenti' ) : array();
                $gruppoAmministratori = $ini->hasVariable( 'ControlloUtenti', 'GruppoAmministratori' ) ? $ini->variable( 'ControlloUtenti', 'GruppoAmministratori' ) : array( 12 );

                $groups = $currentUser->groups();

                $return = false;

                if ( in_array( $gruppoDipendenti, $groups ) )
                {
                    $return = true;
                }

                if ( in_array( $gruppoAmministratori, $groups ) )
                {
                    $return = true;
                }

                $operatorValue = $return;

            } break;

            case 'openpa_shorten':
            {
                $operatorValue = strip_tags( $operatorValue );
                $strlenFunc = function_exists( 'mb_strlen' ) ? 'mb_strlen' : 'strlen';
                //$substrFunc = function_exists( 'mb_substr' ) ? 'mb_substr' : 'substr';
                if ( $strlenFunc( $operatorValue ) > $namedParameters['chars_to_keep'] )
                {
                    $operatorLength = $strlenFunc( $operatorValue );

                    if ( $namedParameters['trim_type'] === 'middle' )
                    {
                        $appendedStrLen = $strlenFunc( $namedParameters['str_to_append'] );

                        if ( $namedParameters['chars_to_keep'] > $appendedStrLen )
                        {
                            $chop = $namedParameters['chars_to_keep'] - $appendedStrLen;

                            $middlePos = (int)($chop / 2);
                            $leftPartLength = $middlePos;
                            $rightPartLength = $chop - $middlePos;

                            $operatorValue = trim( $this->custom_substr( $operatorValue, 0, $leftPartLength ) . $namedParameters['str_to_append'] . $this->custom_substr( $operatorValue, $operatorLength - $rightPartLength, $rightPartLength ) );
                        }
                        else
                        {
                            $operatorValue = $namedParameters['str_to_append'];
                        }
                    }
                    else // default: trim_type === 'right'
                    {
                        $chop = $namedParameters['chars_to_keep'] - $strlenFunc( $namedParameters['str_to_append'] );
                        $operatorValue = $this->custom_substr( $operatorValue, 0, $chop );
                        $operatorValue = trim( $operatorValue );
                        if ( $operatorLength > $chop )
                            $operatorValue = $operatorValue.$namedParameters['str_to_append'];
                    }
                }


            } break;

            case 'has_abstract':
            case 'abstract':
            {
                $has_content = false;
                $text = false;
                $node = $namedParameters['node'];

                if ( !$node )
                    $node = $operatorValue;

                if ( is_numeric( $node ) )
                {
                    $node = OpenPABase::fetchNode( $node );
                }

                if ( $node instanceof eZContentObjectTreeNode )
                {
                    if ( $node->hasAttribute( 'highlight' ) )
                    {
                        $text = $node->attribute( 'highlight' );
                        $text = str_replace( '&amp;nbsp;', ' ', $text );

                        if ( strlen( $text ) > 0 )
                        {
                            $has_content = true;
                        }
                    }

                    if ( !$has_content )
                    {
                        $attributes = $ini->hasVariable( 'Attributi', 'AttributiAbstract' ) ? $ini->variable( 'Attributi', 'AttributiAbstract' ) : array();
                        if ( !empty( $attributes ) )
                        {
                            $dataMap = $node->dataMap();
                            foreach ( $attributes as $attr )
                            {
                                if ( isset( $dataMap[$attr] ) )
                                {
                                    if ( $dataMap[$attr]->hasContent() )
                                    {
                                        $has_content = true;
                                        $tpl = eZTemplate::factory();
                                        $tpl->setVariable( 'attribute', $dataMap[$attr] );
                                        $designPath = "design:content/datatype/view/" . $dataMap[$attr]->attribute( 'data_type_string' ) . ".tpl";
                                        $text = $tpl->fetch( $designPath );
                                        break;
                                    }
                                }

                            }
                        }
                    }
                }

                if ( $operatorName == 'has_abstract' )
                    return $operatorValue = $has_content;
                else
                    return $operatorValue = $text;

            } break;
        }
    }

    private function custom_substr( $string, $start, $length )
    {
        if( strlen( $string ) > $length )
        {
            $substr = substr( $string, $start, $length );
            if ( $start == 0 )
            {
                $lastSpace = strrpos( $substr, " " );
                $string = substr( $substr, 0, $lastSpace );
            }
            else
            {
                $firstSpace = strpos( $substr, " " );
                $string = substr( $substr, $firstSpace, $length );
            }
        }
        return $string;
    }

    private function get_area_tematica_node( $nodeID = 0 )
    {
        if ( !in_array( $nodeID, $this->area_tematica_node ) )
        {
            $ini = eZINI::instance( 'openpa.ini' );
            $areeIdentifiers = $ini->hasVariable( 'AreeTematiche', 'IdentificatoreAreaTematica' ) ? $ini->variable( 'AreeTematiche', 'IdentificatoreAreaTematica' ) : array( 'area_tematica' );
            $node = OpenPABase::fetchNode( $nodeID );

            $return = false;

            if ( $node )
            {
                if ( in_array( $node->attribute( 'class_identifier' ), $areeIdentifiers )
                     && $nodeID != eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' )
                     && strpos( eZINI::instance()->variable( 'SiteSettings', 'IndexPage' ), $nodeID ) === false)
                {
                    $return = $node;
                }
            }

        }
        $this->area_tematica_node[$nodeID] = $return;
        return $this->area_tematica_node[$nodeID];
    }

    private function get_area_tematica_style( $nodeID = 0 )
    {
        $node = $this->get_area_tematica_node( $nodeID );
        $ini = eZINI::instance( 'openpa.ini' );
        $stileAreaAttribute = $ini->hasVariable( 'AreeTematiche', 'IdentificatoreStileAreaTematica' ) ? $ini->variable( 'AreeTematiche', 'IdentificatoreStileAreaTematica' ) : 'stile';

        if ( $node )
        {
            $datdMap = $node->dataMap();
            if ( isset( $datdMap[$stileAreaAttribute] ) )
            {
                return $datdMap[$stileAreaAttribute]->toString();
            }
            return false;
        }
        return false;
    }

    public function currentObjectId()
    {
        if ( self::$currentObjectId === null )
        {
            self::$currentObjectId = 0;
            $globalParams = $GLOBALS['eZRequestedModuleParams'];
            if ( $globalParams['module_name'] == 'content' && $globalParams['function_name'] == 'view'  )
            {
                $currentNodeId = isset( $globalParams['parameters']['NodeID'] ) ? $globalParams['parameters']['NodeID'] : false;
                $currentObject = eZContentObject::fetchByNodeID( $currentNodeId, false );
                if ( is_array( $currentObject ) )
                {
                    self::$currentObjectId = $currentObject['id'];
                }
            }
        }
        return self::$currentObjectId;
    }

    /**
     * @return null|int
     */
    private static function getTrasparenzaRootNodeId()
    {
        if (self::$trasparenzaRootNodeId === null) {

            self::$trasparenzaRootNodeId = false;

            /** @var eZContentObjectTreeNode[] $trasparenzaList */
            $trasparenzaList = eZContentObjectTreeNode::subTreeByNodeID(
                array(
                    'ClassFilterType' => 'include',
                    'ClassFilterArray' => array('trasparenza'),
                    'Limit' => 1
                ), 1
            );

            if (count($trasparenzaList) > 0) {
                self::$trasparenzaRootNodeId = $trasparenzaList[0]->attribute('main_node_id');
            }
        }

        return self::$trasparenzaRootNodeId;
    }

    private static function getSearchData()
    {
        if (self::$searchData === null){
            self::$searchData = OpenPAPageData::getSearchDataCache()->processCache(
                function ($file) {
                    $content = include($file);
                    return $content;
                },
                function () {
                    eZDebug::writeNotice("Regenerate search_data cache", 'OpenPAOperator::getSearchExclude');

                    $excludeFacets = array();
                    $excludeClasses = array();
                    $includeClasses = array();

                    $iniNotAvailableFacets  = OpenPAINI::variable( 'MotoreRicerca', 'faccette_non_disponibili', array() );
                    $iniNotAvailableFacetsGroups = OpenPAINI::variable( 'MotoreRicerca', 'gruppi_faccette_non_disponibili', array() );
                    $classesNotAvailable    = OpenPAINI::variable( 'MotoreRicerca', 'classi_non_disponibili', array() );
                    $classGroupNotAvailable = OpenPAINI::variable( 'MotoreRicerca', 'gruppi_classi_non_disponibili', array() );

                    $classes = eZPersistentObject::fetchObjectList( eZContentClass::definition(), null, array( "version" => eZContentClass::VERSION_STATUS_DEFINED ) );
                    foreach ( $classes as $class )
                    {
                        if ( in_array( $class->attribute('id'), $iniNotAvailableFacets )
                            || strpos( $class->attribute( 'identifier' ), 'tipo' ) === 0
                            || count( array_intersect( $iniNotAvailableFacetsGroups, $class->attribute( 'ingroup_id_list' ) ) ) > 0 )
                        {
                            $excludeFacets[$class->attribute('id')] = $class->attribute('identifier');
                        }

                        if ( in_array( $class->attribute('id'), $classesNotAvailable )
                            || count( array_intersect( $classGroupNotAvailable, $class->attribute( 'ingroup_id_list' ) ) ) > 0 )
                        {
                            $excludeClasses[$class->attribute('id')] = $class->attribute('identifier');
                        }
                        else
                        {
                            $includeClasses[$class->attribute('id')] = $class->attribute('identifier');
                        }

                    }

                    $result = array(
                        'exclude_facets' => $excludeFacets,
                        'exclude_classes' => $excludeClasses,
                        'include_classes' => $includeClasses
                    );

                    return array(
                        'content' => $result,
                        'scope' => 'cache',
                        'datatype' => 'php',
                        'store' => true
                    );
                }
            );
        }

        return self::$searchData;
    }

}
