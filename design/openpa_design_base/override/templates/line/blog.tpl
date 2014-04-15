<div class="{$style} col col-notitle">
    <div class="col-content"><div class="col-content-design">
        <div class="content-view-line">
            <div class="class-{$node.object.class_identifier} float-break">
                
                <h2><a href={$node.url_alias|ezurl} title="{$node.name|wash}">{$node.name|wash}</a></h2>
        
                <div class="attribute-description">
                    {attribute_view_gui attribute=$node.data_map.description}
                </div>
            </div>
        </div>
    </div>
</div>