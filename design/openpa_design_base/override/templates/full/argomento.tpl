{ezpagedata_set( 'extra_menu', false() )}
{def $sortString = 'published-desc'
     $default_filters = array( concat( 'submeta_argomento___id_si:', $node.contentobject_id ) )
     $facets = array(        
        hash( 'field', 'meta_class_identifier_ms', 'name', 'Tipologia_di_contenuto', 'limit', 100, 'sort', 'alpha' )
     )
}

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