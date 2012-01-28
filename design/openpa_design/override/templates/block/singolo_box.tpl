{def $valid_node = $block.valid_nodes[0]}

<!-- BLOCK: START -->

<div class="block-type-singolo block-{$block.view}">

<div class="border-box box-gray box-singolo">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">

<!-- BOX CONTENT: START -->

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
    		<div class="right attribute-image">{attribute_view_gui attribute=$valid_node.data_map.image image_class='mainstory3'}</div>
	{/if}

	{if is_set($valid_node.data_map.abstract)}
		{if $valid_node.data_map.abstract.has_content}
                         <div class="attribute-short no-js-hide">
                              {attribute_view_gui attribute=$valid_node.data_map.abstract}
                         </div>
		{/if}
        {elseif is_set($valid_node.data_map.testo_news)}
		{if $valid_node.data_map.testo_news.has_content}
                        <div class="attribute-short no-js-hide">
                              {attribute_view_gui attribute=$valid_node.data_map.testo_news}
                        </div>
		{/if}
        {elseif is_set($valid_node.data_map.intro)}
		{if $valid_node.data_map.intro.has_content}
                        <div class="attribute-short no-js-hide">
        		{attribute_view_gui attribute=$valid_node.data_map.intro}
                        </div>
		{/if}
        {/if}

<!-- BOX CONTENT: END -->

</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

</div>

<!-- BLOCK: END -->
