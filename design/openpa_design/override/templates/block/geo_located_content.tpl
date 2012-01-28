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

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={ezini('SiteSettings', concat('GMapsKey_', $domain))}" type="text/javascript"></script>
{ezscript_require( 'ezflgmapview.js' )}

<script type="text/javascript">
<!--
    var data{$block.id} = [];
    
    {foreach $locations as $location}
    {if $location.data_map[$attribute].has_content}
    data{$block.id}.push( {ldelim}
                            point: new GLatLng( {$location.data_map[$attribute].content.latitude}, {$location.data_map[$attribute].content.longitude} ),
                            address: "<h3><a href='{$location.url_alias|ezurl('no')}'>{$location.name|wash()}</a></h3>{$location.data_map[$attribute].content.address}"
                        {rdelim} );
    {/if}
    {/foreach}

    eZFLGMapAddListener( window, 'load', function(){ldelim} eZFLGMapView( '{$block.id}', data{$block.id} ) {rdelim}, false );
-->
</script>

<h2 class="block-title">{$block.name|wash()}</h2>

<div id="ezflb-map-{$block.id}" class="block-map" style="float: left; width: {$width}px; height: {$height}px"></div>

<div id="ezflb-map-right" class="block-markers" style="float: left; width: 30%; margin-right:5px">
<ul>
{foreach $locations as $index => $location}
    <li><a id="ezflb-pointer-{$block.id}-{$index}" href="{$location.url_alias|ezurl('no')}">{$location.name|wash()}</a></li>
{/foreach}
</ul>
</div>

