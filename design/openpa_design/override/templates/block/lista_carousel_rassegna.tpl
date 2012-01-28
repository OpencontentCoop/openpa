{*
	BLOCCO
	Vista accordion
*}

	{def $customs=$block.custom_attributes $errors=false() $sort_array=array() $classes=array()}

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


	{if $customs.includi_classi|eq('articolo')}
		{set $classes=$customs.includi_classi|explode(',')}
	        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
								'class_filter_type', 'include', 'class_filter_array', $classes,
								'depth', $depth, 'limit', 20, 'sort_by', array('published', false()) 
							     ) )}
	{elseif $customs.escludi_classi|ne('')}
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


<div class="block-type-lista block-{$block.view}">

	{if $block.name}
		<h2 class="block-title">
			<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">{$block.name}</a>
		</h2>
	{/if}


{ezcss_require(array( 'overlay-gallery.css' ) )}
{ezscript_require(array( 'ezjsc::jquery', 'jcarousel.js', 'tools-expose.js', 'tools-overlay.js' ) )}

<script type="text/javascript">
{literal}
<!--//--><![CDATA[//><!--
$(document).ready(function() {
		var target = $('<div class="simple_overlay hide" id="gallery"><a class="prev">precedente</a><a class="next">successiva</a><div class="info"></div><img class="progress" alt="caricamento..." src={/literal}{'loading.gif'|ezimage()}{literal} /> </div>');
		$('body').append(target);
		$("#banner_carousel-{/literal}{$node.node_id}{literal}").jcarousel({scroll:2});
		$("#banner_carousel-{/literal}{$node.node_id}{literal} .attribute-image p.gallery a").overlay({ 
			target: '#gallery', 
			expose: '#f1f1f1' 
		}).gallery({ 
			speed: 800,
			template: '<strong>${title}</strong> <span>Immagine ${index} di ${total}</span>'
		});
});
//--><!]]>
{/literal}
</script>


<div class="banner-carousel photogallery float-break">

<ul id="banner_carousel-{$node.node_id}" class="jcarousel-list">

{foreach $children as $banner}
<li class="banner-carousel-item  jcarousel-item">
	<div class="attribute-name-title">
		<a href={$banner.url_alias|ezurl()} title="Leggi l'articolo completo">{$banner.name|wash}</a>
	</div>
	<div class="attribute-image">
		{*<a href={$banner.url_alias|ezurl()} title="Leggi l'articolo completo">{$banner.name|wash}</a>*}
		<p class="no-js-hide gallery">{attribute_view_gui attribute=$banner.data_map.image title="guarda l'anteprima dell'articolo" image_class=gallerythumbnail href=$banner.data_map.image.content.imagelargeoverlay.url|ezurl}</p>
		<p class="no-js-show">{attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.url_alias|ezurl}</p>
	</div>
	<div class="attribute-name">
		<p class="no-js-hide gallery-name">
			{if $banner.data_map.testata.has_content}
				Tratto da <strong> {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink 
							attribute=$banner.data_map.testata} </strong>
				{if $banner.data_map.pagina.content|ne(0)}
					a pagina {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.pagina}
					 {if $banner.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.pagina_continuazione}
					 {/if}
				{/if}
						
				{if $banner.data_map.autore.has_content}
		 			di {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.autore}
				{/if}
					
			{/if}
				    
			{if $banner.data_map.argomento_articolo.has_content}
		 		Su: <strong>{attribute_view_gui is_area_tematica=$is_area_tematica 
						href=nolink attribute=$banner.data_map.argomento_articolo} </strong>
			{/if}
		</p>
		{*
		<p class="no-js-hide gallery-name">{$banner.name|shorten(80)|wash}</p>
		<a class="no-js-show gallery-name" href={$banner.url_alias|ezurl()}>{$banner.name|shorten(80)|wash}</a>
		*}
	</div>
</li>
{/foreach}

</ul>

</div>



</div>



