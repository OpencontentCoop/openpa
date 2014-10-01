{def $valid_nodes = $block.valid_nodes}
{if $valid_nodes|count()|gt(0)}

<div class="widget_tabs {$block.view}">


	{if $block.name}<h2 class="block-title">{$block.name}</h2>{else}<h2 class="hide">Altre informazioni</h2>{/if}

    <ul class="nav nav-tabs">
        {foreach $valid_nodes as $index => $child}
        <li class="{if $index|eq(0)}active{/if}">
            <a href="#{$block.id}-{$child.name|slugize()}" data-toggle="tab" title="{$child.name|wash()}">{$child.name|wash()}</a>
        </li>
        {/foreach}
    </ul>

    <div class="tab-content">
        {foreach $valid_nodes as $index => $child}
        <div class="tab-pane{if $index|eq(0)} active{/if}" id="{$block.id}-{$child.name|slugize()}">
            {node_view_gui content_node=$child view=panel}
        </div>
        {/foreach}
    </div>

</div>

{/if}
