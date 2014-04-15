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
{case}{/case}
{/switch}

{if $customs.escludi_classi|ne('')}
    {set $classes=$customs.escludi_classi|explode(',')}
    {set $classes = merge($classes, openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', array())) }
    {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                    'class_filter_type', 'exclude',
                                                    'class_filter_array', $classes,
                                                    'depth', $depth,
                                                    'limit', $limit,
                                                    'sort_by', $sort_array) )}
{elseif $customs.includi_classi|ne('')}
    {set $classes=$customs.includi_classi|explode(',')}
    {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', $classes,
                                                    'depth', $depth,
                                                    'limit', $limit,
                                                    'sort_by', $sort_array) )}
{else}
    {set $classes = openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', array())}
        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                        'class_filter_type', 'exclude',
                                                        'class_filter_array', $classes,
                                                        'depth', $depth,
                                                        'limit', $limit,
                                                        'sort_by', $sort_array) )}
{/if}


<div class="block-type-lista block-{$block.view}">

	{if $block.name}
		<h2 class="block-title">
			<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">{$block.name}</a>
		</h2>
	{/if}
	
{ezscript_require(array( 'ezjsc::jquery', 'jcarousel.js' ) )}

<script type="text/javascript">
{literal}
$(document).ready(function() {
    $("#carousel-{/literal}{$block.id}{literal} ul.jcarousel-list").jcarousel({scroll:4});
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
                		<div class="attribute-image">
                        		<p>
					{if $banner.class_identifier|eq('link')}
						{attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail  href=$banner.data_map.location.content|ezurl()}
					{else}
						{attribute_view_gui attribute=$banner.data_map.image image_class=gallerythumbnail href=$banner.url_alias|ezurl()}
					{/if}
					</p>

                		</div>

                		<div class="attribute-name">
                       		<p>{$banner.name|wash}</p>
                		</div>
        			</li>
        		{/if}
        	{/foreach}
		{/if}		
        </ul>

</div>

</div>
	
