{def $valid_nodes = $block.valid_nodes
    $children = array()
    $subchildren = array()
    $children_count = 0
    $item_per_column = 0
    $classi_iosono_padre = openpaini( 'GestioneClassi', 'classi_iosono_padre' )
    $classi_iosono_figli = openpaini( 'GestioneClassi', 'classi_iosono_figli' )
    $classi_da_escludere = openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow' )}

<div class="widget_tabs {$block.view}">
    <ul class="nav nav-tabs" role="tablist">
        {foreach $valid_nodes as $index => $node}
            <li{if $index|eq(0)} class="active"{/if}>
                <a href="#{$node.name|slugize()}" role="tab" data-toggle="tab">
                    {$node.name|wash()}
                </a>
            </li>
        {/foreach}
    </ul>
    <div class="tab-content">
        {foreach $valid_nodes as $index => $node}
            <div class="tab-pane{if $index|eq(0)} active{/if}" id="{$node.name|slugize()}">
                {set $children = fetch( 'content', 'list', hash( 'parent_node_id', $node.node_id,
                                                                    'class_filter_type', 'exclude',
                                                                    'class_filter_array', $classi_da_escludere,
                                                                    'sort_by', $node.sort_array,
                                                                    'limit', 20 ) )
                $subchildren = array()
                $children_count = $children|count()}

                {foreach $children as $index => $child}

                    {if $i|eq(0)}
                        <div class="row">
                    {/if}

                    <h4><a title="Informazioni su {$child.name|wash}" href={$child.object.main_node.url_alias|ezurl()}>{$child.name|wash()}</a></h4>
                    {set $subchildren=fetch( 'content', 'list', hash( 'parent_node_id', $child.node_id,
                                                                        'class_filter_type', 'exclude',
                                                                        'class_filter_array', $classi_da_escludere,
                                                                        'sort_by', $node.sort_array,
                                                                        'limit', 10 ) )}
                    {if $subchildren|count()|gt(0)}
                        {foreach $subchildren as $subchild}<a title="Informazioni su {$subchild.name|wash}" href={$subchild.object.main_node.url_alias|ezurl()}>{$subchild.name|wash()}</a>{delimiter}, {/delimiter}{/foreach}
                    {else}
                        {if $child.data_map.abstract.has_content}
                            {attribute_view_gui attribute=$child.data_map.abstract}
                        {else}
                            In fase di completamento
                        {/if}
                    {/if}


                {if eq(sum($i,1)|mod($items_per_row),0)}
                    </div>
                    <div class="row">
                {/if}
                {if $i|eq(count($items)|sub(1))}
                    </div>
                {/if}

                {/foreach}
            </div>
        {/foreach}
    </div>
</div>