{def $valid_node = $block.valid_nodes[0]}

<div class="block-type-singolo block-singolo_imgtit block-{$block.view}">

    <div class="attribute-header">
	{if $block.name}
		<h2 class="block-title">
			{$block.name}
		</h2>
	{/if}
    </div>


    <div class="attribute-image">
        {attribute_view_gui href=$valid_node.url_alias|ezurl() attribute=$valid_node.data_map.image image_class='singolo_interne'}
    </div>

    <div class="trans-background">&nbsp;</div>

    <div class="attribute-link">
        <a href="{$valid_node.url_alias|ezurl(no)}">{$valid_node.name}</a>
    </div>
</div>