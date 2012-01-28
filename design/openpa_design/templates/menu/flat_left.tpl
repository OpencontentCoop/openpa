{def $inimenu = openpaini( 'SideMenu', 'IdentificatoriMenu', array() )}
{def $top_menu_class_filter = openpaini( 'TopMenu', 'IdentificatoriMenu', array() )
     $left_menu_depth = count($pagedata.path_array)|gt(1)|choose( 0, 1 )
     $parent_node = fetch(content, node, hash(node_id, $current_node_id))
     $left_menu_root_url = cond( $pagedata.path_array[$left_menu_depth].url_alias, $pagedata.path_array[$left_menu_depth].url_alias, $requested_uri_string )
	 $node_id = $pagedata.path_array[$left_menu_depth].node_id}

{if $custom_keys.is_area_tematica}
	{set $left_menu_depth = inc($left_menu_depth)}
{/if}

{if openpaini( 'SideMenu', 'AjaxMenu', 'enabled' )|eq( 'enabled' )}
{ezscript_require(array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ajaxmenu.js' ) )}
<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('#sidemenu .menu-list').loadMenu(
		{/literal}{$pagedata.path_array[$left_menu_depth].node_id}{literal},
		{/literal}{$current_node_id}{literal},
		{/literal}{count($pagedata.path_array)|gt(1)|choose( 0, 1 )}{literal},
		{/literal}'{$module_result.ui_context}'{literal},
		{/literal}'{$custom_keys.is_area_tematica}'{literal}
		);
	});
{/literal}
</script>
{/if}


<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">

    <h2 class="hide">Menu di navigazione</h2>
	<h3><a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $pagedata.path_array[$left_menu_depth].node_id)|ezurl}{else}{$left_menu_root_url|ezurl}{/if}>{$pagedata.path_array[$left_menu_depth].text}</a></h3>
	
