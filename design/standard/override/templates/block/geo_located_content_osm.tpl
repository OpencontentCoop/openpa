{set_defaults( hash('show_title', true()) )}

{if and( is_set( $block.custom_attributes.height ), ne( $block.custom_attributes.height, '' ) )}
    {def $height = $block.custom_attributes.height}
{else}
    {def $height = '600'}
{/if}

{def $root = ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
{if is_set( $block.custom_attributes.parent_node_id )}
    {set $root = $block.custom_attributes.parent_node_id}
{/if}


{if and( $show_title, $block.name|ne('') )}
<div class="widget {$block.view}">

    <div class="widget_title">
        <h3>{$block.name|wash()}</h3>
    </div>
    <div class="widget_content">
        {/if}

        {include uri='design:parts/children/map.tpl' view='line' node=hash( node_id, root)}

        {if and( $show_title, $block.name|ne('') )}
    </div>
</div>
{/if}
