{* File - List embed view *}

{if $object.main_node.data_map.file.content.contentobject_attribute_id}
	{def $file = $object.main_node.data_map.file}

	<div class="content-body attribute-{$file.content.mime_type_part}">
		<a href={concat("content/download/", $file.contentobject_id, "/", $file.id, "/file/", $file.content.original_filename)|ezurl}>
			{$object.main_node.name}
		</a> 
		{$file.content.filesize|si(byte)}
	</div>

	{undef $file}

{/if}

