{def $valid_node = $block.valid_nodes[0]}

<div class="widget {$block.view}">

    <div class="widget_title">
        <h3>{node_view_gui content_node=$valid_node view=text_linked text=$block.name}</h3>
    </div>
    <div class="widget_content">

        {if $block.name|ne('')}
        <h4><a href={$valid_node.url_alias|ezurl()} title="Link a {$valid_node.name|wash()}">{$valid_node.name|wash()}</a></h4>
        {/if}

        {if and( is_set($valid_node.data_map.image), $valid_node.data_map.image.has_content )}
            {attribute_view_gui alt=$valid_node.name|wash() attribute=$valid_node.data_map.image image_class='medium'}
        {/if}

    </div>
</div>

