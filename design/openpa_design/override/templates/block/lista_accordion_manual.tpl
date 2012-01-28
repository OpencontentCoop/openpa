{*
	BLOCCO
	Vista accordion manuale
*}


{def $children = $block.valid_nodes
	 $nodo = $children[0]}
{def $classi_senza_data_inline = ezini( 'GestioneClassi', 'classi_senza_data_inline', 'content.ini')}


{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}

<script type="text/javascript">
{literal}
$(function() {
	$("#{/literal}{$nodo.name|slugize()}-{$block.id}{literal}").accordion({ 
		autoHeight: false,
		event: "mouseover",
		change: function(event, ui) { 
			$('a', ui.newHeader ).addClass('active'); 
			$('a', ui.oldHeader ).removeClass('active');  
		}
	}); 
});
{/literal}
</script>


<div class="block-type-lista block-lista_accordion block-{$block.view}">

	
	{if $block.name}
		<h2 class="block-title">
			{*<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">*}
			{$block.name}
			{*</a>*}
		</h2>
	{/if}
		
	<div id="{$nodo.name|slugize()}-{$block.id}" class="ui-accordion">	
		{foreach $children as $index => $child}
		
		<div id="{$child.name|slugize()}" class="border-box box-gray box-accordion ui-accordion-header {if $index|eq(0)}no-js-ui-state-active{/if}">
			<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">
				
				<h3 class="attribute-small">
					{if $child.class_identifier|eq('link')}
        					<a href={$child.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">{$child.name|wash()}</a>
					{else}
						<a{if $index|eq(0)} class="active"{/if} href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
					{/if}
				</h3>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>
		
		<div id="{$child.name|slugize()}-detail" class="border-box box-gray box-accordion ui-accordion-content {if $index|eq(0)}ui-accordion-content-active{/if} {if $index|gt(0)}no-js-hide{/if}">
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">						
				
				<div class="attribute-short {if $index|gt(0)}no-js-hide{/if}">
					{if $child.data_map.image.has_content}
					<div class="attribute-image">	
						{if $child.class_identifier|eq('link')}
        						{attribute_view_gui attribute=$child.data_map.image 
								href=$child.data_map.location.content|ezurl() image_class=lista_accordion}
						{else}
							{attribute_view_gui attribute=$child.data_map.image 
								href=$child.url_alias|ezurl() image_class=lista_accordion}
						{/if}
					</div>
					{else}
					  <img class="image-medium" src={concat('icons/crystal/64x64/mimetypes/',$child.class_identifier,'.png')|ezimage()} alt="{$child.class_identifier}" title="{$child.class_identifier}" />
					{/if}
					
					{* mostro (eventualmente) la data di pubblicazione (indotta) *}		
					{if $classi_senza_data_inline|contains($child.class_identifier)|not}
						di {$child.object.published|l10n(date)}
					{/if}
					
					{if is_set($child.data_map.abstract)}
						{if $child.data_map.abstract.has_content}						
							{attribute_view_gui attribute=$child.data_map.abstract}
						{/if}
					{elseif is_set($child.data_map.oggetto)}
						{if $child.data_map.oggetto.has_content}
							<div class="attribute-object">
								{attribute_view_gui attribute=$child.data_map.oggetto}
							</div>
						{/if}
					{elseif is_set($node.data_map.testata)}
					   <div class="abstract-line">
					   {if $node.data_map.testata.has_content}
						<p>Tratto da: 
						<strong> {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$node.data_map.testata} </strong>
					   	   {if $node.data_map.pagina.content|ne(0)}a pag. {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$node.data_map.pagina}
					        	{if $node.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$node.data_map.pagina_continuazione}
							{/if}
					   	   {/if}
						   {if $node.data_map.autore.has_content}
			 				(di {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$node.data_map.autore})
					    	   {/if}
						</p>
					    {/if}    
					    {if $node.data_map.argomento_articolo.has_content}
			 			<p>Su: 
						 <strong>
						 {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$node.data_map.argomento_articolo}
						 </strong>
						</p>
					    {/if}
					    </div>
					{else}
						<div class="attribute-node">
							{node_view_gui content_node=$child view='line'}
						</div>
					{/if}
					{if $child.class_identifier|eq('applicativo')}
						{attribute_view_gui attribute=$child.data_map.location_applicativo}
					{/if}
				</div>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
		</div>
		
		{/foreach}
		
	</div>
	
</div>
	
