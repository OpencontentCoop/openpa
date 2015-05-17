<?php

class OpenPAOperator
{
    
    private $area_tematica_node = array();
    
    private static $currentObjectId;
    
    function OpenPAOperator()
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
            'site_identifier'
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
            'openpaini' => array
            (
                'block' 	    => array( 'type' => 'string', 'required' => true ),
                'setting' 	    => array( 'type' => 'string', 'required' => true ),
                'default' 	    => array( 'type' => 'mixed', 'required' => false, 'default' => false )
            ),
            'has_main_style' => array
            (
                'node'          => array( 'type' => 'mixed', 'required' => true )
            ),
            'openpa_shorten' => array
            (
                'chars_to_keep' => array( "type" => "integer", "required" => false, "default" => 80 ),
                'str_to_append' => array( "type" => "string", "required" => false, "default" => "..." ),
                'trim_type'     => array( "type" => "string", "required" => false, "default" => "right" )
            ),
            'has_abstract' => array
            (
                'node' => array( "type" => "integer", "required" => false, "default" => false )
            ),
            'abstract' => array
            (
                'node' => array( "type" => "integer", "required" => false, "default" => false )
            ),
            'rss_list' => array
            (
                'fetchList' => array( "type" => "string", "required" => true, "default" => 'export' )
            ),
            'materia_make_tree' => array
            (
                'relation_list' => array( "type"  => "array", "required" => true, "default" => array() )
            ),
            'find_first_parent' => array
            (
                'class' => array( "type"  => "mixed", "required" => true, "default" => null )
            )
        );
    }
    
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
            }
            
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

}

?>