{if ne( $pagedata.class_identifier, 'documentation_page' )}
    {def $root_node=fetch( 'content', 'node', hash( 'node_id', $pagedata.path_array[$left_menu_depth].node_id ) )
         $left_menu_items = fetch( 'content', 'list', hash( 'parent_node_id', $root_node.node_id,
                                                            'sort_by', $root_node.sort_array,
                                                            'data_map_load', false(),
                                                            'class_filter_type', 'include',
                                                            'class_filter_array', $inimenu ) )
         $left_menu_items_count = $left_menu_items|count()
         $li_class = array()
		 $li_class3 = array()
		 $li_class4 = array()
         $a_class = array()
		 $a_class3 = array()
		 $a_class4 = array()
         $current_node_in_path_2 = first_set( $pagedata.path_array[$left_menu_depth|inc].node_id,  0 )
         $current_node_in_path_3 = first_set( $pagedata.path_array[$left_menu_depth|sum(2)].node_id,  0 )
         $current_node_in_path_4 = first_set( $pagedata.path_array[$left_menu_depth|sum(3)].node_id,  0 )
         $current_node_in_path_5 = first_set( $pagedata.path_array[$left_menu_depth|sum(4)].node_id,  0 )}

	{if $left_menu_items_count}
        <ul class="menu-list">
        {foreach $left_menu_items as $key => $item}
        {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $item.node_id )|not()}
            {set $a_class = cond($current_node_in_path_2|eq($item.node_id), array("selected"), array())
                 $li_class = cond( $key|eq(0), array("firstli"), array() )}

            {if $left_menu_items_count|eq( $key|inc )}
                {set $li_class = $li_class|append("lastli")}
            {/if}
            {if $item.node_id|eq( $current_node_id )}
                {set $a_class = $a_class|append("current")}
            {/if}
            {if eq( $item.class_identifier, 'link')}
                <li{if $li_class} class="{$li_class|implode(" ")}"{/if}>
				<div class="second_level_menu">
					<span class="handler {if $a_class}{$a_class|implode(" ")}{/if}"></span>
					<a {if eq( $ui_context, 'browse' )}href={concat("content/browse/", $item.node_id)|ezurl}{else}href={$item.data_map.location.content|ezurl}{if and( is_set( $item.data_map.open_in_new_window ), $item.data_map.open_in_new_window.data_int )} target="_blank"{/if}{/if}{if $a_class} class="{$a_class|implode(" ")}"{/if} title="{$item.data_map.location.data_text|wash}" class="menu-item-link" rel={$item.url_alias|ezurl}>
						<span>{$item.name|wash()}</span>
					</a>
				</div>
            {else}
                <li{if $li_class} class="{$li_class|implode(" ")}"{/if}>
					<div class="second_level_menu">
						<span class="handler {if $a_class}{$a_class|implode(" ")}{/if}"></span>
						<a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $item.node_id)|ezurl}{else}{$item.url_alias|ezurl}{/if}{if $a_class} class="{$a_class|implode(" ")}"{/if}>
							<span>{$item.name|wash()}</span>
						</a>
					</div>
            {/if}

            {if eq( $current_node_in_path_2, $item.node_id )}
                {def $sub_menu_items = fetch( 'content', 'list', hash( 'parent_node_id', $item.node_id, 'sort_by', $item.sort_array, 'data_map_load', false(),
                                                                      'class_filter_type', 'include', 'class_filter_array', $inimenu ) )
                     $sub_menu_items_count = $sub_menu_items|count}
                {if $sub_menu_items_count}
                <ul class="submenu-list">
                    {foreach $sub_menu_items as $subkey => $subitem}
                    {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem.node_id )|not()}   	
                        {set $a_class = cond($current_node_in_path_3|eq($subitem.node_id), array("selected"), array())  $li_class = cond( $subkey|eq(0), array("firstli"), array() )}
                    	{if $sub_menu_items_count|eq( $subkey|inc )} {set $li_class = $li_class|append("lastli")} {/if}
                    	{if $subitem.node_id|eq( $current_node_id )} {set $a_class = $a_class|append("current")} {/if}
                    	<li{if $li_class} class="{$li_class|implode(" ")}"{/if}>
							<div class="third_level_menu">
								<span class="handler {if $a_class}{$a_class|implode(" ")}{/if}"></span>
								<a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $subitem.node_id)|ezurl}{else}{$subitem.url_alias|ezurl}{/if}{if $a_class} class="{$a_class|implode(" ")}"{/if}>
									<span>{$subitem.name|wash()}</span>
								</a>
							</div>
                        {* start terzo livello  *}
                                {if eq( $current_node_in_path_3, $subitem.node_id )}
                                {def $sub_menu_items3 = fetch( 'content', 'list', hash( 'parent_node_id', $subitem.node_id, 'sort_by', $subitem.sort_array, 'data_map_load', false(),
                                                                                  'class_filter_type', 'include', 'class_filter_array', $inimenu ) )
                                         $sub_menu_items_count3 = $sub_menu_items3|count}
                                    {if $sub_menu_items_count3} 
                                        <ul class="submenu-list-3">
                                            {foreach $sub_menu_items3 as $subkey3 => $subitem3}
                                            {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem3.node_id )|not()}
                                                {set $a_class3 = cond($current_node_in_path_4|eq($subitem3.node_id), array("selected"), array())  $li_class3 = cond( $subkey3|eq(0), array("firstli"), array() )}
                                                {if $sub_menu_items_count3|eq( $subkey|inc )} {set $li_class3 = $li_class3|append("lastli")} {/if}
                                                {if $subitem3.node_id|eq( $current_node_id )} {set $a_class3 = $a_class3|append("current")}{/if}
                                                    <li{if $li_class3} class="{$li_class3|implode(" ")}"{/if}>
                                                        <div class="fourth_level_menu">
                                                            <span class="handler {if $a_class3}{$a_class3|implode(" ")}{/if}"></span>
                                                            <a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $subitem3.node_id)|ezurl}{else}{$subitem3.url_alias|ezurl}{/if}{if $a_class3} class="{$a_class3|implode(" ")}"{/if}>
                                                                <span>{$subitem3.name|wash()}</span>
                                                            </a>
                                                        </div>
                        {* start quarto livello  *}
                                    {if eq( $current_node_in_path_4, $subitem3.node_id )}
                                    {def $sub_menu_items4 = fetch( 'content', 'list', 
                                                hash( 'parent_node_id', $subitem3.node_id, 'sort_by', $subitem3.sort_array, 'data_map_load', false(),
                                                                                    'class_filter_type', 'include', 'class_filter_array', $inimenu ) )
                                                 $sub_menu_items_count4 = $sub_menu_items4|count}
                                            {if $sub_menu_items_count4}
                                            <ul class="submenu-list-4">
                                                {foreach $sub_menu_items4 as $subkey4 => $subitem4}
                                                {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem4.node_id )|not()}
                                                    {set $a_class4 = cond($current_node_in_path_5|eq($subitem3.node_id), array("selected"), array())  $li_class4 = cond( $subkey4|eq(0), array("firstli"), array() )}
                                                    {if $sub_menu_items_count4|eq( $subkey|inc )} {set $li_class4 = $li_class4|append("lastli")} {/if}
                                                    {if $subitem4.node_id|eq( $current_node_id )} {set $a_class4 = $a_class4|append("current")}{/if}
                                                    {if $subitem4.node_id|eq( $parent_node.parent_node_id )} {set $a_class4 = $a_class4|append("selected")}{/if}
                                                    <li{if $li_class4} class="{$li_class4|implode(' ')}"{/if}>
                                                        <div class="fifth_level_menu">											
                                                            <a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $subitem4.node_id)|ezurl}{else}{$subitem4.url_alias|ezurl}{/if}{if $a_class4} class="{$a_class4|implode(' ')}"{/if}>
                                                                <span>{$subitem4.name|wash()}</span>
                                                            </a>
                                                        </div>
                                            </li>
                                                {/if}
                                                {/foreach}
                                            </ul>
                                            {/if}
                                    {undef $sub_menu_items4 $sub_menu_items_count4}
                                    {/if}
                        {* end quarto livello  *}
                                    </li>
                                            {/if}
                                            {/foreach}
                                        </ul>
                                    {/if}
                                {undef $sub_menu_items3 $sub_menu_items_count3}
                                {/if}
                        {* end terzo livello  *}
                        
                        </li>
                    {/if}
                    {/foreach}
                </ul>
                {/if}
            {undef $sub_menu_items $sub_menu_items_count}
            {/if}
            </li>
        {/if}
        {/foreach}
        </ul>
    {/if}
    {undef $root_node $left_menu_items $left_menu_items_count $a_class $li_class $current_node_in_path_2 $current_node_in_path_3}

