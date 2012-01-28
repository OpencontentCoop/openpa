{def $icon_size='small'
     $icon_title=$attribute.content.mime_type
     $icon='yes'}

<div class="content-view-embed">	 
<div class="class-file-embed">
<div class="content-body">

{if $attribute.has_content}
	{if $attribute.content}
	{switch match=$icon}
		{case match='no'}
			<a href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.id,"/file/",$attribute.content.original_filename)|ezurl}>{$attribute.content.original_filename|wash( xhtml )} ({$attribute.content.filesize|si( byte )})</a>
		{/case}
		{case}
			<a class="file-icon float-break" href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.id,"/file/",$attribute.content.original_filename)|ezurl}>
                <span class="icon">{$attribute.content.mime_type|mimetype_icon( $icon_size, $icon_title )}</span>
                <span class="text">{$attribute.content.original_filename|explode('_')|implode(' ')|wash( xhtml )} ({$attribute.content.filesize|si( byte )})</span>
            </a>
		{/case}
	{/switch}
	{else}
		<div class="message-error"><h2>{'The file could not be found.'|i18n( 'design/ezwebin/view/ezbinaryfile' )}</h2></div>
	{/if}
{/if}

</div>
</div>
</div>
