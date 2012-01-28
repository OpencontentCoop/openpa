{def $has_image = false()
     $has_text = false()
     $shorten = 400}
     
<div class="{$style} col col-notitle">

    <div class="col-content"><div class="col-content-design">
        <div class="content-view-line">
            <div class="class-{$node.object.class_identifier} float-break">

                {if is_set( $node.url_alias )}
                    <h2><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h2>
                {else}
                    <h2>{$node.name|wash()}</h2>
                {/if}
                
                <small class="line-date">{include name=date node=$node uri='design:parts/common/date.tpl'}</small>
                
                

                
                    {if is_set( $node.data_map.image )}
                        {if $node.data_map.image.has_content}
                            <div class="object-left">
                            {attribute_view_gui attribute=$node.data_map.image image_class=small}
                            </div>
                            {set $has_image = true()}
                        {/if}
                    {/if}
                    {if and( is_set( $node.data_map.file ), $has_image|not() )}
                        {if $node.data_map.file.has_content}
                            {if $node.object.data_map.file.content.mime_type|eq('application/pdf')}
                                {set $has_image = $node.object.data_map.file.content.filepath|pdfpreview( 150, 150, 1, $node.name|slugize() )|ezroot}
                                <div class="object-left">
                                <img src={$has_image} alt="{$node.name|wash()}">
                                </div>
                            {/if}
                        {/if}
                    {/if}

                    {if $node|has_abstract()}
                        <p class="line-preview">{$node|abstract()|openpa_shorten( $shorten )}</p>
                        {set $has_text = true()}
                    {/if}
                
                
                
                {if and( $has_text|not(), $node.children )}
                <ul class="subchild-menu">
                    {def $children_count = fetch_alias( 'children_count', hash( 'parent_node_id', $node.node_id ) )
                         $limit = 5
                         $other = $children_count|sub( $limit )}
                    {if $other|eq( 1 )}
                        {set $limit = inc( $limit )
                             $other = 0}
                    {/if}
                    {def $children = fetch_alias( 'children', hash( 'parent_node_id', $node.node_id,
                                                       'limit', $limit, 
                                                       'sort_by', $node.sort_array ) )}

                    {foreach $children as $sub_child}                    
                        {if and( is_set( $sub_child.data_map.file ), $sub_child.data_map.file.has_content )}
                            <li class="file">{attribute_view_gui attribute=$sub_child.data_map.file}</li>
                        {else}
                            <li><a href={$sub_child.url_alias|ezurl()} title="Vai a {$sub_child.name|wash()}">{$sub_child.name|wash()}</a></li>
                        {/if}
                    {/foreach}
                    
                    {if $other|gt( 0 )}
                        <li><a href={concat( $node.url_alias, '/(offset)/', $limit )|ezurl()} title="Visualizza gli elementi in {$node.name|wash()}">... e altri {$other} elementi</a></li>
                    {/if}
                </ul>
                {set $has_text = true()}
                {/if}
                
            </div>
        </div>
    </div></div>
</div>