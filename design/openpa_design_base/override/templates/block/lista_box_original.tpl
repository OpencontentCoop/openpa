{def $customs=$block.custom_attributes $errors=false() $sort_array=array() $classes=array()}

{if $customs.limite|gt(0)}
    {def $limit=$customs.limite}
{else}
    {def $limit=10}
{/if}

{if $customs.livello_profondita|eq('')}
    {def $depth=10}
{else}
    {def $depth=$customs.livello_profondita}
{/if}

{def $nodo=fetch(content,node,hash(node_id,$customs.node_id))}
{switch match=$customs.ordinamento}
{case match=''}
    {set $sort_array=$nodo.sort_array}
{/case}
{case match='priorita'}
        {set $sort_array=array('priority', true())}
{/case}
{case match='pubblicato'}
        {set $sort_array=array('published', false())}
{/case}
{case match='modificato'}
        {set $sort_array=array('modified', false())}
{/case}
{case match='nome'}
        {set $sort_array=array('name', true())}
{/case}
{/switch}

{if $customs.classi|eq('')}
    {set $classes = openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow' )}
    {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                        'class_filter_type', 'exclude', 'class_filter_array', $classes,
                        'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
{elseif $custom.escludi_classi|eq('')}
    {set $classes=$customs.classi|explode(',')}
    {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                    'class_filter_type', 'include', 'class_filter_array', $classes,
                                                    'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
{else}
    {set $classes = $custom.escludi_classi|explode(',')}
    {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                    'class_filter_type', 'exclude', 'class_filter_array', $classes,
                                                    'depth', $depth, 'limit', $limit, 'sort_by', $sort_array) )}
{/if}

{if count($children)|gt(0)}

	<div class="block-type-lista block-{$block.view}">

		{foreach $children as $node}
		<div class="{$block.view}-item">

			<h2 class="block-title">{$node.name|wash()}</h2>

			{def $subchildren=fetch( 'content', 'list',  hash( 'parent_node_id', $node.node_id, 'limit', 30 ) )}	
			{foreach $subchildren as $child}
				<div class="{$block.view}-child-item">
					{node_view_gui content_node=$child view=line}
				</div>
			{/foreach}
		</div>	
		{/foreach}

	</div>

{else}

    {def $valid_nodes = $block.valid_nodes}

	<div class="block-type-lista block-{$block.view}">

		{foreach $valid_nodes as $node}
		<div class="{$block.view}-item">

			<h2 class="block-title">{$node.name|wash()}</h2>

			{def $children=fetch( 'content', 'list',  hash( 'parent_node_id', $node.node_id, 'limit', 30 ) )}	
			{foreach $children as $child}
				<div class="{$block.view}-child-item">
				{node_view_gui content_node=$child view=line}
				</div>
			{/foreach}
		</div>	
		{/foreach}

	</div>

{/if}
