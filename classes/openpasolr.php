<?php


class OpenPASolr extends eZSolr
{

    function search( $searchText, $params = array(), $searchTypes = array() )
    {
        eZDebug::createAccumulator( 'Search', 'eZ Find' );
        eZDebug::accumulatorStart( 'Search' );
        $error = 'Server not running';

        $asObjects = isset( $params['AsObjects'] ) ? $params['AsObjects'] : true;

        //distributed search: fields to return can be specified in 2 parameters
        $params['FieldsToReturn'] = isset( $params['FieldsToReturn'] ) ? $params['FieldsToReturn'] : array();
        if ( isset( $params['DistributedSearch']['returnfields'] ) )
        {
            $params['FieldsToReturn'] = array_merge( $params['FieldsToReturn'], $params['DistributedSearch']['returnfields'] );

        }

        $coreToUse = null;
        $shardQueryPart = null;
        if ( $this->UseMultiLanguageCores === true )
        {
            $languages = $this->SiteINI->variable( 'RegionalSettings', 'SiteLanguageList' );
            if ( array_key_exists ( $languages[0], $this->SolrLanguageShards ) )
            {
                $coreToUse = $this->SolrLanguageShards[$languages[0]];
                if ( $this->FindINI->variable( 'LanguageSearch', 'SearchMainLanguageOnly' ) <> 'enabled' )
                {
                    $shardQueryPart = array( 'shards' => implode( ',', $this->SolrLanguageShardURIs ) );
                }
            }
            //eZDebug::writeNotice( $languages, __METHOD__ . ' languages' );
            eZDebug::writeNotice( $shardQueryPart, __METHOD__ . ' shards' );
            //eZDebug::writeNotice( $this->SolrLanguageShardURIs, __METHOD__ . ' this languagesharduris' );
        }
        else
        {
            $coreToUse = $this->Solr;
        }


        if ( $this->SiteINI->variable( 'SearchSettings', 'AllowEmptySearch' ) == 'disabled' &&
             trim( $searchText ) == '' )
        {
            $error = 'Empty search is not allowed.';
            eZDebug::writeNotice( $error, __METHOD__ );
            $resultArray = null;
        }

        else
        {
            eZDebug::createAccumulator( 'Query build', 'eZ Find' );
            eZDebug::accumulatorStart( 'Query build' );
            $queryBuilder = new ezfeZPSolrQueryBuilder( $this );
            $queryParams = $queryBuilder->buildSearch( $searchText, $params, $searchTypes );
            if ( !$shardQueryPart == null )
            {
                $queryParams = array_merge( $shardQueryPart, $queryParams );
            }
            eZDebug::accumulatorStop( 'Query build' );
            eZDebugSetting::writeDebug( 'extension-ezfind-query', $queryParams, 'Final query parameters sent to Solr backend' );

            eZDebug::createAccumulator( 'Engine time', 'eZ Find' );
            eZDebug::accumulatorStart( 'Engine time' );
            $resultArray = $coreToUse->rawSearch( $queryParams );
            eZDebug::accumulatorStop( 'Engine time' );
        }

        if ( $resultArray )
        {
            $searchCount = $resultArray[ 'response' ][ 'numFound' ];
            $objectRes = $this->buildResultObjects(
                $resultArray, $searchCount, $asObjects, $params
            );

            $stopWordArray = array();
            eZDebug::accumulatorStop( 'Search' );
            return array(
                'SearchResult' => $objectRes,
                'SearchCount' => $searchCount,
                'StopWordArray' => $stopWordArray,
                'SearchExtras' => new ezfSearchResultInfo( $resultArray )
            );
        }
        else
        {
            eZDebug::accumulatorStop( 'Search' );
            return array(
                'SearchResult' => false,
                'SearchCount' => 0,
                'StopWordArray' => array(),
                'SearchExtras' => new ezfSearchResultInfo( array( 'error' => ezpI18n::tr( 'ezfind', $error ) ) ) );
        }
    }

