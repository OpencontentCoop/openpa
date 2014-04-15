{def $valid_node = $block.valid_nodes[0]}

<div class="block-type-singolo block-{$block.view}">

	{if $block.name}
		<h2 class="block-title">
			{$block.name}
		</h2>
	{/if}

<div class="border-box box-gray box-singolo">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">

	<div class="attribute-header">
		{if $valid_node.class_identifier|eq('link')}
        		<h2>
				<a href={$valid_node.data_map.location.content|ezurl()} target="_blank" title="Apri il link '{$valid_node.name|wash()}' in una pagina esterna (si lascerà il sito)">
					{$valid_node.name|wash()}
				</a>
			</h2>
		{else}
        		<h2><a href="{$valid_node.url_alias|ezurl(no)}">{$valid_node.name}</a></h2>
		{/if}
        </div>

	{if $valid_node.data_map.image.has_content}
    		<div class="right attribute-image">

			{if $valid_node.class_identifier|eq('link')}
 				<a href={$valid_node.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">
					{attribute_view_gui attribute=$valid_node.data_map.image image_class='lista_accordion'}
				</a>
			{else}
				<a href={$valid_node.url_alias|ezurl()}>
					{attribute_view_gui attribute=$valid_node.data_map.image image_class='lista_accordion'}
				</a>
			{/if}

		</div>

	{/if}

    {if $valid_node|has_abstract()}
        <p>{$valid_node|abstract()}</p>
    {/if}

</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

</div>