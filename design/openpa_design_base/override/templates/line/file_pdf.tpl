{* File pdf - Line view *}

<div class="content-view-line">
    <div class="class-file_pdf">

	{*if $node.data_map.file.content.original_filename|ne( concat($node.name, '.', $node.data_map.file.content.mime_type_part) )*}
		<h2>
            {include name=edit node=$node uri='design:parts/openpa/edit_buttons.tpl'}    
            {$node.name|wash()}
        </h2>
	{*/if*}
	
	{if $node.data_map.file.has_content}
		<div class="attribute-file">
		{attribute_view_gui attribute=$node.data_map.file}
		</div>
	{/if}

    </div>
</div>