    protected function buildResultObjects( $resultArray, &$searchCount, $asObjects = true, $params = array() )
    {
        $objectRes = array();
        $highLights = array();
        if ( !empty( $resultArray['highlighting'] ) )
        {
            foreach ( $resultArray['highlighting'] as $id => $highlight )
            {
                $highLightStrings = array();
                //implode apparently does not work on associative arrays that contain arrays
                //$element being an array as well
                foreach ( $highlight as $key => $element )
                {
                    $highLightStrings[] = implode( ' ', $element);
                }
                $highLights[$id] = implode( ' ...  ', $highLightStrings);
            }
        }
        if ( !empty( $resultArray ) )
        {
            $result = $resultArray['response'];
            if ( !is_array( $result ) ||
                 !isset( $result['maxScore'] ) ||
                 !isset( $result['docs'] ) ||
                 !is_array( $result['docs'] ) )
            {
                eZDebug::writeError( 'Unexpected response from Solr: ' . var_export( $result, true ), __METHOD__ );
                return $objectRes;
            }

            $maxScore = $result['maxScore'];
            $docs = $result['docs'];
            $localNodeIDList = array();
            $nodeRowList = array();

            // Loop through result, and get eZContentObjectTreeNode ID
            foreach ( $docs as $idx => $doc )
            {
                if ( $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'installation_id' )] == self::installationID() )
                {
                    $localNodeIDList[] = $this->getNodeID( $doc );
                }
            }

            if ( !empty( $localNodeIDList ) )
            {
                $tmpNodeRowList = eZContentObjectTreeNode::fetch( $localNodeIDList, false, false );
                // Workaround for eZContentObjectTreeNode::fetch behaviour
                if ( count( $localNodeIDList ) === 1 )
                {
                    $tmpNodeRowList = array( $tmpNodeRowList );
                }
                if ( $tmpNodeRowList )
                {
                    foreach ( $tmpNodeRowList as $nodeRow )
                    {
                        $nodeRowList[$nodeRow['node_id']] = $nodeRow;
                    }
                }
                unset( $tmpNodeRowList );
            }

