{*
	GALLERIA FOTOGRAFICA
	con effetto carosello

	node	nodo della galleria
*}

<h2><a href={$node.url_alias|ezurl()}>{$node.name|wash()}</a></h2>

{set-block variable=overlay}
<div class="simple_overlay hide" id="gallery-{$node.node_id}"> 
<a class="prev">precedente</a>
<a class="next">successiva</a>
<div class="info"></div>
<img class="progress" alt="caricamento..." src={'loading.gif'|ezimage()} /> 
</div>
{/set-block}

{set scope=global persistent_variable=hash('bottom_include', $overlay)}

{ezcss_require(array( 'overlay-gallery.css' ) )}
{ezscript_require(array( 'ezjsc::jquery', 'jcarousel.js', 'tools-expose.js', 'tools-overlay.js' ) )}

<script type="text/javascript">
{literal}
$(document).ready(function() {
		$("#banner_carousel-{/literal}{$node.node_id}{literal}").jcarousel({scroll:2});
		$("#banner_carousel-{/literal}{$node.node_id}{literal} .attribute-image p.gallery a").overlay({ 
			target: '#gallery-{/literal}{$node.node_id}{literal}', 
			expose: '#f1f1f1' 
		}).gallery({ 
			speed: 800,
			template: '<strong>${title}</strong> <span>Immagine ${index} di ${total}</span>'
		});
});
{/literal}
</script>

<div class="banner-carousel photogallery float-break">

<ul id="banner_carousel-{$node.node_id}" class="jcarousel-list">

{def $banner_folder=fetch( 'content', 'list',  hash( 'parent_node_id', $node.node_id, 'limit', 30 ) )}
{foreach $banner_folder as $banner}
<li class="banner-carousel-item  jcarousel-item">
	<div class="attribute-image">
		<p class="no-js-hide gallery">{attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.data_map.image.content.imagelargeoverlay.url|ezurl}</p>
		<p class="no-js-show">{attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.url_alias|ezurl}</p>
	</div>
	<div class="attribute-name">
		<a href={$banner.url_alias|ezurl()}>{$banner.name|shorten(80)|wash}</a>
	</div>
</li>
{/foreach}

</ul>

</div>
