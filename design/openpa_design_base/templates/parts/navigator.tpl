{def $previous_node = fetch( 'content', 'list', hash( 'parent_node_id', $node.parent_node_id,
                                                       'limit', '1',
                                                       'attribute_filter', array( 'and', array( $sort_column, $sort_order|choose( '>', '<' ), $sort_column_value ) ),
                                                       'sort_by', array( array( $sort_column, $sort_order|not ), array( 'node_id', $sort_order|not ) ) ) )
     $next_node = fetch( 'content', 'list', hash( 'parent_node_id', $node.parent_node_id,
                                                   'limit', '1',
                                                   'attribute_filter', array( 'and', array( $sort_column, $sort_order|choose( '<', '>' ), $sort_column_value ) ),
                                                   'sort_by', array( array( $sort_column, $sort_order ), array( 'node_id', $sort_order ) ) ) ) }

<div class="border-box box-gray block-navigation">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
	<div class="content-navigator float-break">
	
		{if $previous_node}
			<div class="content-navigator-previous">
				<div class="content-navigator-arrow"><a href={$previous_node[0].url_alias|ezurl} title="{$previous_node[0].name|wash}">Precedente</a></div>
			</div>
		{else}
			<div class="content-navigator-previous-disabled">
				<div class="content-navigator-arrow"><span>Precedente</span></div>					
			</div>
		{/if}

		<div class="link-to-parent content-navigator-forum-link"><a href={$node.parent.url_alias|ezurl} title="{$node.parent.name|wash}">&#8593; {$node.parent.name|wash}</a></div>

		{if $next_node}
			<div class="content-navigator-next">
				<div class="content-navigator-arrow"><a href={$next_node[0].url_alias|ezurl} title="{$next_node[0].name|wash}">Successivo</a></div>
			</div>
		{else}
			<div class="content-navigator-next-disabled">
				<div class="content-navigator-arrow"> <span>Successivo</span></div>
			</div>
		{/if}
	
	</div>
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
</div>