{else}

    <div class="contentstructure">
    {def $current_node         = fetch( content, node, hash( node_id, $current_node_id ) )
         $chapter_container    = fetch( content, node, hash( node_id, $current_node.path_array[$left_menu_depth|inc] ) )
         $class_filter         = ezini( 'TreeMenu', 'ShowClasses', 'contentstructuremenu.ini' )
         $depth                = is_set( $current_node.path_array[$left_menu_depth|sum(2)] )|choose( $left_menu_depth|sum(3), 0 )
         $node_to_unfold       = is_set( $current_node.path_array[$left_menu_depth|sum(2)] )|choose(0 , $current_node.path_array[$left_menu_depth|sum(2)] )
         $contentStructureTree = content_structure_tree( $chapter_container.node_id, $class_filter, $depth, 0, 'false', false(), $node_to_unfold )}

    {include uri='design:simplified_treemenu/show_simplified_menu.tpl' contentStructureTree=$contentStructureTree is_root_node=true() skip_self_node=true() current_node_id=$current_node_id unfold_node=$node_to_unfold chapter_level=0}

    {undef $current_node $chapter_container $class_filter $depth $node_to_unfold $contentStructureTree}
    </div>
{/if}

{undef $left_menu_root_url $left_menu_depth}

</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>
{undef}