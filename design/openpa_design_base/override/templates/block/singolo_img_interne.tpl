{def $valid_node = $block.valid_nodes[0]}

<!-- BLOCK: START -->

<div class="block-type-singolo block-singolo_img block-{$block.view}">

    <div class="attribute-header">
	{if $block.name}
		<h2 class="block-title">
			{$block.name}
		</h2>
	{/if}
    </div>

    <div class="attribute-image">
        {attribute_view_gui attribute=$valid_node.data_map.image image_class='singolo_interne'}
    </div>
</div>

<!-- BLOCK: END -->
