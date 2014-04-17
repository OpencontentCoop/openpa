<?php

class OpenPaFunctionCollection
{

    protected static $topmenu;
    protected static $home;

    public static $remoteHeader = 'OpenPaHeader';
    public static $remoteLogo = 'OpenPaLogo';
    public static $remoteRoles = 'OpenPaRuoli';

    protected static $params = array(
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
    
    protected static function search( $params, $query = '' )
    {
        $solrSearch = new eZSolr();
        return $solrSearch->search( $query, $params );
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

    public static function fetchRuoli( $struttura, $dipendente )
    {
        $params = self::$params;
        $params['SearchSubTreeArray'] = array( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ),
                                               eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' ) );
        $params['SearchContentClassID'] = array( 'ruolo' );    
        if ( $struttura || $dipendente )
        {
            if ( $struttura )
                $params['Filter'][] = array( 'submeta_struttura_di_riferimento___id_si:' . $struttura );
            elseif( $dipendente )
                $params['Filter'][] = array( 'submeta_utente___id_si:' . $dipendente );
        }
        $search = self::search( $params );        
        return array( 'result' => $search['SearchResult'] );
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
        $params['SearchContentClassID'] = array( 'dipendente' );
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
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
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
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
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
    
    protected static function fetchHeaderImage()
    {
        $result = false;
        $homePage = self::fetchHome();
        if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
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

    
    public static function fetchHome()
    {
        if ( self::$home == null )
        {
            //eZDebug::writeNotice( 'Fetch home' );
            self::$home = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
        }
        return self::$home;
    }
    
    public static function fetchTopMenuNodes()
    {
        if ( self::$topmenu == null )
        {            
            $homePage = self::fetchHome();
            if ( $homePage->attribute( 'class_identifier' ) == 'homepage' )
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
        }
        return self::$topmenu;
    }
    
    
}

?>
