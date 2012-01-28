{def $has_image = false()
     $has_text = false()
     $shorten = 400}
     
<div class="{$style} col float-break col-image">
    <div class="col-image">
        <div class="image text-center">
            {if is_set( $node.data_map.image )}
                {if $node.data_map.image.has_content}
                    {attribute_view_gui attribute=$node.data_map.image image_class=line_icon}
                    {set $has_image = true()}
                {/if}
            {/if}
            {if and( is_set( $node.data_map.file ), $has_image|not() )}
                {if $node.data_map.file.has_content}
                    {if $node.object.data_map.file.content.mime_type|eq('application/pdf')}
                        {set $has_image = $node.object.data_map.file.content.filepath|pdfpreview( 50, 50, 1, $node.name|slugize() )|ezroot}
                        <img src={$has_image} alt="{$node.name|wash()}">
                    {/if}
                {/if}
            {/if}
        </div>
    </div>
    <div class="col-content"><div class="col-content-design">
        <div class="content-view-line">
            <div class="class-{$node.object.class_identifier}">

                <p class="line-date">{include name=date node=$node uri='design:parts/common/date.tpl'}</p>

                {if is_set( $node.url_alias )}
                    <h2><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h2>
                {else}
                    <h2>{$node.name|wash()}</h2>
                {/if}
                                
                <p class="line-preview">
                {$node.highlight}
                </p>
                
            </div>
        </div>
    </div></div>
</div>