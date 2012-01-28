{*
	BLOCCO
	Vista accordion
*}

	{def $customs=$block.custom_attributes $errors=false() $sort_array=array() $classes=array()}
	{def $classi_senza_data_inline = ezini( 'GestioneClassi', 'classi_senza_data_inline', 'content.ini')
	     $classi_blocco_particolari = ezini( 'GestioneClassi', 'classi_blocco_particolari', 'content.ini')
	     $classi_senza_correlazioni_inline = ezini( 'GestioneClassi', 'classi_senza_correlazioni_inline', 'content.ini')
 	     $attributes_to_show=array('organo_competente', 'circoscrizione', 'prosecuzioni')
	     $attributes_with_title=array('servizio','argomento')
	     $ruolo=false()
	}

	{if $customs.limite|gt(0)}
        	{def $limit=$customs.limite}
	{else}
        	{def $limit=3}
	{/if}

	{if $customs.livello_profondita|eq('')}
        	{def $depth=10}
	{else}
        	{def $depth=$customs.livello_profondita}
	{/if}

	{def $nodo=fetch(content,node,hash(node_id,$customs.node_id))}
	{switch match=$customs.ordinamento}
	{case match=''}
		{set $sort_array=$nodo.sort_array}
	{/case}
	{case match='priorita'}
        	{set $sort_array=array('priority', true())}
	{/case}
	{case match='pubblicato'}
        	{set $sort_array=array('published', false())}
	{/case}
	{case match='modificato'}
	        {set $sort_array=array('modified', false())}
	{/case}
	{case match='nome'}
	        {set $sort_array=array('name', true())}
	{/case}
	{/switch}

	{if is_set($customs.escludi_classi)|not()}
	{set $customs = $customs|merge(hash('escludi_classi', ''))}
	{/if}
	
	{if is_set($customs.includi_classi)|not()}
	{set $customs = $customs|merge(hash('includi_classi', ''))}
	{/if}
	
	{if $customs.escludi_classi|ne('')}
		{set $classes=$customs.escludi_classi|explode(',')}
		{set $classes = merge($classes, ezini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', 'content.ini')) }
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'exclude', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
	{elseif $customs.includi_classi|ne('')}
		{set $classes=$customs.includi_classi|explode(',')}
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'include', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
	{else}
		{set $classes = ezini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', 'content.ini')}
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'exclude', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
	{/if}


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

<div class="block-type-lista block-{$block.view}">

	{if $block.name}
		<h2 class="block-title">
				<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">{$block.name}</a>
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

				   {if and($child.class_identifier|eq('folder'), $child.data_map.classi_filtro.has_content)}
					<div class="main-image left">
			  		<img class="image-medium" 
					     src={concat('icons/crystal/64x64/mimetypes/',$child.data_map.classi_filtro.content,'.png')|ezimage()}
 			       		     alt="{$child.data_map.classi_filtro.content}" title="{$child.data_map.classi_filtro.content}" />
					</div>
				   {else}
					{if is_set($child.data_map.image)}
					{if $child.data_map.image.has_content}
					<div class="attribute-image">
						{if $child.class_identifier|eq('link')}
        						{attribute_view_gui attribute=$child.data_map.image 
								href=$child.data_map.location.content|ezurl() image_class=medium}
						{elseif $child.class_identifier|eq('struttura')}
							{def $my_node=fetch( 'content', 'node', 
									hash( 'node_id', $node.data_map.tipo_struttura.content.relation_list[0].node_id) )}
				
						<img class="image-medium" src={concat('icons/crystal/64x64/mimetypes/',$my_node.name,'.png')|ezimage()}
 							alt="{$my_node.name|wash()}" title="{$my_node.name|wash()}" />
						{else}
							{attribute_view_gui attribute=$child.data_map.image 
								href=$child.url_alias|ezurl() image_class=lista_accordion}
						{/if}
					</div>
					{else}
						<img class="image-medium" 
						     src={concat('icons/crystal/64x64/mimetypes/',$child.class_identifier,'.png')|ezimage()}
		 				     alt="{$child.class_identifier}" title="{$child.class_identifier}" />
					{/if}
					{/if}
				   {/if}
					
					{* mostro (eventualmente) la data di pubblicazione (indotta) *}		
					{if $classi_senza_data_inline|contains($child.class_identifier)|not}
						di {$child.object.published|l10n(date)}
					{/if}

					{if $child.class_identifier|eq('politico')}
						{set $ruolo=false()}
						{if $child.data_map.ruolo.has_content}
							{set $ruolo = $child.data_map.ruolo}
						{/if}
						{if $ruolo}
							{attribute_view_gui attribute=$child.data_map.ruolo}
						{else}
							{if $child.data_map.ruolo2.has_content}
								{attribute_view_gui attribute=$child.data_map.ruolo2}
							{elseif $child.data_map.abstract.has_content}}			
								{attribute_view_gui attribute=$child.data_map.abstract}
							{/if}
						{/if}
					
					
					{elseif is_set($child.data_map.abstract)}
						{if $child.data_map.abstract.has_content}
							{attribute_view_gui attribute=$child.data_map.abstract}
						{/if}	
					{elseif is_set($child.data_map.oggetto)}
						{if $child.data_map.oggetto.has_content}
							<div class="attribute-object">
								{attribute_view_gui attribute=$child.data_map.oggetto}
							</div>
						{/if}
					{elseif is_set($child.data_map.testata)}
					   <div class="abstract-line">
					   {if $child.data_map.testata.has_content}
						<p>Tratto da: 
						<strong> {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$child.data_map.testata} </strong>
					   	   {if $child.data_map.pagina.content|ne(0)}a pag. {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$child.data_map.pagina}
					        	{if $child.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$child.data_map.pagina_continuazione}
							{/if}
					   	   {/if}
						   {if $child.data_map.autore.has_content}
			 				(di {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$child.data_map.autore})
					    	   {/if}
						</p>
					   {/if}    
					   {if $child.data_map.argomento_articolo.has_content}
			 			<p>Su: 
						 <strong>
						 {attribute_view_gui href=nolink attribute=$child.data_map.argomento_articolo}
						 </strong>
						</p>
					   {/if}
					   </div>

					{else}
						<div class="attribute-node">
							{node_view_gui content_node=$child view='line' show_image='nessuna'}
						</div>
					{/if}
					{if $child.class_identifier|eq('applicativo')}
						{attribute_view_gui attribute=$child.data_map.location_applicativo}
					{/if}

				{* mostro gli altri attributi *}
				{foreach $child.data_map as $attribute}
				
				{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				
					{if $attribute.has_content}
						{if is_set($is_area_tematica)}
							{attribute_view_gui is_area_tematica=$is_area_tematica attribute=$attribute}
						{else}
							{attribute_view_gui attribute=$attribute}
						{/if}
					{/if}
				{elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				
					{if $attribute.has_content}
					{if $classi_senza_correlazioni_inline|contains($child.class_identifier)|not}
						<strong>{$attribute.contentclass_attribute_name}: </strong>
						{if is_set($is_area_tematica)}
							{attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$attribute}
						{else}
							{attribute_view_gui href=nolink attribute=$attribute}
						{/if}
					{/if}
					{/if}
				{/if}			
				{/foreach}
					

				</div>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
		</div>
		
		{/foreach}
		
	</div>
	
</div>
	
