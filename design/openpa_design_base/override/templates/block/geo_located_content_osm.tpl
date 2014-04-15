{if and( is_set( $block.custom_attributes.limit ), 
            ne( $block.custom_attributes.limit, '' ) )}
    {def $limit = $block.custom_attributes.limit}
{else}
    {def $limit = '5'}
{/if}

{if and( is_set( $block.custom_attributes.width ), 
            ne( $block.custom_attributes.width, '' ) )}
    {def $width = $block.custom_attributes.width}
{else}
    {def $width = '460'}
{/if}

{if and( is_set( $block.custom_attributes.height ), 
            ne( $block.custom_attributes.height, '' ) )}
    {def $height = $block.custom_attributes.height}
{else}
    {def $height = '600'}
{/if}

{def $locations = fetch( 'content', 'tree', hash( 'parent_node_id', $block.custom_attributes.parent_node_id,
                                                  'class_filter_type', 'include',
                                                  'class_filter_array', array( $block.custom_attributes.class ),
                                                  'sort_by', array( 'name', true() ),
                                                  'limit', $limit ) )
     $attribute = $block.custom_attributes.attribute}

{def $domain=ezsys( 'hostname' )|explode('.')|implode('_')} 

<h2 class="block-title">{$block.name|wash()}</h2>

<div id="ezflb-map-{$block.id}" class="block-map" style="float: left; width: {$width}px; height: {$height}px"></div>

<div id="ezflb-map-right" class="block-markers" style="float: left; width: 30%; margin-right:5px">
<ul>
{foreach $locations as $index => $location}
    <li><a id="ezflb-pointer-{$block.id}-{$index}" class="marker" href="{$location.url_alias|ezurl('no')}">{$location.name|wash()}</a></li>
{/foreach}
</ul>
</div>	 
	 

<script src="http://openlayers.org/api/OpenLayers.js" type="text/javascript" ></script>
{ezscript_require( array('ezjsc::jquery', 'ezflosmview.js') )}

<script type="text/javascript">
var data{$block.id} = [];
{foreach $locations as $location}
{if $location.data_map[$attribute].has_content}
data{$block.id}.push( 
{ldelim}
point: new OpenLayers.LonLat( {$location.data_map[$attribute].content.longitude}, {$location.data_map[$attribute].content.latitude} ), 
address: "<h3><a href='{$location.url_alias|ezurl('no')}'>{$location.name|wash()}</a></h3>{$location.data_map[$attribute].content.address}"
{rdelim} 
);
{/if}
{/foreach}

$(document).ready(function(){ldelim}
	eZFLOsmView( '{$block.id}', data{$block.id} );
{rdelim} );
</script>


{undef $locations}
