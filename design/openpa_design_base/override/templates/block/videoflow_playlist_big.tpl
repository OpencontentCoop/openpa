{*  $block.valid_nodes[0] = genitore *}

{def $parent = $block.valid_nodes[0]
     $page_limit = 10
     $valid_nodes = fetch( 'content', 'list', hash( 'parent_node_id', $parent.node_id,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array( 'ezflowmedia' ),
                                                    'limit', $page_limit,
                                                    'offset', $view_parameters.offset
                                                    ) )}

{ezscript_require(array( 'ezjsc::jquery', ocmp('flowplayer','js'), ocmp('controls','js'), ocmp('playlist','js') ) )}

{def $flash_node=0
	 $attribute=0
     $siteurl = concat( "http://", ezini( 'SiteSettings', 'SiteURL' ), '/content/download' )  
	 $url = false()
     $image_content = false()
     $image = false
     $width='100%'
	 $height='400px'
     $sottotitoli = false()
     $image_class = 'small'
}


<script type="text/javascript">
$(function(){ldelim}
    $f("player-{$block.id}", {ocmp('flowplayer','flash')}, {ldelim} 
		clip:{ldelim}
			scaling: 'fit', 
			autoPlay: true,
			autoBuffering: true,
			baseUrl: '{$siteurl}'
        {rdelim},  

        playlist:[ 
{foreach $valid_nodes as $valid_node}
    {set $flash_node = $valid_node 
         $attribute = $flash_node.data_map.ezflowmedia
         $sottotitoli = false()
         $image_content = false()
         $image = 'icons/logo-player.png'|ezimage(no)
         $url = false()}
    
    {if $flash_node.data_map.cover.has_content}	
        {set $image_content = $flash_node.data_map.cover.content}
        {if $image_content.is_valid}
            {set $image = $image_content[$image_class].url|ezurl(no)}	
        {/if}
    
    {elseif $flash_node.data_map.image.has_content}	
        {set $image_content = $flash_node.data_map.image.content}
        {if $image_content.is_valid}
            {set $image = $image_content[$image_class].url|ezurl(no)}	
        {/if}
    
    {elseif $flash_node.parent.data_map.image.has_content}		
        {set $image_content = $flash_node.parent.data_map.image.content}
        {if $image_content.is_valid}
            {set $image = $image_content[$image_class].url|ezurl(no)}
        {elseif $flash_node.data_map.image.has_content}			
            {set $image_content = $flash_node.data_map.image.content}
            {if $image_content.is_valid}
                {set $image = $image_content[$image_class].url|ezurl(no)}
            {/if}
        {/if}
    {/if}
    
    {if $image|eq('')}
    {set $image = 'icons/logo-player.png'|ezimage(no)}
    {/if}

    {if is_set( $flash_node.data_map.sottotitoli )}
        {if $flash_node.data_map.sottotitoli.has_content}
            {set $sottotitoli = concat("content/download/",$flash_node.data_map.sottotitoli.contentobject_id,"/",$flash_node.data_map.sottotitoli.content.contentobject_attribute_id,"/captions")}
        {/if}
    {/if}
    
    {switch match=$attribute.content.streaming}
        {case match=file}			
            {set $url = concat($attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/video")}
        {/case}
        {case match=http}
            {set $url = $attribute.content.url}
        {/case}
        {case match=rtmp}
            {set $url = $attribute.content.movie}
        {/case}
    {/switch}
    
    {ldelim}			
            url:        "{$url}",
            captionUrl: "{$sottotitoli}",
            img:        "{$image}",
            title:      "{$flash_node.name|wash()}"
    {rdelim}
    {delimiter},{/delimiter}
{/foreach}				      
        ], 		
        plugins:{ldelim} 
            controls:{ldelim} playlist: true {rdelim},

{foreach $valid_nodes as $valid_node}
    {set $flash_node = $valid_node 
         $attribute = $flash_node.data_map.ezflowmedia}
    {switch match=$attribute.content.streaming}
        {case match=rtmp}
            rtmp:{ldelim}url:'{'images/flowplayer.rtmp-3.0.2.swf'|ezdesign(no)}', netConnectionUrl:'{$attribute.content.url}'{rdelim},
        {/case}
        {case}
        {/case}
    {/switch}
{/foreach}

            captions:{ldelim}
                url: {ocmp('captions','flash')},
                captionTarget: 'content'
            {rdelim},
            content:{ldelim}
                url:{ocmp('content','flash')},
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
	
	$f("player-{$block.id}").playlist("ul.clips-{$block.id}:first", {ldelim} playOnClick:true,loop:true {rdelim} ); 
{rdelim});
</script>

<div class="block-type-video video-flow video-flow-playlist">
    
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

    <div class="video-description float-break no-js-hide"> 
        <a class="player" id="player-{$block.id}" style="display:block;width:{$width};height:{$height};"> 	
            <img style="margin-top:{$height|div(2.2)}px" class='default' src={'icons/logo-player.png'|ezimage()} />
        </a>
        <ul class="clips clips-big clips-{$block.id}"> 
        {literal} 
            <li>
                <a href="${url}" title="${title}"> 
                <img src="${img}" alt="${title}" />
                </a>
            </li> 
        {/literal}
        </ul>
    
    </div>
    
    <div class="square-box-gray video-description float-break no-js-show"> 
    <ul>
    {foreach $valid_nodes as $valid_node}
        <li><a href={$valid_node.url_alias|ezurl()}>{$valid_node.name|wash()}</a></li>
    {/foreach}
    </ul>
    </div>

</div>
