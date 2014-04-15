{def $valid_node = $block.valid_nodes[0]
     $esiste_link = 0}

{if is_set($valid_node.data_map.location)}
	{if $valid_node.data_map.location.has_content}
		{def $collegamento = $valid_node.data_map.location.content}
		{set $esiste_link = 1}
	{else}
		{def $collegamento = $valid_node.url_alias}
	{/if}
{elseif is_set($valid_node.data_map.location_applicativo)}
	{if $valid_node.data_map.location_applicativo.has_content}
		{def $collegamento = $valid_node.data_map.location_applicativo.content}
		{set $esiste_link = 1}
	{else}
		{def $collegamento = $valid_node.url_alias}
	{/if}
{else}
	{def $collegamento = $valid_node.url_alias}
{/if}

<div class="block-type-singolo block-{$block.view}">

{if $block.name}
	<h2 class="block-title"> 
		{if $esiste_link|eq(1)}
			<a href={$collegamento|ezurl()} title="Collegati a '{$valid_node.name|wash()}' in una nuova pagina">{$block.name}</a></h2>
		{else}
			<a href={$collegamento|ezurl()} title="Vai a {$valid_node.name|wash()}">{$block.name}</a></h2>
		{/if}
{else}
	<h2 class="block-title"><a href="{$collegamento|ezurl(no)}">{$valid_node.name}</a></h2>
{/if}

<div class="square-box-gray">
<div class="box-content float-break">

    <div class="attribute-image">
		<a href={$collegamento|ezurl()} title="Collegati a '{$valid_node.name|wash()}'">
			{attribute_view_gui attribute=$valid_node.data_map.image image_class='singolo_banner'}
		</a>
    </div>

    <div class="attribute-short">
        {if is_set($valid_node.data_map.image_map)}
			{attribute_view_gui attribute=$valid_node.data_map.image_map}
		{else}
			{attribute_view_gui attribute=$valid_node.data_map.abstract}
		{/if}
    </div>	 

</div>
</div>

</div>