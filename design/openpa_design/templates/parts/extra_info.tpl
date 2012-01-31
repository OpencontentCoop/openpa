{set-block variable=$open}
<div class="block content-view-block">
{/set-block}
{set-block variable=$close}
</div>
{/set-block}

{def $global_layout_class = fetch( 'content', 'class', hash( 'class_id', 'global_layout' ) )
     $module_result_node = fetch( 'content', 'node', hash('node_id', $module_result.node_id) )
	 $related_content = false()
     $mlt_enabled = openpaini( 'MoreLikeThis', 'AbilitaInExtraInfo', 'enabled' )
     $mlt_classes = openpaini( 'Classi', 'MoreLikeThisDisabilita', array() )
     $documenti_struttura = openpaini( 'Classi', 'ExtraInfoMostraDocmentiPerStruttura', array() )
     $enabled_container = openpaini( 'Classi', 'ExtraInfoCustom', 'content.ini')
     $mostra_doc_per_struttura = false()
     $mostra_doc_per_struttura = false()
     $folder = false()
     $classes=fetch( 'class', 'list' )
     $mlt_class_ids = array()
     $mlt_exclude_classes = openpaini( 'Classi', 'MoreLikeThisEscludiDaiRisultati', array() )}          

{foreach $classes as $class}
    {if $mlt_exclude_classes|contains( $class.identifier )|not()}    
        {set $mlt_class_ids = $mlt_class_ids|merge( array( $class.id ) )}
    {/if}
{/foreach}

{if $documenti_struttura|contains($module_result_node.class_identifier)}
	{set $mostra_doc_per_struttura = $module_result_node.class_identifier}
{/if}
{if $enabled_container|contains( $module_result_node.class_identifier )}
	{if is_set($module_result_node.data_map.classi_filtro)}
		{if $module_result_node.data_map.classi_filtro.has_content}
			{set $folder = $module_result_node}
		{/if}
	{/if}
{elseif $enabled_container|contains($module_result_node.parent.class_identifier)}
	{set $folder = $module_result_node.parent}
{/if}

{*
	CALENDARIO
*}
{if $module_result_node.class_identifier|eq('event')}
	{$open}
		{include 
			node=$module_result_node			
			uri='design:parts/calendar.tpl'}
	{$close}
{/if}

{* 
	BLOCCO DI RICERCA
	compare solo nei folder e negli oggetti con padre folder
	qualora il campo 'engine' sia valorizzato la ricerca viene estesa a tutto il database
*}
{if and( $folder, is_set( $folder.data_map.classi_filtro ) )}
    {$open}
    
    {def $class_filters = $folder.data_map.classi_filtro.content|explode(',')}
    {if and( count( $class_filters )|eq(1), $class_filters[0]|ne('') )}
    
		{include 
			name=searchbox node=$module_result_node 
			folder=$folder.name|wash()
			class_filters=$class_filters
			uri='design:parts/openpa/search_class_and_attributes.tpl' }
    
    {elseif count( $class_filters )|gt(1)}
    
        {include 
			name=searchbox node=$module_result_node 
			folder=$folder.name|wash()
			class_filters=$class_filters
			uri='design:parts/openpa/search_classes_and_attributes.tpl' }
    {/if}
	{$close}
{/if}


{*
	BLOCCO PER LE STRUTTURE
*}

{if $mostra_doc_per_struttura}
{set-block variable=$blocco_strutture}		
{include name=documenti 
         node=$module_result_node
         classe_filtro = $mostra_doc_per_struttura
         uri='design:parts/openpa/documenti_per_struttura.tpl' }
{/set-block}			
	{if ne($blocco_strutture|trim(), '')}
		{$open}
		{$blocco_strutture}
		{$close}
	{/if}
{/if}

{*
	COLONNA DEFINITA NEL EZFLOW DEL FOLDER (O DELL ANTENATO)
*}
{$open}
{def 	$has_boxes=false() 
		$has_boxes_folder=false()
		$global_layout=array()
        $layout1zone = '1ZonesLayoutFolder'
}

