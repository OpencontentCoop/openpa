{def $valid_node = $block.valid_nodes[0]}
<div class="widget {$block.view}">
    <div class="widget_title">
        <h3>{node_view_gui content_node=$valid_node view=text_linked text=$block.name}</h3>
    </div>
    <div class="widget_content">
        {node_view_gui content_node=$valid_node view=line image_class=small}
    </div>
</div>

