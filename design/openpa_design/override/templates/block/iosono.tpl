{*
	Vista a tab
*}

{def $valid_nodes = $block.valid_nodes
	 $children = array()
	 $subchildren = array()
	 $children_count = 0
	 $item_per_column = 0
	 $classi_iosono_padre = ezini( 'GestioneClassi', 'classi_iosono_padre', 'content.ini')
	 $classi_iosono_figli = ezini( 'GestioneClassi', 'classi_iosono_figli', 'content.ini')
	 $classi_da_escludere = ezini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', 'content.ini')
}


{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js') )}

<script type="text/javascript">
{literal}
$(function() {
	$('.block-lista_tab .ui-tabs-nav li a').each(function(index) {
		$(this).attr( 'href', '#'+$('span', this).attr('class') );
	});
	$("#zone-id-{/literal}{$block.id}{literal}").tabs({ 
		tabTemplate: '<![CDATA[<li><a class="no-js-hide" href="#{href}"><span>#{label}</span></a><a class="no-js-show"></a></li>]]>'
		});
});
{/literal}
</script>

<div class="block-type-lista block-{$block.view} block-lista_tab">

	{if $block.name}<h2 class="block-title">{$block.name|wash()}</h2>{else}<h2 class="hide">Naviga per</h2>{/if}

	<div class="ui-tabs">	

		<div class="border-box box-trans-blue box-tabs-header tabs">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content" id="zone-id-{$block.id}">
			<ul class="ui-tabs-nav">							 
				{foreach $valid_nodes as $index => $node}
				<li class="{if $index|eq(0)}ui-state-active{else}ui-state-default{/if}">															
					<a href={$node.url_alias|ezurl()} title="{$node.name|wash()}"><span class="{$node.name|slugize()}">{$node.name|wash()}</span></a>
				</li>
				{/foreach}
			</ul>
		</div>
		</div></div></div>
		</div>			
		
		<div class="tabs-panels">			
			{foreach $valid_nodes as $index => $node}
			<h3 class="hide no-js-hide">{$node.name}</h3>
			<div id="{$node.name|slugize()}" class="{if $index|gt(0)}no-js-hide {/if}ui-tabs-hide">
			
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
				
					{set $children = fetch( 'content', 'list',  
								hash(   'parent_node_id', $node.node_id, 
									'class_filter_type', 'exclude', 
									'class_filter_array', $classi_da_escludere,
									'sort_by', $node.sort_array, 
									'limit', 20 ) )
					     $subchildren = array()
					     $children_count = $children|count()}
					
					{if $children_count|gt(0)}
						{set $item_per_column = floor( $children_count|div( 3 ) ) }
					{/if}
					
					<div class="columns-three">
					
					{foreach $children as $index => $child}			
					
					{if $item_per_column|gt(0)}
					{if $index|eq(0)}
						<div class="col-1-2">
						<div class="col-1">
						<div class="col-content">	
					{elseif eq($index, $item_per_column)} 
						</div>
						</div>
						<div class="col-2">
						<div class="col-content">
					{elseif eq($index, mul($item_per_column,2))} 
						</div>
						</div>
						</div>
						<div class="col-3">
						<div class="col-content">
					{/if}
					{/if}					
					
						<div id="{$child.name|slugize()}" class="tab-panel-detail">
							<h4><a title="Informazioni su {$child.name|wash}" href={$child.object.main_node.url_alias|ezurl()}>{$child.name|wash()}</a></h4>
						{set $subchildren=fetch( 'content', 'list',  
									  hash( 'parent_node_id', $child.node_id, 
										'class_filter_type', 'exclude', 
										'class_filter_array', $classi_da_escludere,
										'sort_by', $node.sort_array, 
										'limit', 10 ) )}
						{if $subchildren|count()|gt(0)}
						{foreach $subchildren as $subchild}<a title="Informazioni su {$subchild.name|wash}" href={$subchild.object.main_node.url_alias|ezurl()}>{$subchild.name|wash()}</a>{delimiter}, {/delimiter}{/foreach}
						{else}
							{if $child.data_map.abstract.has_content}
							{attribute_view_gui attribute=$child.data_map.abstract}
							{else}
								In fase di completamento
							{/if}
						{/if}
						</div>

					{if $item_per_column|gt(0)}
					{if $children_count|eq( $index|inc )}
						</div>
						</div>
					{/if}
					{/if}
						
					{/foreach}
					
					</div>

				</div>
				</div></div></div>
				</div>
				
				<div class="border-box box-violet-gray box-tabs-footer tab-link">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					
					<a class="arrows" href={$node.url_alias|ezurl()} title="{$node.name|wash()}"><span class="arrows-blue-r">Vai a {$node.name|wash()}</span></a>
				
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>		
			
			</div>
				
			{/foreach}
		</div>		
		

	
	</div>

</div>
