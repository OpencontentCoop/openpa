{ezscript_require( array( 'cachedmenu.js' ) )}

<div class="topmenu-design{if openpaini( 'TopMenu', 'NodiCustomMenu', false() )} custom{/if}">

    <h2 class="hide">Menu principale</h2>
	
	{if is_area_tematica()}
	<ul id="topmenu-firstlevel">
        {def $aree_tematiche = is_area_tematica().parent}			
		
        <li class="menu-area-tematica">
			<div><a href={is_area_tematica().url_alias|ezurl()}><span>{is_area_tematica().name|wash()}</span></a></div>
		</li> 		
		
        {def $aree_tematiche_level_2 = fetch('content', 'list', hash( 'parent_node_id', $aree_tematiche.node_id,
                                    		'sort_by', $aree_tematiche.sort_array, 'limit', 20,
                                        	'class_filter_type', 'include', 
                                        	'class_filter_array',  openpaini( 'TopMenu', 'IdentificatoriMenu', array() ) ) ) 
			 $aree_tematiche_level_2_class = array()
			 $aree_tematiche_level_2_count=0
			 $current_node_in_path_2 = first_set($pagedata.path_array[2].node_id, 0  )}
             
		<li id="menu-aree-tematiche" class="lastli aree-tematiche">
            <div>
                <a href={$aree_tematiche.url_alias|ezurl()}>
                    <span>{$aree_tematiche.name}</span>
                </a>														 
			
                {if $aree_tematiche_level_2|count()}
                <ul class="secondlevel">
                    {foreach $aree_tematiche_level_2 as $key => $item2}
                        {set $aree_tematiche_level_2_class = array()
                             $aree_tematiche_level_2_count = $aree_tematiche_level_2|count()}
                        {if $current_node_in_path_2|eq($item2.node_id)}
                            {set $aree_tematiche_level_2_class = array("selected")}
                        {/if}
                        {if $key|eq(0)}
                            {set $aree_tematiche_level_2_class = $aree_tematiche_level_2_class|append("subfirstli")}
                        {/if}
                        {if $aree_tematiche_level_2_count|eq( $key|inc )}
                            {set $aree_tematiche_level_2_class = $aree_tematiche_level_2_class|append("sublastli")}
                        {/if}
                        {if $item2.node_id|eq( $current_node_id )}
                            {set $aree_tematiche_level_2_class = $aree_tematiche_level_2_class|append("current")}
                        {/if}
                        {set $aree_tematiche_level_2_class = $aree_tematiche_level_2_class|append($item2.name|slugize())}
                        <li id="node_id_{$item2.node_id}"{if $aree_tematiche_level_2_class} class="{$aree_tematiche_level_2_class|implode(" ")}"{/if}><div><a title="Link a {$item2.name|wash()}" class="{$item2.name|slugize()}" href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $item2.node_id)|ezurl}{else}{if $item2.node_id|eq($item2.main_node_id)}{$item2.url_alias|ezurl}{else}{$item2.object.main_node.url_alias|ezurl}{/if}{/if}{if $pagedata.is_edit} onclick="return false;"{/if}>{$item2.name|wash()}</a></div></li>
                    {/foreach}
                </ul>
                {/if}
        
            </div>
        </li>					
    </ul>
	{else}    
		{top_menu_cached( hash( 'root_node_id', $pagedata.root_node ) )}
    {/if}
</div>
