{def $object = $attribute.object
     $sottotitoli = false()}
{if is_set( $object.data_map.sottotitoli )}
    {if $object.data_map.sottotitoli.has_content}
        {set $sottotitoli = concat("content/download/",$object.data_map.sottotitoli.contentobject_id,"/",$object.data_map.sottotitoli.content.contentobject_attribute_id,"/",$object.data_map.sottotitoli.content.original_filename)|ezurl}
    {/if}
{/if}

{def $width=$attribute.content.width
	 $height=$attribute.content.height
	 $image = $attribute.object.data_map.image
	 $name = $attribute.object.data_map.name}
{if $width|eq(0)}
	{set $width='100%'}
{else}
	{set $width=concat($width,'px')}
{/if}
{if $height|eq(0)}
	{set $height='350'}
{/if}



{if is_set($attribute.content.streaming)}
{ezscript_require(array( 'ezjsc::jquery', 'flowplayer-3.2.6.min.js' ) )}
{switch match=$attribute.content.streaming}

{case match=file}
<a
	 class="player" 
	 href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename)|ezurl} 
	 style="display:block;width:{$width};height:{$height}px"  
	 id="media-{$attribute.contentobject_id}">
		<img class='default' src={'retecivica/logo-player.jpg'|ezimage()} alt="{$name}" />
</a>
<script>
{if $sottotitoli}
    flowplayer("media-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}",
    {ldelim}
        clip:
            {ldelim}
            scaling:'fit',
            autoPlay:true,
            captionUrl:{$sottotitoli},
            autoBuffering: true
            {rdelim},
        plugins:
            {ldelim}
                captions:
                {ldelim}
                    url: "{'images/flowplayer.captions-3.2.3.swf'|ezdesign(no)}",
                    captionTarget: 'content'
                {rdelim},
                content:
                {ldelim}
                    url:"{'images/flowplayer.content-3.2.0.swf'|ezdesign(no)}",
                    bottom: 10,
                    height:40,
                    backgroundColor: 'transparent',
                    backgroundGradient: 'none',
                    border: 0,
                    textDecoration: 'outline',
                    style: {ldelim} 
                        body: {ldelim} 
                            fontSize: 14, 
                            fontFamily: 'Arial',
                            textAlign: 'center',
                            color: '#ffffff'
                        {rdelim}
                    {rdelim}
                {rdelim}
            {rdelim}
    {rdelim});
{else}
	flowplayer("media-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}", {ldelim} clip: {ldelim} scaling:'fit', autoPlay:true, autoBuffering: true  {rdelim} {rdelim});
{/if}
</script>
{/case}

{case match=http}

<a
	 class="player" 
	 href={$attribute.content.url}
	 style="display:block;width:{$width};height:{$height}px"  
	 id="media-{$attribute.contentobject_id}">
		<img class='default' src={'retecivica/logo-player.jpg'|ezimage()} alt="{$name}" />
</a>
<script>
{if $sottotitoli}
    flowplayer("media-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}",
    {ldelim}
        clip:
            {ldelim}
            scaling:'fit',
            autoPlay:true,
            captionUrl:{$sottotitoli},
            autoBuffering: true
            {rdelim},
        plugins:
            {ldelim}
                captions:
                {ldelim}
                    url: "{'images/flowplayer.captions-3.2.3.swf'|ezdesign(no)}",
                    captionTarget: 'content'
                {rdelim},
                content:
                {ldelim}
                    url:"{'images/flowplayer.content-3.2.0.swf'|ezdesign(no)}",
                    bottom: 10,
                    height:40,
                    backgroundColor: 'transparent',
                    backgroundGradient: 'none',
                    border: 0,
                    textDecoration: 'outline',
                    style: {ldelim} 
                        body: {ldelim} 
                            fontSize: 14, 
                            fontFamily: 'Arial',
                            textAlign: 'center',
                            color: '#ffffff'
                        {rdelim}
                    {rdelim}
                {rdelim}
            {rdelim}
    {rdelim});
{else}
	flowplayer("media-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}", {ldelim} clip: {ldelim} scaling:'fit', autoPlay:true, autoBuffering: true  {rdelim} {rdelim});
{/if}
</script>
{/case}

{case match=rtmp}
<a
	 class="player" 
	 style="display:block;width:{$width};height:{$height}px"  
	 id="media-{$attribute.contentobject_id}">
		<img class='default' src={'retecivica/logo-player.jpg'|ezimage()} alt="{$name}" />
</a>
<script>
	flowplayer("media-{$attribute.contentobject_id}", "{'images/flowplayer-3.0.7.swf'|ezdesign(no)}", 
	{ldelim} 
	clip: {ldelim}
		url: '{$attribute.content.movie}',
		provider: 'rtmp',
		scaling:'fit', 
		autoPlay:true,
		autoBuffering: true 
			{rdelim},
	plugins:
			{ldelim}
				rtmp: {ldelim}
					url: '{'images/flowplayer.rtmp-3.2.3.swf'|ezdesign(no)}',
					netConnectionUrl: '{$attribute.content.url}'
					{rdelim}
			{rdelim}
	{rdelim});
</script>
	{/case}
{/switch}

{/if}
