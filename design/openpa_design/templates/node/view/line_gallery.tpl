{*
	GALLERIA FOTOGRAFICA
	con effetto carosello

	node	nodo della galleria
	titolo  titolo da dare al blocco (opzionale)
*}

{if is_set($scope)|not() }
{def $scope = false()}
{/if}

{if is_set( $node )}

    {if $node.object.class_identifier|eq('gallery')}
        <h2><a href={$node.url_alias|ezurl()}>{$node.name|wash()}</a></h2>
    {elseif $scope|eq('attribute')}
        <h2>{$titolo}</h2> 
        {*<h2>Anteprima dell'articolo (con Zoom)</h2>*}
    {else}
        <h2>Immagini riferite a "{$node.name|wash()}"</h2>
    {/if}
    
    {if $scope|eq('attribute')}
        {def $nodes = array($node)}
    {else}
        {def $nodes = fetch( 'content', 'list',  hash( 'parent_node_id', $node.node_id, 'class_filter_type', 'include', 'class_filter_array', array('image', 'flash_player', 'ezflowmedia'), 'limit', 30 ) )}
    {/if}
    
    {def $node_id = $node.node_id}
{elseif is_set( $nodes )}
    
    {def $node_id = rand( 0, 10000 )}
    
{/if}

{ezcss_require(array( 'overlay-gallery.css' ) )}
{ezscript_require(array( 'jquery.js', 'jcarousel.js', 'tools-expose.js', 'tools-overlay.js' ) )}

<script type="text/javascript">
{literal}
<!--//--><![CDATA[//><!--
$(document).ready(function() {
    var target = $('<div class="simple_overlay hide" id="gallery"><a class="prev">precedente</a><a class="next">successiva</a><div class="info"></div><img class="progress" alt="caricamento..." src={/literal}{'loading.gif'|ezimage()}{literal} /> </div>');
    $('#page').append(target);
    $("#banner_carousel-{/literal}{$node_id}{literal}").jcarousel({scroll:2});
    $("#banner_carousel-{/literal}{$node_id}{literal} .attribute-image p.gallery a").overlay({ 
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

<div class="banner-carousel photogallery block float-break">

<ul id="banner_carousel-{$node_id}" class="jcarousel-list">


{foreach $nodes as $banner}
<li class="banner-carousel-item  jcarousel-item">
	<div class="attribute-image">
		{if $banner.can_edit}<a href={$banner.url_alias|ezurl()}>Vai all'immagine</a>{/if}
		<p class="no-js-hide gallery">
            {attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.data_map.image.content.imagelargeoverlay.url|ezroot}
        </p>
		<p class="no-js-show">
            {attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.url_alias|ezurl}            
        </p>
	</div>
	<div class="attribute-name">
		<p class="no-js-hide gallery-name">{$banner.name|shorten(80)|wash}</p>
		<a class="no-js-show gallery-name" href={$banner.url_alias|ezurl()}>{$banner.name|shorten(80)|wash}</a>
	</div>
</li>
{/foreach}

</ul>

</div>