{if $enabled_container|contains( $module_result_node.class_identifier) } 
	{if is_set($module_result_node.data_map.layout)}		
		{if count($module_result_node.data_map.layout.content)}
			{if $module_result_node.data_map.layout.content.zone_layout|ne('0ZonesLayoutFolder')}
				{if and($module_result_node.data_map.layout.content.zone_layout|eq($layout1zone), $module_result_node.depth|gt(2))}
					{attribute_view_gui custom_keys=$custom_keys attribute=$module_result_node.data_map.layout}
					{set $has_boxes_folder=true()}
				{/if}
			{/if}
		{/if}
	{/if}
    {if is_set($module_result_node.path_array[6])}
        {set $global_layout=fetch(content,list, hash(parent_node_id,$module_result_node.path_array[6],
                            class_filter_type, include, class_filter_array, array('global_layout'),
                            limit, 1))}
        {if $global_layout|count()|gt(0)} 
            {set $has_boxes=true()}
        {/if}
    {/if}
    
    {if $has_boxes|not()}
        {if is_set($module_result_node.path_array[5])}
        {set $global_layout=fetch(content,list, hash(parent_node_id,$module_result_node.path_array[5],
                        class_filter_type, include, class_filter_array, array('global_layout'),
                        limit, 1))}
        {/if}
        {if $global_layout|count()|gt(0)}
            {set $has_boxes=true()}
        {/if}			
    {/if}
    {if $has_boxes|not()}
        {if is_set($module_result_node.path_array[4])}
        {set $global_layout=fetch(content,list, hash(parent_node_id,$module_result_node.path_array[4],
                        class_filter_type, include, class_filter_array, array('global_layout'),
                        limit, 1))}
        {/if}
        {if $global_layout|count()|gt(0)}
            {set $has_boxes=true()}
        {/if}			
    {/if}

    {if $has_boxes|not()}
        {if is_set($module_result_node.path_array[3])}
        {set $global_layout=fetch(content,list, hash(parent_node_id,$module_result_node.path_array[3],
                        class_filter_type, include, class_filter_array, array('global_layout'),
                        limit, 1))}
        {/if}			
        {if $global_layout|count()|gt(0)}
            {set $has_boxes=true()}
        {/if}			
    {/if}
    {if $has_boxes|not()}
        {set $global_layout=fetch(content,list, hash(parent_node_id,$module_result_node.path_array[2],
                        class_filter_type, include, class_filter_array, array('global_layout'),
                        limit, 1))}
        
    {/if}
    
    {if $global_layout|count()|gt(0)}
        {attribute_view_gui custom_keys=$custom_keys attribute=$global_layout[0].data_map.page}
        {set $has_boxes=true()}
    {/if}
{/if}
{$close}


{$open}
{if $enabled_container|contains($module_result_node.class_identifier)|not()}

    {if $mlt_exclude_classes|contains( $module_result_node.class_identifier )|not()}
    {* BLOCCO RICERCA AUTOMATICA MORE LIKE THIS - con filtro sulla classe *}
        {include 
			name=morelikethis 
			node=$module_result_node
			title='Analoghi a questo'
			class_filter=$module_result_node.class_identifier
			uri='design:parts/common/related_contents.tpl' }
    {/if}

    {* BLOCCO RICERCA AUTOMATICA MORE LIKE THIS *}
        {include 
			name=morelikethis 
			node=$module_result_node
			class_filters=$mlt_class_ids
			title='Ti può interessare'
			uri='design:parts/common/related_contents.tpl'}
			{set $has_boxes=true()}

{else} 

    {if is_set($module_result_node.data_map.layout)}
        {if count($module_result_node.data_map.layout.content)}	
            {if $module_result_node.data_map.layout.content.zone_layout|ne('0ZonesLayoutFolder')}
                {if and($has_boxes|not(), $has_boxes_folder|not())}
                    {attribute_view_gui attribute=$module_result_node.data_map.layout}
                    {set $has_boxes=true()}
                {/if}
            {/if}
        {/if}
    {else}
        {if $global_layout|count()|gt(0)}
                {attribute_view_gui custom_keys=$custom_keys attribute=$global_layout[0].data_map.page}
        {/if}
    {/if}
{/if}
{$close}	

{*
	MLT SEMPLICE
*}
{if and( $mlt_enabled|eq( 'enabled' ), ezmodule( 'ezfind' ), $mlt_classes|contains( $module_result_node.class_identifier )|not() )}
{def $mlt=fetch( ezfind, moreLikeThis, hash(
                                        'query_type', 'nid',
                                        'query', $module_result_node.node_id,
                                        'class_id', $mlt_class_ids,
                                        'limit', openpaini( 'MoreLikeThis', 'Limite', 15 ),
                                        'sort_by', hash( published, desc )
                                    ) )}
    {if $mlt.SearchCount|gt(0)}
        {$open}
            <h2 class="block-title">Ti pu&ograve; interessare</h2>
            <div class="block-content">
                <ul class="menu">
                    {foreach $mlt.SearchResult as $child}
                    {if is_set($child.name)}
                    <li>
                        <small>{$child.class_name} {include pre=' - ' name=date node=$child uri='design:parts/common/date.tpl'}</small><br/>
                        <a href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
                    </li>
                    {/if}
                    {/foreach}
                </ul>
            </div>
        {$close}
    {/if}
{/if}

{*if is_set($global_layout_class.object_list[0])}
    {def $global_layout_object = $global_layout_class.object_list[0]}
    {attribute_view_gui attribute=$global_layout_object.data_map.page}
{/if*}