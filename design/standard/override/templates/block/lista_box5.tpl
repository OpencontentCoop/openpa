{def $valid_nodes = $block.valid_nodes
    $valid_nodes_count = $valid_nodes|count()
    $children=array()
    $classes= openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow', array())}
    {set_defaults( hash('show_title', true()) )}

{if and( $show_title, $block.name|ne('') )}
<div class="widget {$block.view}">
    <div class="widget_title">
        <h3><a href={$openpa.root_node.url_alias|ezurl()}>{$block.name|wash()}</a></h3>
    </div>
    {/if}
    {if $valid_nodes_count|eq(1)}

        {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[0].node_id,
                                                        'class_filter_type', 'exclude',
                                                        'class_filter_array', $classes,
                                                        'depth', 10,
                                                        'limit', 4,
                                                        'sort_by', array('published', false())  ))}
        {if $children|count()}
            <ul>
                {foreach $children as $child}
                    <li>
                        <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                        {node_view_gui content_node=$child view=text_linked}
                    </li>
                {/foreach}
            </ul>
        {else}
            <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
        {/if}



    {elseif $valid_nodes_count|eq(2)}

        <div class="row">

            <div class="col-md-6">
                {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[0].node_id,
                                                                'class_filter_type', 'exclude',
                                                                'class_filter_array', $classes,
                                                                'depth', 10,
                                                                'limit', 4,
                                                                'sort_by', array('published', false())  ))}
                {if $children|count()}
                    <h4>{$valid_nodes[0].name|wash()}</h4>
                    <ul>
                        {foreach $children as $child}
                            <li>
                                <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                                {node_view_gui content_node=$child view=text_linked}
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
                {/if}
            </div>

            <div class="col-md-6">
                {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[1].node_id,
                                                                'class_filter_type', 'exclude',
                                                                'class_filter_array', $classes,
                                                                'depth', 10,
                                                                'limit', 4,
                                                                'sort_by', array('published', false())  ))}
                {if $children|count()}
                    <h4>{$valid_nodes[1].name|wash()}</h4>
                    <ul>
                        {foreach $children as $child}
                            <li>
                                <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                                {node_view_gui content_node=$child view=text_linked}
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
                {/if}
            </div>
        </div>

    {elseif $valid_nodes_count|ge(3)}

        <div class="row">

            <div class="col-md-4">
                {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[0].node_id,
                                                                'class_filter_type', 'exclude',
                                                                'class_filter_array', $classes,
                                                                'depth', 10,
                                                                'limit', 4,
                                                                'sort_by', array('published', false())  ))}
                {if $children|count()}
                    <h4>{$valid_nodes[0].name|wash()}</h4>
                    <ul>
                        {foreach $children as $child}
                            <li>
                                <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                                {node_view_gui content_node=$child view=text_linked}
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
                {/if}
            </div>

            <div class="col-md-4">
                {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[1].node_id,
                                                            'class_filter_type', 'exclude',
                                                            'class_filter_array', $classes,
                                                            'depth', 10,
                                                            'limit', 4,
                                                            'sort_by', array('published', false())  ))}
                                                            {if $children|count()}
                    <h4>{$valid_nodes[1].name|wash()}</h4>
                    <ul>
                        {foreach $children as $child}
                            <li>
                                <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                                {node_view_gui content_node=$child view=text_linked}
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
                {/if}
            </div>

            <div class="col-md-4">
                {set $children=fetch( 'content', 'tree', hash( 'parent_node_id', $valid_nodes[2].node_id,
                                                                'class_filter_type', 'exclude',
                                                                'class_filter_array', $classes,
                                                                'depth', 10,
                                                                'limit', 4,
                                                                'sort_by', array('published', false())  ))}
                {if $children|count()}
                    <h4>{$valid_nodes[2].name|wash()}</h4>
                    <ul>
                        {foreach $children as $child}
                            <li>
                                <span class="details">{$child.object.published|datetime(custom, '%j %F %Y')}</span>
                                {node_view_gui content_node=$child view=text_linked}
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p>Nessun contenuto disponibile in {$valid_nodes[0].name|wash()}</p>
                {/if}
            </div>
        </div>

    {/if}

    {if and( $show_title, $block.name|ne('') )}
</div>
{/if}