{*

	
{ezscript_require(array( 'ezjsc::jquery', 'jcarousel.js' ) )}

<script type="text/javascript">
{literal}
$(document).ready(function() {
        $("#carousel-{/literal}{$block.id}{literal} ul.jcarousel-list").jcarousel({scroll:2});
		
		var canvasWidth = parseInt($("#carousel-{/literal}{$block.id}{literal}").width());
		var containerPaddingLeft = parseInt($("#carousel-{/literal}{$block.id}{literal} div.jcarousel-container-horizontal").css('padding-left'));
		var containerPaddingRight = parseInt($("#carousel-{/literal}{$block.id}{literal} div.jcarousel-container-horizontal").css('padding-right'));
		var containerBorderLeft = parseInt($("#carousel-{/literal}{$block.id}{literal} div.jcarousel-container-horizontal").css('border-left-width'));
		var containerBorderRight = parseInt($("#carousel-{/literal}{$block.id}{literal} div.jcarousel-container-horizontal").css('border-right-width'));
		var containerWidth = canvasWidth - containerPaddingLeft - containerPaddingRight - containerBorderLeft - containerBorderRight;
		$("#carousel-{/literal}{$block.id}{literal} div.jcarousel-container-horizontal, #carousel-{/literal}{$block.id}{literal} div.jcarousel-clip").width(containerWidth);
		
		var defaultItemWidth = parseInt($("#carousel-{/literal}{$block.id}{literal} li.jcarousel-item").css('width'));
		var defaultItemPaddingLeft = parseInt($("#carousel-{/literal}{$block.id}{literal} li.jcarousel-item").css('padding-left'));
		var defaultItemPaddingRight = parseInt($("#carousel-{/literal}{$block.id}{literal} li.jcarousel-item").css('padding-right'));
		var numItems = Math.ceil(containerWidth/defaultItemWidth);
		var newItemWidth = (parseInt(containerWidth/numItems) - defaultItemPaddingLeft - defaultItemPaddingRight);
		$("#carousel-{/literal}{$block.id}{literal} li.jcarousel-item").width(newItemWidth);
		
		var ItemsWidth = (newItemWidth + defaultItemPaddingLeft + defaultItemPaddingRight) * $("#carousel-{/literal}{$block.id}{literal} li.jcarousel-item").length ;
		$("#carousel-{/literal}{$block.id}{literal} ul.jcarousel-list").css('width', ItemsWidth);
});
{/literal}
</script>

<div id="carousel-{$block.id}" class="banner-carousel float-break">
<h2 class="hide">{if is_set($nodo.name)}{$nodo.name|wash()}{else}Siti collegati{/if}</h2>
        <ul class="jcarousel-list">
		{if $children|count()}
        	{foreach $children as $banner}
        		{if and(is_set($banner.data_map.image),$banner.data_map.image.has_content)}
        			<li class="banner-carousel-item  jcarousel-item" id="node-{$banner.node_id}">
				<div class="attribute-name-title">
                       			{$banner.name|wash}
                		</div>
                		<div class="attribute-image-rassegna">
                        		<p>
					{if $banner.class_identifier|eq('link')}
						{attribute_view_gui attribute=$banner.data_map.image image_class=homecarousel 
							href=$banner.data_map.location.content|ezurl()}
					{else}
						{attribute_view_gui attribute=$banner.data_map.image image_class=homecarousel 
							href=$banner.url_alias|ezurl()}
					{/if}
					</p>

                		</div>

				{if is_set($banner.data_map.testata)}
				<div class="attribute-name-rassegna">
				   {if $banner.data_map.testata.has_content}
					<p>Tratto da 
					<strong> {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink attribute=$banner.data_map.testata} </strong>
				   	{if $banner.data_map.pagina.content|ne(0)}
					a pagina {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.pagina}
					 {if $banner.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.pagina_continuazione}
					 {/if}
					{/if}
						
					   {if $banner.data_map.autore.has_content}
		 				(di {attribute_view_gui is_area_tematica=$is_area_tematica attribute=$banner.data_map.autore})
				    	   {/if}
					</p>
				    {/if}
				    
				    {if $banner.data_map.argomento_articolo.has_content}
		 			<p>Su: 
					 <strong>
					 {attribute_view_gui is_area_tematica=$is_area_tematica href=nolink 
						attribute=$banner.data_map.argomento_articolo}
					 </strong>
					</p>
				    {/if}
				</div>
				{/if}

      			</li>
        		{/if}
        	{/foreach}
		{/if}		
        </ul>

</div>

</div>

*}
	
