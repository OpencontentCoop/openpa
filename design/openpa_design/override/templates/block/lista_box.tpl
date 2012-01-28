{*
	Vista blocco simile a numerini che però visualizza uno sotto l'altro gli elementi
	Questa vista ammette UNICAMENTE OGGETTI DI TIPO AVVISO, per aggiungere altri tipi di oggetti implementare l'array $class_filter
*}

	{def $customs=$block.custom_attributes $errors=false() $sort_array=array() $classes=array()
	     $classi_senza_data_inline = ezini( 'GestioneClassi', 'classi_senza_data_inline', 'content.ini')
	     $classi_blocco_particolari = ezini( 'GestioneClassi', 'classi_blocco_particolari', 'content.ini')
	     $classi_senza_correlazioni_inline = ezini( 'GestioneClassi', 'classi_senza_correlazioni_inline', 'content.ini')
 	     $attributes_to_show=array('organo_competente', 'circoscrizione')
	     $attributes_with_title=array('servizio','argomento')
	     $curr_ts = currentdate()
	}

	{if $customs.limite|gt(0)}
        	{def $limit=$customs.limite}
	{else}
        	{def $limit=10}
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

	{if $customs.escludi_classi|ne('')}
		{set $classes=$customs.escludi_classi|explode(',')}
		{set $classes = merge($classes, ezini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', 'content.ini')) }
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'exclude', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
	{elseif $customs.includi_classi|ne('')}
		{set $classes=$customs.includi_classi|explode(',')}

		{if $customs.includi_classi|ne('news')}
	         {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'include', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
		{else}
		 {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
							'attribute_filter', array( 'and',
               			                                 array( 'news/data_inizio_pubblicazione_news', '<=', $curr_ts  ),
                                       			         array( 'news/data_fine_pubblicazione_news', '>=', $curr_ts  )),
                                                        'class_filter_type', 'include', 'class_filter_array', array('news'),
                                                        'depth', $depth, 'limit', $limit, 'sort_by', array('published', false())) )}



		{/if}
	{else}
		{set $classes = ezini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', 'content.ini')}
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'exclude', 'class_filter_array', $classes,
								'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
	{/if}

{*
{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}

<script type="text/javascript">
{literal}
$(function() {
	$("#{/literal}x{$block.id}{literal}").tabs().tabs("rotate", 4000);
	var rotation = true;
	$(".rotation-control").bind('click', function() {
		if (rotation){
			$("#{/literal}x{$block.id}{literal}").tabs("rotate", 0);
			rotation = false;
			$(this).removeClass('started');
			$(this).addClass('stopped');
		}else{
			$("#{/literal}x{$block.id}{literal}").tabs("rotate", 4000);
			rotation = true;
			$(this).removeClass('stopped');
			$(this).addClass('started');
		}
	});
});
{/literal}
</script>
*}

<div class="block-type-lista block-{$block.view}">

	{if $block.name}
		<h2 class="block-title">
		<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">{$block.name}</a>
		</h2>
	{/if}

	<div class="border-box box-gray box-numeri">
	<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
	<div class="border-ml"><div class="border-mr"><div class="border-mc">
	<div class="border-content">
	
		<div id="x{$block.id}" class="ui-tabs">	
					
			<div class="num-tabs-panels">
				{foreach $children as $index => $child}
				<div id="{$child.name|slugize()}-{$child.node_id}" class="Blocco_Lista_Box">

				{if $child.class_identifier|eq('news')}
				
					<div class="attribute-header">
					<h3>
	 				   <a{if $index|eq(0)} class="active"{/if} href={$child.parent.url_alias|ezurl()} title="{$child.parent.name|wash()}">{$child.parent.name|wash()}</a>
					</h3>
					</div>
					
					<div class="attribute-small">	
					{if $classi_senza_data_inline|contains($child.class_identifier)|not}
						di {$child.object.published|l10n(date)}
					{/if}
					</div>
					{if $child.parent.data_map.image.has_content}
						<div class="attribute-image no-js-hide">
							{attribute_view_gui attribute=$child.parent.data_map.image image_class=lista_accordion}
						</div>
					{else}
 					   <img class="image-medium" 
						src={concat('icons/crystal/64x64/mimetypes/',$child.parent.class_identifier,'.png')|ezimage()}
 						alt="{$child.parent.class_identifier}" title="{$child.parent.class_identifier}" />
					{/if}
					<div class="no-js-hide">
					{if is_set($child.data_map.testo_news)}
						{if $child.data_map.testo_news.has_content}
							{attribute_view_gui attribute=$child.data_map.testo_news}
						{/if}	
					{/if}
					</div>

					{* mostro gli altri attributi *}
					{foreach $child.parent.data_map as $attribute}
				
					{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				
						{if $attribute.has_content}
						 <div class="no-js-hide">{attribute_view_gui is_area_tematica=$is_area_tematica attribute=$attribute}</div>
						{/if}
					{elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				
						{if $attribute.has_content}
						{if $classi_senza_correlazioni_inline|contains($child.class_identifier)|not}
							<div class="no-js-hide">
							<strong>{$attribute.contentclass_attribute_name}: </strong>
							{if is_set($is_area_tematica)}
								{attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$attribute}
							{else}
								{attribute_view_gui href=nolink attribute=$attribute}
							{/if}
							</div>
						{/if}
						{/if}
					{/if}			
					{/foreach}

				{else}

				{*if $classi_blocco_particolari|contains($child.class_identifier)*}

					{*if is_set($child.data_map.data)}
						{if $child.data_map.data.has_content}
						<div class="attribute-date">
							{$child.data_map.data.data_int|datetime(custom, '%j %F %Y')}
						</div>
						{/if}
					{/if*}
					
					<div class="attribute-header">
					<h3>
					{if $child.class_identifier|eq('link')}
        					<a href={$child.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">{$child.name|wash()}</a>
					{else}
						<a{if $index|eq(0)} class="active"{/if} href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
					{/if}
					</h3>
					</div>

					<div class="attribute-small">
					{* mostro (eventualmente) la data di pubblicazione (indotta) *}		
					{if $classi_senza_data_inline|contains($child.class_identifier)|not}
						di {$child.object.published|l10n(date)}
					{/if}
					</div>

					{if and(is_set($child.data_map.image), $child.data_map.image.has_content)}
						<div class="attribute-image no-js-hide">
							{attribute_view_gui attribute=$child.data_map.image image_class=lista_accordion}
						</div>
						
					{else}
 					   <img class="image-medium" 
						src={concat('icons/crystal/64x64/mimetypes/',$child.class_identifier,'.png')|ezimage()}
 						alt="{$child.class_identifier}" title="{$child.class_identifier}" />
					{/if}
					
					<div class="no-js-hide">
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
					{else}
						<div class="attribute-node">
							{node_view_gui content_node=$child view='line'}
						</div>
					{/if}	
					</div>					
				
				
					{* mostro gli altri attributi *}
					{foreach $child.data_map as $attribute}
				
					{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				
						{if $attribute.has_content}
						 <div class="no-js-hide">{attribute_view_gui is_area_tematica=$is_area_tematica attribute=$attribute}</div>
						{/if}
					{elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				
						{if $attribute.has_content}
						{if $classi_senza_correlazioni_inline|contains($child.class_identifier)|not}
							<div class="no-js-hide">
							<strong>{$attribute.contentclass_attribute_name}: </strong>
							{if is_set($is_area_tematica)}
								{attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$attribute}
							{else}
								{attribute_view_gui href=nolink attribute=$attribute}
							{/if}
							</div>
						{/if}
						{/if}
					{/if}			
					{/foreach}
				
				{/if}

				</div>
				{delimiter}{if $index|lt(3)}<hr class="no-js-show clear" />{/if}{/delimiter}
				{/foreach}
			</div>
			{*
			<div class="rotation-control started no-js-hide"></div>
			<ul class="num-tabs no-js-hide float-break">						 
			{foreach $children as $index => $child}
				<li><a href="#{$child.name|slugize()}-{$child.node_id}">{$index|inc()}</a></li>
			{/foreach}
			</ul>			
			*}
			<div class="no-js-show"><a href={$nodo.url_alias|ezurl()} title="{$nodo.name|wash()}">Vai a {$nodo.name|wash()}<span class="arrow-blue-r"></span></a></div>
			
		</div>
		
	</div>
	</div></div></div>
	<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
	</div>	

</div>
