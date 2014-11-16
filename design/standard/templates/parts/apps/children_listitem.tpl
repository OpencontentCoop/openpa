{if $root.children_count|gt(0)}
<ol class="dd-list">
  {foreach $root.children as $child}
	<li class="dd-item">{node_view_gui content_node=$child view=comuneintasca_listitem}</li>
  {/foreach}
</ol>
{/if}