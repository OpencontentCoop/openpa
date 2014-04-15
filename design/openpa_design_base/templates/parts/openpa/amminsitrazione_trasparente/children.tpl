{foreach $nodes as $child }                            
    {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
    <div class="{$style} col col-notitle float-break">
        <div class="col-content"><div class="col-content-design">
            {node_view_gui view='line' show_image='no' content_node=$child.object.main_node}
        </div></div>
    </div>
{/foreach}

{include name=navigator
         uri='design:navigator/google.tpl'
         page_uri=$node.url_alias
         item_count=$nodes_count
         view_parameters=$view_parameters
         item_limit=openpaini( 'GestioneFigli', 'limite_paginazione', 25 )}