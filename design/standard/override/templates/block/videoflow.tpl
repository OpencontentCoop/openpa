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

<div class="widget {$block.view}">

        <div class="widget_title">
        {if $block.name|ne('')}
            <h3>{$block.name|wash()}</h3>
        {else}
            <h3><a title="{$flash_node.name|wash()}" href={$flash_node.url_alias|ezurl}>{$flash_node.name|shorten(73)|wash()}</a></h3>
        {/if}
        </div>

    <div class="widget_content">

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

    <div class="webtv-content">
        {if $image}
            {def $style = concat( 'display:block;width:', $width, ';height:', $height, ';background-image:url(', $image.url|ezurl(no), ')' ) }
        {else}
            {def $style = concat( 'display:block;width:', $width, ';height:', $height, ';' )}        
        {/if}
        {include name=video_player uri="design:content/mediaplayer/video_player.tpl" attribute=$attribute params=hash( 'style', $style, 'image', 'icons/logo-player.png' )}
    </div>
    
	<p class="link">
		<a title="Entra nella sezione specifica {$flash_node.parent.name}" href={$flash_node.parent.url_alias|ezurl()}>
			Entra in {$flash_node.parent.name}
		</a>
	</p>




    </div>
</div>

{undef}
