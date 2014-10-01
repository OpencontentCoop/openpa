{set_defaults( hash('show_title', true()) )}
{def $valid_node = $block.valid_nodes[0]}
{def $openpa_node = object_handler($valid_node)}

{if and( $show_title, $block.name|ne('') )}
<div class="widget {$block.view}">
  <div class="widget_title">
    <h3><a href={$valid_node.url_alias|ezurl()}>{$block.name|wash()}</a></h3>
  </div>
{/if}

  <div class="{if and( $show_title, $block.name|ne('') )}widget_content {/if}">
    {include uri=$openpa_node.content_main.template openpa=$openpa_node node=$valid_node}
    {include uri=$openpa_node.content_detail.template openpa=$openpa_node node=$valid_node}
  </div>

{if and( $show_title, $block.name|ne('') )}
</div>
{/if}