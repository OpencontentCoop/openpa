<div class="content-view-embed">
	{if $node.class_identifier|eq('tipo_struttura')}
		<img class="image-class_identifier" src={concat('icons/crystal/64x64/mimetypes/',$node.class_identifier,'.png')|ezimage()} alt="{$node.name}" title="{$node.name}" />
	{else}
		{if $node.class_identifier|eq('link')}
			<a title="{$node.name|wash()}" target="_blank" href={$node.data_map.location.content|ezurl()}>{$node.name|wash()}</a>
		{else}
			{if is_set( $node.url_alias )}
				<a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a>
			{else}
				{$node.name|wash()}
			{/if}
		{/if}
	{/if}
</div>
