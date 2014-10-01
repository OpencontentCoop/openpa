{def $openpa= object_handler($block)}
{set_defaults( hash('show_title', true(), 'items_per_row', 4) )}

<div class="{$block.view}">

    {if and( $show_title, $block.name|ne('') )}
      <h3 class="widget_title">{$block.name|wash()}</h3>
    {/if}

    <div class="panels-container">
        {include uri='design:atoms/panels.tpl'
                 items_per_row=$items_per_row
                 items=$openpa.content
                root_node=$openpa.root_node}
    </div>

</div>

{unset_defaults( array('show_title', 'items_per_row') )}