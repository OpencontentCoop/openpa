{def $top_menu_class_filter = openpaini( 'TopMenu', 'IdentificatoriMenu', array() )
     $custom_menu = openpaini( 'TopMenu', 'NodiCustomMenu', false() )
     $custom_aree = openpaini( 'TopMenu', 'NodiAreeCustomMenu', array() )
     $main_styles = openpaini( 'Stili', 'Nodo_NomeStile', array() )}

{ezscript_require( array( 'menu.js' ) )}
<script type="text/javascript">{literal}
$(document).ready(function(){{/literal}
$.preLoadImages("{'openpa/topmenu-secondlevel.png'|ezimage(no)}","{'openpa/bullet_secondlevel.gif'|ezimage(no)}");
{literal}});
{/literal}</script>


<div class="topmenu-design{if $custom_menu} custom{/if}">

    <h2 class="hide">Menu principale</h2>
	
    <ul id="topmenu-firstlevel">
	
	{if $custom_keys.is_area_tematica}
		
        {def $aree_tematiche = $custom_keys.is_area_tematica.parent}			
		
        <li class="menu-area-tematica">
			<div><a href={$custom_keys.is_area_tematica.url_alias|ezurl()}><span>{$custom_keys.is_area_tematica.name|wash()}</span></a></div>
		</li> 		
		
        {def $aree_tematiche_level_2 = fetch('content','list', hash( 'parent_node_id', $aree_tematiche.node_id,
                                    		'sort_by', $aree_tematiche.sort_array, 'limit', 20,
                                        	'class_filter_type', 'include', 
                                        	'class_filter_array',  $top_menu_class_filter ) ) 
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
																 
	{else}		
			
		
        {if $custom_menu|not()}
            {* LIMITE DELLA FETCH DEL TOPMENU DI DEFAULT A 4 (QUATTRO) *}	
            {def $root_node=fetch( 'content', 'node', hash( 'node_id', $pagedata.root_node) )
                 $top_menu_items=fetch( 'content', 'list', hash( 'parent_node_id', $root_node.node_id,
                                                                  'sort_by', $root_node.sort_array,
                                                                  'class_filter_type', 'include',
                                                                  'class_filter_array', $top_menu_class_filter,
                                                                  'limit', openpaini( 'TopMenu', 'LimitePrimoLivello', 4 ) ) )
                 $current_node_in_path = first_set($pagedata.path_array[1].node_id, 0  )
                 $current_node_in_path_2 = first_set($pagedata.path_array[2].node_id, 0  )
            }
        {else}
            {def $top_menu_items=array()}
            {foreach $custom_menu as $menu_id}
                {set $top_menu_items = $top_menu_items|append( fetch( 'content', 'node', hash( 'node_id', $menu_id ) )  )}
            {/foreach}
            {def $root_node=false
                 $current_node_in_path = first_set($pagedata.path_array[1].node_id, 0  )
                 $current_node_in_path_2 = first_set($pagedata.path_array[2].node_id, 0  )}
            {if eq($pagedata.path_array[1].node_id, ezini('NodeSettings', 'RootNode', 'content.ini'))}
            	{set $current_node_in_path = first_set($pagedata.path_array[2].node_id, 0  )
                     $current_node_in_path_2 = first_set($pagedata.path_array[3].node_id, 0  )}
            {/if}
        {/if}
        
		{def $level_2_items_count = 0
        	 $top_menu_items_count = $top_menu_items|count()
			 $item_class = array()
			 $level_2_items = 0
			 $item_class_2 = array()
			 $level_3_items= array()
		}

		{if $top_menu_items_count}
		   {foreach $top_menu_items as $key => $item}
			    {set $item_class = cond($current_node_in_path|eq($item.node_id), array("selected"), array())
                     $level_2_items = 0
                     $level_2_items = fetch( 'content', 'list', hash( 'parent_node_id', $item.node_id,
                                                                    'sort_by', $item.sort_array,
                                                                    'limit', openpaini( 'TopMenu', 'LimiteSecondoLivello', 20 ),
                                                                    'class_filter_type', 'include', 
                                                                    'class_filter_array', $top_menu_class_filter ) )}
																			  
				{if $key|eq(0)}
					{set $item_class = $item_class|append("firstli")}
				{/if}
				{if $top_menu_items_count|eq( $key|inc )}
					{set $item_class = $item_class|append("lastli")}
				{/if}
				{if $item.node_id|eq( $current_node_id )}
					{set $item_class = $item_class|append("current")}
				{/if}
				
                {if is_set($main_styles[$item.node_id])}
			    {set $item_class = $item_class|append($main_styles[$item.node_id]|slugize())}
                {/if}

					<li id="node_id_{$item.node_id}"{if $item_class} class="{$item_class|implode(" ")}"{/if}>
                        <div>
                            <a class="{$item.name|slugize()}" href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $item.node_id)|ezurl}{else}{$item.url_alias|ezurl}{/if}{if $pagedata.is_edit} onclick="return false;"{/if}>
                                <span>{$item.name|wash()}</span>
                            </a>
					{if $level_2_items|count()}
						<ul class="secondlevel">
							{foreach $level_2_items as $key => $item2}
								{set $item_class_2 = array()
									 $level_2_items_count = $level_2_items|count()}
								{if $current_node_in_path_2|eq($item2.node_id)}
									{set $item_class_2 = array("selected")}
								{/if}
								{if $key|eq(0)}
									{set $item_class_2 = $item_class_2|append("subfirstli")}
								{/if}
								{if $level_2_items_count|eq( $key|inc )}
									{set $item_class_2 = $item_class_2|append("sublastli")}
								{/if}
								{if $item2.node_id|eq( $current_node_id )}
									{set $item_class_2 = $item_class_2|append("current")}
								{/if}
								{set $item_class_2 = $item_class_2|append($item2.name|slugize())}
								<li id="node_id_{$item2.node_id}" class="count-{$level_2_items|count()}{if $item_class_2} {$item_class_2|implode(" ")}{/if}">
                                    <div>
                                        <a title="{if is_set($item2.data_map.abstract)}{if $item2.data_map.abstract.has_content}{$item2.data_map.abstract.content.output.output_text|explode("<br />")|implode(" ")|strip_tags()|trim()}{/if}{else}link a {$item2.name|wash()}{/if}" class="{$item2.name|slugize()}" href={if eq( $ui_context, 'browse' )}{concat("content/browse/", $item2.node_id)|ezurl}{else}{if $item2.node_id|eq($item2.main_node_id)}{$item2.url_alias|ezurl}{else}{if $item2.class_identifier|eq('area_tematica')}{$item2.object.main_node.url_alias|ezurl}{else}{$item2.url_alias|ezurl}{/if}{/if}{/if}{if $pagedata.is_edit} onclick="return false;"{/if}>
                                            {$item2.name|wash()}
                                        </a>
                                    </div>
								{if $item2.class_identifier|eq('area_tematica')}
									{set $level_3_items=array()}
								{else}
									{set $level_3_items = fetch( 'content', 'list', hash(
                                                                                        'parent_node_id', $item2.node_id, 
                                                                        				'sort_by', $item2.sort_array,
                                                                        				'limit', openpaini( 'TopMenu', 'LimiteTerzoLivello', 10 ), 
                                                                        				'class_filter_type','include',
                                                                                        'class_filter_array', $top_menu_class_filter ) )}
								{/if}
								{if $level_3_items|count()|gt(0)}
								<ul class="thirdlevel float-break {$item2.name|slugize()}">
									{foreach $level_3_items as $item3}
										<li class="thirdlevel-count-{$level_3_items|count()}">
                                            <div>
                                                <a title="{if is_set($item3.data_map.abstract)}{if $item3.data_map.abstract.has_content}{$item3.data_map.abstract.content.output.output_text|explode("<br />")|implode(" ")|strip_tags()|trim()}{/if}{else}link a {$item3.name|wash()}{/if}" class="{$item3.name|slugize()}" href={if $item3.node_id|eq($item3.main_node_id)}{$item3.url_alias|ezurl}{else}{if $item3.class_identifier|eq('area_tematica')}{$item3.object.main_node.url_alias|ezurl}{else}{$item3.url_alias|ezurl}{/if}{/if}>
                                                    {$item3.name|wash()}
                                                </a>
                                            </div>
                                        </li>
									{/foreach}
								</ul>
								{/if}
								</li>
							{/foreach}
						</ul>
					{/if}				
                        </div>
                    </li>

			  {/foreach}
		{/if}
		{undef $level_2_items $root_node $top_menu_items $item_class $top_menu_items_count $current_node_in_path}
	{/if}    
    </ul>
</div>
