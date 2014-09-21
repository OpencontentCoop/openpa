<?php

class BlockHandlerLista extends OpenPABlockHandler
{
    /**
     * @var array
     */
    protected $fetchParameters = array();

    /**
     * @var eZContentObjectTreeNode
     */
    protected $currentSubTreeNode;

    protected function run()
    {
        $this->data['fetch_parameters'] = $this->getFetchParameters();
        $this->data['content'] = $this->getContent();
    }

    protected function getContent()
    {
        $data = array();
        $params = array(
            'SearchLimit' => $this->solrFetchParameter( 'SearchLimit' ),
            'Filter' => $this->solrFetchParameter( 'Filter' ),
            'SortBy' => $this->solrFetchParameter( 'SortBy' ),
        );
        $search = OpenPaFunctionCollection::search( $params );
        if ( $search['SearchCount'] > 0 )
        {
            $data = $search['SearchResult'];
        }
        //eZDebug::writeDebug( $params, __METHOD__ );
        return $data;
    }

    protected function solrFetchParameter( $key )
    {
        $data = null;

        switch( $key )
        {
            case "SearchLimit":
                if ( isset( $this->fetchParameters['limit'] ) )
                {
                    $data = $this->fetchParameters['limit'];
                }
                break;

            case "Filter":
                $filter = array();
                
                $defaultFilter = array();                
                foreach( $this->fetchParameters['subtree_array'] as $subtree )
                {
                    $defaultFilter[] = "meta_path_si:" . intval( $subtree );
                    $defaultFilter[] = "-meta_node_id_si:" . intval( $subtree );
                }
                if ( isset( $this->fetchParameters['class_filter_array'] )
                     && !empty( $this->fetchParameters['class_filter_array'] ))
                {
                    $filterType = $this->fetchParameters['class_filter_type'] == 'exclude' ? '-' : '';
                    
                    foreach( $this->fetchParameters['class_filter_array'] as $class )
                    {
                        if ( !empty( $class ) )
                            $defaultFilter[] =  $filterType . "meta_class_identifier_ms:" . $class;
                    }                    
                }

                if ( $this->fetchParameters['class_filter_type'] == 'include'
                     && in_array( 'news', $this->fetchParameters['class_filter_array'] ) )
                {
                    //@todo filtri su data di pubblicazione
                }


                if ( isset( $this->fetchParameters['depth'] )
                     && !empty( $this->fetchParameters['depth'] ))
                {
                    $defaultFilter[] = "meta_depth_si:[* TO {$this->fetchParameters['depth']}]";
                }
                
                if ( isset( $this->fetchParameters['virtual_subtree_array'] )
                     || isset( $this->fetchParameters['virtual_classes'] ) )
                {
                    $virtualFilter = array();
                    if ( isset( $this->fetchParameters['virtual_subtree_array']  ) )
                    {
                        foreach( $this->fetchParameters['virtual_subtree_array'] as $subtree )
                        {
                            $virtualFilter[] = "meta_path_si:" . intval( $subtree );
                        }
                    }
                    foreach( $this->fetchParameters['virtual_classes'] as $class )
                    {
                        $virtualFilter[] =  "meta_class_identifier_ms:" . $class;
                    }
                    
                    $filter = $defaultFilter;
                    if ( !empty( $virtualFilter ) )
                    {
                        $filter[] = array( 'or', $defaultFilter, $virtualFilter );
                    }
                }
                $data = $filter;
                break;

            case "SortBy":
                if ( isset( $this->fetchParameters['sort_array'] ) )
                {
                    $sortArray = $this->fetchParameters['sort_array'];
                    $sortOrder = $sortArray[1] ? 'asc' : 'desc';
                    $orderBy = false;
                    switch( $sortArray[0] )
                    {
                        case 'nome':
                        case 'name':
                            $orderBy = 'name';
                            break;

                        case 'priority':
                            eZDebug::writeNotice( "Priority non ammesso in ordinamento ezfind", __METHOD__ );
                            $orderBy = 'name';
                            break;

                        case 'modified':
                        case 'published':
                            $orderBy = $sortArray[0];
                            break;
                    }
                    if ( $orderBy )
                        $data = array( $orderBy => $sortOrder );
                }
                break;

            default:
                break;
        }
        return $data;
    }

    protected function getFetchParameters()
    {
        if ( isset( $this->currentCustomAttributes['node_id'] ) )
        {
            $this->currentSubTreeNode = OpenPABase::fetchNode( $this->currentCustomAttributes['node_id'] );

            if ( $this->currentSubTreeNode instanceof eZContentObjectTreeNode )
            {
                $this->data['root_node'] = $this->currentSubTreeNode;
                $this->fetchParameters['subtree_array'] = array( $this->currentSubTreeNode->attribute( 'node_id' ) );

                $objectHandler = OpenPAObjectHandler::instanceFromObject( $this->currentSubTreeNode );
                $virtualService = $objectHandler->attribute( 'content_virtual' );

                foreach( $virtualService->attributes() as $id )
                {                                        
                    if ( $id != 'template' )
                    {
                        $value = $virtualService->attribute( $id );                        
                        $this->fetchParameters['virtual_subtree_array'] = $value['subtree'];
                        $this->fetchParameters['virtual_classes'] = isset( $value['classes'] ) ? $value['classes'] : array();
                        break;
                    }
                }                
            }
        }

        $this->fetchParameters['class_filter_type'] = 'exclude';
        if ( OpenPAINI::variable( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', false ) )
        {
            $this->fetchParameters['class_filter_array'] = OpenPAINI::variable( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow' );
        }
        foreach( $this->currentCustomAttributes as $key => $value )
        {
            $this->mapParameter( $key, $value );
        }
        return $this->fetchParameters;
    }

    protected function mapParameter( $key, $value )
    {
        switch( $key )
        {
            case 'livello_profondita':
                $this->fetchParameters['depth'] = $value;
                break;

            case 'limite':
                $this->fetchParameters['limit'] = $value;
                break;

            case 'includi_classi':
                $classes = explode( ',', $value );
                $classes = array_map( 'trim', $classes );
                if ( !empty( $classes ) )
                {
                    $this->fetchParameters['class_filter_type'] = 'include';
                    $this->fetchParameters['class_filter_array'] = $classes;
                }
                break;

            case 'escludi_classi':
                $classes = explode( ',', $value );
                $classes = array_map( 'trim', $classes );
                if ( !empty( $classes ) && !isset( $this->fetchParameters['class_filter_type'] ) )
                {
                    $this->fetchParameters['class_filter_type'] = 'exclude';
                    $this->fetchParameters['class_filter_array'] = array_merge(
                        $classes,
                        OpenPAINI::variable( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', array() )
                    );
                }
                break;

            case 'ordinamento':
            {
                switch( $value )
                {
                    case 'priority':
                        $this->fetchParameters['sort_array'] = array( 'priority', true );
                        break;
                    case 'published':
                        $this->fetchParameters['sort_array'] = array( 'published', false );
                        break;
                    case 'modified':
                        $this->fetchParameters['sort_array'] = array( 'modified', false );
                        break;
                    case 'nome':
                    case 'name':
                    $this->fetchParameters['sort_array'] = array( 'name', true );
                        break;
                    default:
                        if ( $this->currentSubTreeNode instanceof eZContentObjectTreeNode )
                        {
                            $this->fetchParameters['sort_array'] = $this->currentSubTreeNode->attribute( 'sort_array' );
                        }
                        break;
                }
            }
        }
    }
}