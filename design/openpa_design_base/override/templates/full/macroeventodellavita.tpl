{ezpagedata_set( 'extra_menu', false() )}
{if $node.class_identifier|eq( 'macroeventodellavita' )}
    {def $sortString = 'published-desc'
         $default_filters = array( array( 'or',
                                   concat( 'submeta_macroevento_vita___id_si:', $node.contentobject_id ),
                                   concat( 'submeta_evento_vita___main_parent_node_id_si:', $node.node_id )
                                   ) )
         $facets = array(        
            hash( 'field', 'meta_class_identifier_ms', 'name', 'Tipologia_di_contenuto', 'limit', 100, 'sort', 'alpha' ),
            hash( 'field', 'submeta_argomento___id_si', 'name', 'Argomento', 'limit', 100, 'sort', 'alpha' ),
            hash( 'field', 'submeta_evento_vita___id_si', 'name', 'Evento_della_vita', 'limit', 100, 'sort', 'alpha' )
         )}

{elseif $node.class_identifier|eq( 'eventodellavita' )}
    {def $sortString = 'published-desc'
         $default_filters = array( concat( 'submeta_evento_vita___id_si:', $node.contentobject_id ) )
         $facets = array(        
            hash( 'field', 'meta_class_identifier_ms', 'name', 'Tipologia_di_contenuto', 'limit', 100, 'sort', 'alpha' ),
            hash( 'field', 'submeta_argomento___id_si', 'name', 'Argomento', 'limit', 100, 'sort', 'alpha' )
         )}

{else}
    {def $sortString = 'published-desc'
         $default_filters = array( concat( 'submeta_io_sono___id_si:', $node.contentobject_id ) )
         $facets = array(        
            hash( 'field', 'meta_class_identifier_ms', 'name', 'Tipologia_di_contenuto', 'limit', 100, 'sort', 'alpha' ),
            hash( 'field', 'submeta_argomento___id_si', 'name', 'Argomento', 'limit', 100, 'sort', 'alpha' )
         )}

{/if}

{include name=folder_facet
         uri='design:content/facetsearch.tpl'
         node=$node
         subtree=array( ezini( 'NodeSettings', 'RootNode', 'content.ini' ) )
         facets=$facets
         classes=false()
         default_filters=$default_filters
         useDateFilter=true()
         view_parameters=$view_parameters
         sortString=$sortString}