{if openpaini( 'LinkSpeciali', 'NodoBannerHomepage' )}
{def $banner_folder=fetch( 'content', 'list', hash( 'parent_node_id', openpaini( 'LinkSpeciali', 'NodoBannerHomepage' ), 
                                                    'limit', 15,
                                                    'sort_by', array( 'priority', true() )
                                                    ) )}


{ezscript_require(array( 'ezjsc::jquery', 'jcarousel.js' ) )}


<script type="text/javascript">
{literal}
$(document).ready(function() {
	$("#banner_carousel").jcarousel({scroll:4});
});
{/literal}
</script>

<div class="banner-carousel-position">
<div class="banner-carousel float-break">
<h2 class="hide">{if is_set($banner_folder.name)}{$banner_folder.name|wash()}{else}Siti collegati{/if}</h2>
	<ul id="banner_carousel" class="jcarousel-list">
	
	{foreach $banner_folder as $banner}
	{if is_set($banner.data_map.image)}
	<li class="banner-carousel-item  jcarousel-item">
		<div class="attribute-image">
			<p>
			{if $banner.class_identifier|eq('link')}
				{attribute_view_gui attribute=$banner.data_map.image image_class=homecarousel 
						href=$banner.data_map.location.content|ezurl()}
			{else}
				{attribute_view_gui attribute=$banner.data_map.image image_class=homecarousel href=$banner.url_alias|ezurl()}
			{/if}
			</p>
		</div>

		<div class="attribute-name">
			<p>{$banner.name|shorten(14)|wash}</p>
		</div>
	</li>
	{/if}
	{/foreach}

	</ul>

</div>
</div>
{/if}