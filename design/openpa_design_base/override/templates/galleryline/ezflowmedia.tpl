{def $width=100
	 $height = 100}

<div class="content-view-galleryline">
    <div class="class-image">

    <div class="attribute-image"{if is_set($#image_style)} style="{$#image_style}"{/if}>
        {switch match=$node.data_map.file.content.streaming}
    		{case match=file}
    	<a
    		 href={concat("content/download/",$node.data_map.file.contentobject_id,"/",$node.data_map.file.content.contentobject_attribute_id,"/",$node.data_map.file.content.original_filename)|ezurl} 
			 style="display:block;{if is_set($#image_style)}{$#image_style}{/if}"  
			 id="media-{$node.data_map.file.contentobject_id}">
		</a>
<script>
flowplayer(
	"media-{$node.data_map.file.contentobject_id}",
	{ldelim} src: '{'images/flowplayer-3.0.7.swf'|ezdesign(no)}', wmode: 'transparent'{rdelim},
	{ldelim} 
		clip: 
			{ldelim} 
			autoPlay:false,
			autoBuffering: true  
			{rdelim}, 
		plugins: {ldelim} controls: {ldelim}all: false,fullscreen: true, backgroundGradient: 'low'{rdelim} {rdelim}
	{rdelim});
</script>
			{/case}
		{case match=http}
    	<a
    		 href={$node.data_map.file.content.url}
			 style="display:block;{if is_set($#image_style)}{$#image_style}{/if}"  
			 id="media-{$node.data_map.file.contentobject_id}">
		</a>
		<script>
			flowplayer("media-{$node.data_map.file.contentobject_id}", "{'images/flowplayer-3.0.7.swf'|ezdesign(no)}", {ldelim} clip: {ldelim} autoPlay:false, autoBuffering: true  {rdelim}, plugins: {ldelim} controls: {ldelim}all: false,fullscreen: true{rdelim} {rdelim}{rdelim});
		</script>
			{/case}
		{case match=rtmp}
    	<a
    		 class="player" 
			 style="display:block;{if is_set($#image_style)}{$#image_style}{/if}"
			 id="media-{$node.data_map.file.contentobject_id}">
		</a>
		<script>
			flowplayer("media-{$node.data_map.file.contentobject_id}", "{'images/flowplayer-3.0.7.swf'|ezdesign(no)}", 
			{ldelim} 
			clip: {ldelim}
				url: '{$node.data_map.file.content.movie}',
				provider: 'rtmp',
				autoBuffering: true 
					{rdelim},
			plugins:
					{ldelim}
						controls: {ldelim}all: false,fullscreen: true{rdelim},
						rtmp: {ldelim}
							url: '{'images/flowplayer.rtmp-3.0.2.swf'|ezdesign(no)}',
							netConnectionUrl: '{$node.data_map.file.content.url}'
							{rdelim}
					{rdelim}
			{rdelim});
		</script>
			{/case}
		{/switch}
    </div>

    <div class="attribute-name"{if is_set($#image_style)} style="{$#image_style|explode(';').0}"{/if}>
        <p><a href={$node.url_alias|ezurl()} title="{$node.name|wash()}">{$node.name|shorten(14)|wash}</a></p>
    </div>

    </div>
</div>