{def $valid_node = $block.valid_nodes[0]}

<!-- BLOCK: START -->

<div class="block-type-singolo block-{$block.view}">
    <div class="attribute-image">
        {attribute_view_gui href=$valid_node.url_alias|ezurl() attribute=$valid_node.data_map.image image_class='original'}
    </div>

    <div class="trans-background">&nbsp;</div>

    <div class="attribute-link">
        <a href="{$valid_node.url_alias|ezurl(no)}">{$valid_node.name}</a>
    </div>
</div>

<!-- BLOCK: END -->