{def $left_menu_depth = count($pagedata.path_array)|gt(1)|choose( 0, 1 )}
{if is_area_tematica()}
    {set $left_menu_depth = inc( $left_menu_depth )}
{/if}

{def $left_menu_root_node = $pagedata.path_array[$left_menu_depth]
     $left_menu_root_url = cond( $pagedata.path_array[$left_menu_depth].url_alias, $pagedata.path_array[$left_menu_depth].url_alias, $requested_uri_string )
     $root_node = fetch( content, node, hash( node_id, $left_menu_root_node.node_id ) )}

{ezscript_require( array( 'ezjsc::jquery', 'ezjsc::jqueryio', 'ajaxmenu.js', 'cachedmenu.js' ) )}

<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">

    <h2 class="hide">Menu di navigazione</h2>
	<h3>
        <a href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $left_menu_root_node.node_id)|ezurl}{else}{$left_menu_root_url|ezurl}{/if}>
            {$left_menu_root_node.text}
        </a>
    </h3>

    {if is_area_tematica()}
        {*include uri='design:menu/cached/leftmenu.tpl' root_node_id=$left_menu_root_node.node_id*}
        {left_menu_cached( hash( 'root_node_id', $left_menu_root_node.node_id, 'user_hash', $user_hash ) )}
    {else}
        {def $custom_templates_classes = openpaini( 'SideMenu', 'CachedMenuCustomTemplateClassi', array() )
             $custom_templates_nodes = openpaini( 'SideMenu', 'CachedMenuCustomTemplateNodi', array() )}
        {if is_set( $custom_templates_classes[$root_node.class_identifier] )}
            {left_menu_cached( hash( 'root_node_id', $root_node.node_id, 'template', $custom_templates_classes[$root_node.class_identifier] ) )}
        {elseif is_set( $custom_templates_nodes[$left_menu_root_node.node_id] )}
            {left_menu_cached( hash( 'root_node_id', $left_menu_root_node.node_id, 'template', $custom_templates_nodes[$left_menu_root_node.node_id] ) )}
        {else}
            {left_menu_cached( hash( 'root_node_id', $left_menu_root_node.node_id ) )}
        {/if}
    {/if}

</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

{undef $left_menu_root_url $left_menu_root_node $left_menu_depth}