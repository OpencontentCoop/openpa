<div class="no-js-hide">
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
    {def $width = '100%'}
{/if}

{if and( is_set( $block.custom_attributes.height ), 
            ne( $block.custom_attributes.height, '' ) )}
    {def $height = $block.custom_attributes.height}
{else}
    {def $height = '600px'}
{/if}

{def $source = ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
{if is_set( $block.custom_attributes.parent_node_id )}
    {set $source = $block.custom_attributes.parent_node_id}
{/if}

<h2 class="block-title">{$block.name|wash()}</h2>

{include mame = 'map'
         uri = 'design:parts/map.tpl'
         parent_node_id = $source
         class = $block.custom_attributes.class
         height = $height
         width = $width
         limit = $limit
    }
    
{undef $limit $width $height $source}
</div>