            //need refactoring from the moment Solr has globbing in fl parameter
            foreach ( $docs as $idx => $doc )
            {
                if ( !$asObjects )
                {
                    $emit = array();
                    foreach ( $doc as $fieldName => $fieldValue )
                    {
                        // check if field is not in the explicit field list, to keep explode from generating notices.
                        if ( strpos( $fieldName, '_' ) !== false )
                        {
                            list( $prefix, $rest ) = explode( '_', $fieldName, 2 );
                            // get the identifier for meta, binary fields
                            $inner = implode( '_', explode( '_', $rest, -1 ) );
                            if ( $prefix === 'meta' )
                            {
                                $emit[$inner] = $fieldValue;
                            }
                            elseif ( $prefix === 'as' )
                            {
                                $emit['data_map'][$inner] = ezfSolrStorage::unserializeData( $fieldValue );
                            }
                        }
                        // it may be a field originating from the explicit fieldlist to return, so it should be added for template consumption
                        // note that the fieldname will be kept verbatim in a substructure 'fields'
                        //@patch @luca else
                        if( in_array( $fieldName, $params['FieldsToReturn'] ) )
                        {
                            $emit['fields'][$fieldName] = $fieldValue;
                        }

                    }
                    $emit['highlight'] = isset( $highLights[$doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'guid' )]] ) ?
                        $highLights[$doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'guid' )]] : null;
                    $emit['elevated'] = ( isset($doc['[elevated]']) ? $doc['[elevated]'] === true : false );
                    $objectRes[] = $emit;
                    unset( $emit );
                    continue;
                }
                elseif ( $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'installation_id' )] == self::installationID() )
                {
                    // Search result document is from current installation
                    $nodeID = $this->getNodeID( $doc );

                    // no actual $nodeID, may ocurr due to subtree/visibility limitations.
                    if ( $nodeID === null )
                        continue;

                    // Invalid $nodeID
                    // This can happen if a content has been deleted while Solr was not running, provoking desynchronization
                    if ( !isset( $nodeRowList[$nodeID] ) )
                    {
                        $searchCount--;
                        eZDebug::writeError( "Node #{$nodeID} (/{$doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'main_url_alias' )]}) returned by Solr cannot be found in the database. Please consider reindexing your content", __METHOD__ );
                        continue;
                    }

                    $resultTree = new eZFindResultNode( $nodeRowList[$nodeID] );
                    $node = $nodeRowList[$nodeID];
                    $resultTree->setContentObject(
                        new eZContentObject(
                            array(
                                "id" => $node["id"],
                                "section_id" => $node["section_id"],
                                "owner_id" => $node["owner_id"],
                                "contentclass_id" => $node["contentclass_id"],
                                "name" => $node["name"],
                                "published" => $node["published"],
                                "modified" => $node["modified"],
                                "current_version" => $node["current_version"],
                                "status" => $node["status"],
                                "remote_id" => $node["object_remote_id"],
                                "language_mask" => $node["language_mask"],
                                "initial_language_id" => $node["initial_language_id"],
                                "class_identifier" => $node["class_identifier"],
                                "serialized_name_list" => $node["class_serialized_name_list"],
                            )
                        )
                    );
                    $resultTree->setAttribute( 'is_local_installation', true );
                    // can_read permission must be checked as they could be out of sync in Solr, however, when called from template with:
                    // limitation, hash( 'accessWord', ... ) this check should not be performed as it has precedence.
                    // See: http://issues.ez.no/15978
                    if ( !isset( $params['Limitation'], $params['Limitation']['accessWord'] ) && !$resultTree->attribute( 'object' )->attribute( 'can_read' ) )
                    {
                        $searchCount--;
                        eZDebug::writeNotice( 'Access denied for eZ Find result, node_id: ' . $nodeID, __METHOD__ );
                        continue;
                    }

                    $urlAlias = $this->getUrlAlias( $doc );
                    $globalURL = $urlAlias . '/(language)/' . $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'language_code' )];
                    eZURI::transformURI( $globalURL );
                }
                else
                {
                    $resultTree = new eZFindResultNode();
                    $resultTree->setAttribute( 'is_local_installation', false );
                    $globalURL = $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'installation_url' )] .
                                 $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'main_url_alias' )] .
                                 '/(language)/' . $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'language_code' )];
                }

                $resultTree->setAttribute( 'name', $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'name' )] );
                $resultTree->setAttribute( 'published', $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'published' )] );
                $resultTree->setAttribute( 'global_url_alias', $globalURL );
                $resultTree->setAttribute( 'highlight', isset( $highLights[$doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'guid' )]] ) ?
                    $highLights[$doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'guid' )]] : null );
                /**
                 * $maxScore may be equal to 0 when the QueryElevationComponent is used.
                 * It returns as first results the elevated documents, with a score equal to 0. In case no
                 * other document than the elevated ones are returned, maxScore is then 0 and the
                 * division below raises a warning. If maxScore is equal to zero, we can safely assume
                 * that only elevated documents were returned. The latter have an articifial relevancy of 100%,
                 * which must be reflected in the 'score_percent' attribute of the result node.
                 */
                $maxScore != 0 ? $resultTree->setAttribute( 'score_percent', (int) ( ( $doc['score'] / $maxScore ) * 100 ) ) : $resultTree->setAttribute( 'score_percent', 100 );
                $resultTree->setAttribute( 'language_code', $doc[ezfSolrDocumentFieldBase::generateMetaFieldName( 'language_code' )] );
                $resultTree->setAttribute( 'elevated', ( isset($doc['[elevated]']) ? $doc['[elevated]'] === true : false ) );
                $objectRes[] = $resultTree;
            }
        }
        return $objectRes;
    }

    public static function generateSolrField( $identifier, $type )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName( ezfSolrDocumentFieldBase::ATTR_FIELD_PREFIX . $identifier, $type );
    }

    public static function generateSolrSubMetaField( $identifier, $subIdentifier )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName(
            ezfSolrDocumentFieldBase::SUBMETA_FIELD_PREFIX . $identifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . $subIdentifier,
            eZSolr::getMetaAttributeType( $subIdentifier ) );
    }

    public static function generateSolrSubField( $identifier, $subIdentifier, $type )
    {
        $DocumentFieldName = new ezfSolrDocumentFieldName();
        return $DocumentFieldName->lookupSchemaName(
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_PREFIX . $identifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR . $subIdentifier .
            ezfSolrDocumentFieldBase::SUBATTR_FIELD_SEPARATOR,
            $type );
    }
}