{def $flash_node = $block.valid_nodes[0]
     $siteurl = concat( "http://", ezini( 'SiteSettings', 'SiteURL' ) )  
     $attribute = $flash_node.data_map.ezflowmedia
     $video = concat("content/download/", $attribute.contentobject_id, "/", $attribute.content.contentobject_attribute_id)|ezurl(no)
	 $width='100%'
	 $height='180px'
     $sottotitoli = false()
}
{if is_set( $flash_node.data_map.sottotitoli )}
    {if $flash_node.data_map.sottotitoli.has_content}
        {set $sottotitoli = concat("content/download/",$flash_node.data_map.sottotitoli.contentobject_id,"/",$flash_node.data_map.sottotitoli.content.contentobject_attribute_id,"/",$flash_node.data_map.sottotitoli.content.original_filename)|ezurl}
    {/if}
{/if}

<div class="block-type-video video-flow">
{if $block.name}
<div class="attribute-header">
    <h2 class="block-title">
	{*<a href={$flash_node.parent.url_alias|ezurl()}>*}
		{$block.name|wash()}
	{*</a>*}
    </h2>
</div>
{else}
	<h2 class="hide">Multimedia</h2>
{/if}

{*
{if $flash_node.parent.data_map.image.has_content}
	{def $image_content = $flash_node.parent.data_map.image.content}
	{if $image_content.is_valid}
		{def $image = $image_content['ezflowmediablock']}
	{elseif $flash_node.data_map.image.has_content}			
		{def $image_content = $flash_node.data_map.image.content}
		{if $image_content.is_valid}
			{def $image = $image_content['ezflowmediablock']}
		{/if}
	{/if}
{elseif $flash_node.data_map.image.has_content}			
	{def $image_content = $flash_node.data_map.image.content}
	{if $image_content.is_valid}
		{def $image = $image_content['ezflowmediablock']}
	{/if}
{/if}
*}

{if $flash_node.data_map.cover.has_content}	
	{def $image_content = $flash_node.data_map.cover.content}
	{if $image_content.is_valid}
		{def $image = $image_content['ezflowmediablock']}	
	{/if}

{elseif $flash_node.data_map.image.has_content}	
	{def $image_content = $flash_node.data_map.image.content}
	{if $image_content.is_valid}
		{def $image = $image_content['ezflowmediablock']}	
	{/if}

{elseif $flash_node.parent.data_map.image.has_content}		
	{def $image_content = $flash_node.parent.data_map.image.content}
	{if $image_content.is_valid}
		{def $image = $image_content['ezflowmediablock']}
	{elseif $flash_node.data_map.image.has_content}			
		{def $image_content = $flash_node.data_map.image.content}
		{if $image_content.is_valid}
			{def $image = $image_content['ezflowmediablock']}
		{/if}
	{/if}
{/if}
	
<div class="square-box-gray video-description float-break"> 
	<div class="attribute-header">
	<h3>
        <a title="{$flash_node.data_map.abstract.content.output.output_text|explode("<br />")|implode(" ")|strip_tags()|trim()}" href={$flash_node.url_alias|ezurl}>
			{$flash_node.name|shorten(73)|wash()}
		</a>
  	</h3> 
	</div>

    <div class="webtv-content">


{if is_set($attribute.content.streaming)}

{ezscript_require(array( 'ezjsc::jquery', 'flowplayer-3.2.6.min.js' ) )}
{switch match=$attribute.content.streaming}
{case match=file}
        <a	class="player no-js-hide"
			href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename)|ezurl}
			style="display:block;width:{$width};height:{$height};{if $image}background-image:url({$image.url|ezurl(no)}){/if}"
			title="Guarda il video: {$flash_node.name}"
			id="id-{$attribute.contentobject_id}">
				<img class='default' src={'retecivica/logo-player.png'|ezimage()} alt="{$flash_node.name}" />
		</a>
		<script type="text/javascript">
        
            {if $sottotitoli}
                flowplayer("id-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}",
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
                                        fontSize: 12, 
                                        fontFamily: 'Arial',
                                        textAlign: 'center',
                                        color: '#ffffff'
                                    {rdelim}
                                {rdelim}
                            {rdelim}
                        {rdelim}
                {rdelim});
            {else}
				flowplayer("id-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}", {ldelim} clip: {ldelim} autoPlay:true, scaling:'fit', autoBuffering: true  {rdelim} {rdelim});
            {/if}
		</script>		
{/case}
{case match=http}
        <a	class="player no-js-hide"
			href="{$attribute.content.url}"
			style="display:block;width:{$width};height:{$height};{if $image}background-image:url({$image.url|ezurl(no)}){/if}"
			title="Guarda il video: {$flash_node.name}"
			id="id-{$attribute.contentobject_id}">
				<img class='default' src={'retecivica/logo-player.png'|ezimage()} alt="{$flash_node.name}" />
		</a>
		<script type="text/javascript">
			{if $sottotitoli}
                flowplayer("id-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}",
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
                                        fontSize: 12, 
                                        fontFamily: 'Arial',
                                        textAlign: 'center',
                                        color: '#ffffff'
                                    {rdelim}
                                {rdelim}
                            {rdelim}
                        {rdelim}
                {rdelim});
            {else}
                flowplayer("id-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}", {ldelim} clip: {ldelim} autoPlay:true, scaling:'fit', autoBuffering: true  {rdelim} {rdelim});
            {/if}
		</script>
{/case}
{case match=rtmp}
        <a	class="player no-js-hide"
			style="display:block;width:{$width};height:{$height};{if $image}background-image:url({$image.url|ezurl(no)}){/if}"
			title="Guarda il video: {$flash_node.name}"
			id="id-{$attribute.contentobject_id}">
				<img class='default' src={'retecivica/logo-player.png'|ezimage()} alt="{$flash_node.name}" />		
		</a>
		<script type="text/javascript">
				flowplayer("id-{$attribute.contentobject_id}", "{'images/flowplayer-3.2.7.swf'|ezdesign(no)}",
				{ldelim}
				clip: {ldelim}
						url: '{$attribute.content.movie}',
						provider: 'rtmp',
						autoPlay:false,
						scaling:'fit', 
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

</div>
	{* 
	<div class="attribute-intro">
		{attribute_view_gui attribute=$flash_node.data_map.abstract}
	</div>
	*}
	<div class="bottom-content">
		<a class="arrows" title="Entra nella sezione specifica {$flash_node.parent.name}" href={$flash_node.parent.url_alias|ezurl()}>
			<span class="arrows-blue-r">Entra in {$flash_node.parent.name}</span>
		</a>
	</div>
</div>


{else}

<div class="square-box-gray video-description float-break">
<h3>Errore: avviso per l'editor...</h3>
<div class="attribute-intro">
	L'oggetto richiesto da questo blocco deve essere di tipo flowmedia (ezflowmedia).<br />
</div>

<a class="arrows" title="Entra" href={$flash_node.parent.url_alias|ezurl()}><span class="arrows-blue-r">Entra in {$flash_node.parent.name}</span></a>
</div>

{/if}

</div>

{undef}
