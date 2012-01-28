{*
	Vista a tab
	E' necessario usare il blocco con oggetti che dispongano dei seguenti attributi:
	- image
	- short_description
	- intro
*}

{def $valid_nodes = $block.valid_nodes}
{if $valid_nodes|count()|gt(0)}

{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}

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

	{if $block.name}<h2 class="block-title">{$block.name}</h2>{else}<h2 class="hide">Altre informazioni</h2>{/if}

	<div class="ui-tabs">	

		<div class="border-box box-trans-blue box-tabs-header tabs">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content" id="zone-id-{$block.id}">
			<ul class="ui-tabs-nav">							 
				{foreach $valid_nodes as $index => $node}
				<li class="{$node.name|slugize()} {if $index|eq(0)}ui-state-active{else}ui-state-default{/if}">											
					<a href={$node.url_alias|ezurl()} title="{$node.name|wash()}"><span class="{$node.name|slugize()}">{$node.name|wash()}</span></a>					
				</li>
				{/foreach}
			</ul>
		</div>
		</div></div></div>
		</div>			
		
		<div class="tabs-panels">			
			{foreach $valid_nodes as $index => $node}

			<div id="{$node.name|slugize()}" class="{if $index|gt(0)}no-js-hide {/if}ui-tabs-hide">
			
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
									
					<div id="content-{$node.name|slugize()}">
						{if $node.data_map.image.has_content}
							<div class="attribute-image">{attribute_view_gui image_class='lista_tab' attribute=$node.data_map.image}</div>
						{/if}
						{if $node.data_map.abstract.has_content}
							<div class="abstract">{attribute_view_gui attribute=$node.data_map.abstract}</div>
						{/if}
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


{else}
	Errore: nessun Folder selezionato!
{/if}
