{include uri="design:content/mediaplayer/audio_player.tpl" attribute=$attribute}

<p><a href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.id,"/file/",$attribute.content.original_filename)|ezurl} title="Scarica il file {$attribute.content.original_filename|wash( xhtml )}">
    Download "{$attribute.object.name|wash( xhtml )}"
    <br /><small>(File di tipo {$attribute.content.mime_type_part} di {$attribute.content.filesize|si( byte )})</small>
</a></p>