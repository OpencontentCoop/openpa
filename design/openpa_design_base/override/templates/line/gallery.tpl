<div class="content-view-line">
    <div class="class-folder">
    
    {if $node.data_map.image.has_content}
        <div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
    {else}
        {def $first_child = fetch('content', 'list', hash( 'parent_node_id', $node.node_id,
                                                    'class_filter_type', 'include',
                                                    'limit', 1,
                                                    'class_filter_array', array('image') ) )}
        <div class="main-image left">{attribute_view_gui attribute=$first_child[0].data_map.image image_class='small'}</div>
    {/if}
	
	<div class="blocco-titolo-oggetto">
		<div class="titolo-blocco-titolo">
		        <h3><a href={$node.url_alias|ezurl}>{$node.name|wash()}</a></h3>
		</div>
        {if $node|has_abstract()}
        <div class="attribute-short">
            {$node|abstract()}
        </div>
        {/if}
	</div>

    </div>
</div>
