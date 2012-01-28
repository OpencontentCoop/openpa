{def $valid_nodes = $block.valid_nodes
	 $valid_nodes_count = $valid_nodes|count()
	 $children=array()}

<div class="block-type-lista block-{$block.view}">
{if $block.name}
	<h2 class="hide">{$block.name}</h2>	 
{/if}
{if $valid_nodes_count|eq(1)}

	<div class="border-box box-trans-gray box-lista">
	<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
	<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
	<div class="border-content">

		<h2>{$valid_nodes[0].name|wash()}</h2>

		{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[0].node_id, 'sort_by', $valid_nodes[0].sort_array, 'limit', 6 ) )}
		{if $children|count()}
			<ul>							 
				{foreach $children as $child}
				<li><a href={$child.url_alias|ezurl}>{$child.name|wash()}</a>
				    <div class="attribute-date">{$child.object.published|datetime(custom, '%j %F %Y')}</div></li>
				{/foreach}
			</ul>
		{else}
			<p>Non ci sono {$valid_nodes[0].name|wash()} disponibili.</p>
		{/if}
				
	</div>
	</div></div>
	<div class="border-bottom-content">
		<a class="arrows" href={$valid_nodes[0].url_alias|ezurl()} title="Vai a {$valid_nodes[0].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
	</div>	
	</div>
	<div class="border-bl"><div class="border-br"><div class="border-bc">
	</div></div></div>
	</div>

{elseif $valid_nodes_count|eq(2)}

	<div class="columns-two">
	
	<div class="col-1">
	<div class="col-content">

		<div class="border-box box-trans-gray box-lista">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<h2>{$valid_nodes[0].name|wash()}</h2>

			{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[0].node_id,'sort_by', $valid_nodes[0].sort_array, 'limit', 6 ) )}
			{if $children|count()}
			
				<ul>							 
					{foreach $children as $child}
					<li><a href={$child.url_alias|ezurl}>{$child.name|wash()}</a>
				    <div class="attribute-date">{$child.object.published|datetime(custom, '%j %F %Y')}</div></li>
					{/foreach}
				</ul>
			{else}
				<p>Non ci sono {$valid_nodes[0].name|wash()} disponibili.</p>
			{/if}
		
		</div>
		</div></div>
		<div class="border-bottom-content">
			<a class="arrows" href={$valid_nodes[0].url_alias|ezurl()} title="Vai a {$valid_nodes[0].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
		</div></div>

		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>

	</div>
	</div>
	
	<div class="col-2">
	<div class="col-content">

		<div class="border-box box-trans-gray-2 box-lista-2">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<h2>{$valid_nodes[1].name|wash()}</h2>

			{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[1].node_id,'sort_by', $valid_nodes[0].sort_array, 'limit', 3 ) )}
			{if $children|count()}
				<ul>							 
					{foreach $children as $child}
					<li><a href={$child.url_alias|ezurl}>{$child.name|wash()}</a>
				    <div class="attribute-date">{$child.object.published|datetime(custom, '%j %F %Y')}</div></li>
					{/foreach}
				</ul>
			{else}
				<p>Non ci sono {$valid_nodes[1].name|wash()} disponibili.</p>
			{/if}
		
		</div>
		</div></div>
		<div class="border-bottom-content">	
			<a class="arrows" href={$valid_nodes[1].url_alias|ezurl()} title="Vai a {$valid_nodes[1].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
		</div></div>

		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>

	</div>
	</div>
	</div>

{elseif $valid_nodes_count|eq(3)}

	<div class="columns-three">
	<div class="col-1-2">
	<div class="col-1">
	<div class="col-content">

		<div class="border-box box-trans-gray box-lista">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<h2>{$valid_nodes[0].name|wash()}</h2>
			{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[0].node_id,'sort_by', $valid_nodes[0].sort_array, 'limit', 4 ) )}
			{if $children|count()}
				<ul>							 
					{foreach $children as $child}
					<li><a href={$child.url_alias|ezurl} title="{$child.name|wash()}">{$child.name|shorten(60)|wash()}</a></li>
					{/foreach}
				</ul>
			{else}
				<p>Non ci sono {$valid_nodes[0].name|wash()} disponibili.</p>
			{/if}
		
		</div>
		</div></div>
		<div class="border-bottom-content">	
			<a class="arrows" href={$valid_nodes[0].url_alias|ezurl()} title="Vai a {$valid_nodes[0].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
		</div></div>

		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>

	</div>
	</div>
	<div class="col-2">
	<div class="col-content">

		<div class="border-box box-trans-gray-2 box-lista-2">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<h2>{$valid_nodes[1].name|wash()}</h2>

			{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[1].node_id,'sort_by', $valid_nodes[1].sort_array, 'limit', 3 ) )}
			{if $children|count()}
				<ul>							 
					{foreach $children as $child}
					<li><a href={$child.url_alias|ezurl} title="{$child.name|wash()}">{$child.name|shorten(60)|wash()}</a></li>
					{/foreach}
				</ul>
			{else}
				<p>Non ci sono {$valid_nodes[1].name|wash()} disponibili.</p>
			{/if}

		</div>
		</div></div>
		<div class="border-bottom-content">	
			<a class="arrows" href={$valid_nodes[1].url_alias|ezurl()} title="Vai a {$valid_nodes[1].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
		</div></div>

		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>

	</div>
	</div>
	</div>
	<div class="col-3">
	<div class="col-content">

		<div class="border-box box-trans-gray-3 box-lista-3">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml float-break"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<h2>{$valid_nodes[2].name|wash()}</h2>

			{set $children=fetch( 'content', 'list',  hash( 'parent_node_id', $valid_nodes[2].node_id, 'sort_by', $valid_nodes[2].sort_array, 'limit', 3 ) )}
			{if $children|count()}
			<ul>							 
				{foreach $children as $child}
				<li><a href={$child.url_alias|ezurl} title="{$child.name|wash()}">{$child.name|shorten(60)|wash()}</a></li>
				{/foreach}
			</ul>
			{else}
				<p>Non ci sono {$valid_nodes[2].name|wash()} disponibili.</p>
			{/if}

		</div>
		</div></div>
		<div class="border-bottom-content">	
			<a class="arrows" href={$valid_nodes[2].url_alias|ezurl()} title="Vai a {$valid_nodes[2].name|wash()}"><span class="arrows-blue-r">Leggi tutti</span></a>
		</div></div>		

		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>

	</div>
	</div>
	</div>

{/if}

</div>
