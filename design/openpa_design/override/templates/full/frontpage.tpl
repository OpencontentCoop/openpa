{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{if $node.object.data_map.page.has_content}

<div class="content-view-full">
    <div class="class-frontpage">

    <div class="attribute-page">
    {attribute_view_gui attribute=$node.object.data_map.page}
    </div>

    </div>
</div>
{else}
    
    <h1>{$node.name|wash()}</h1>
    
    
    {def $page_limit = 20
         $classes = openpaini( 'Classi', 'FullFigliDaEscludereDefault', array() )|merge( openpaini( 'Classi', 'FullRiquadroModuli', array() ) )
         $children_count=fetch_alias( 'children_count', hash( 'parent_node_id', $node.node_id,
                                                              'class_filter_type', 'exclude',
                                                              'class_filter_array', $classes ) )}
    
    {if $children_count}
    <div class="content-view-children">
        {foreach fetch_alias( 'children', hash( 'parent_node_id', $node.node_id,
                                                'offset', $view_parameters.offset,
                                                'sort_by', $node.sort_array,
                                                'class_filter_type', 'exclude',
                                                'class_filter_array', $classes,
                                                'limit', $page_limit ) ) as $child sequence array( 'col-even', 'col-odd' ) as $style}
            {node_view_gui style=$style view='line' content_node=$child}
        {/foreach}
    </div>
    

    {include name=navigator
             uri='design:navigator/google.tpl'
             page_uri=$node.url_alias
             item_count=$children_count
             view_parameters=$view_parameters
             item_limit=$page_limit}
    {/if}

{/if}