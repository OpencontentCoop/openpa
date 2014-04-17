{if is_array( $node )}
    {def $children = $node}
    {set $node = $children[0]}
{else}
    {def $children = fetch( 'content', 'list', hash('parent_node_id', $node.node_id,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array( 'audio' ),
                                                    'sort_by', $node.sort_array,
                                                    'limit', 100 ) )}
{/if}

{def $defaults = ezini( 'DefaultSettings', 'Settings', 'ocmediaplayer.ini' )}



{def $cover_image_class = ezini( 'DefaultSettings', 'VideoCoverImageClass', 'ocmediaplayer.ini' )
     $thumb_image_class = ezini( 'DefaultSettings', 'VideoThumbImageClass', 'ocmediaplayer.ini' )
     $identifiers = ezini( 'AttributeIdentifiers', 'Identifier', 'ocmediaplayer.ini' )
     $file = $identifiers['video_file']
     $cover = $identifiers['cover']
     $captions = $identifiers['captions']}

<div class="video">

    <a	class="player no-js-hide"
        href={concat("content/download/",$children[0].data_map.$file.contentobject_id,"/",$children[0].data_map.$file.content.contentobject_attribute_id,"/",$children[0].data_map.$file.content.original_filename)|ezurl}
        style="display:block;width:{$defaults['width']}px;height:{$defaults['height']}px;"
        title="{$children[0].name|wash()}"
        id="video-{$node.node_id}">
        {if and( is_set( $children[0].data_map.$cover ), $children[0].data_map.$cover.data_type_string|eq( 'ezimage' ), $children[0].data_map.$cover.has_content )}
            {attribute_view_gui attribute=$children[0].data_map.cover image_class=$cover_image_class}
        {/if}
    </a>
    
    <div id="videocontrols-{$node.node_id}" class="controls"></div>

{ezscript_require(array( 'ezjsc::jquery', ocmp('flowplayer','js'), ocmp('controls','js'), ocmp('playlist','js') ) )}
{ezcss_require( 'controls-apple.css' )}
<style type="text/css">
#videocontrols-{$node.node_id} .track{ldelim}width:{$defaults['width']|sub(20)}px{rdelim}
</style>
<script type="text/javascript">
$(document).ready(function(){ldelim}
    flowplayer("video-{$node.node_id}", {ocmp('flowplayer','flash')},
    {ldelim}
        clip:
            {ldelim}
            scaling:'fit',
            baseUrl: "{ezurl(no,full)}",
            autoPlay:false,           
            autoBuffering: true
            {rdelim},
        playlist: [
        {foreach $children as $item}
            {ldelim}
                url: "{concat('content/download/',$item.data_map.$file.contentobject_id,'/',$item.data_map.$file.content.contentobject_attribute_id,'/',$item.data_map.$file.content.original_filename)}",
                title: "{$item.name|wash(javascript)}"
                {if and( is_set( $item.data_map.$cover ), $item.data_map.$cover.data_type_string|eq( 'ezimage' ), $item.data_map.$cover.has_content )}
                    ,image: "{$item.data_map.$cover.content.$thumb_image_class.full_path|ezroot(no)}"
                {/if}
                {if and( is_set( $item.data_map.$captions ), $item.data_map.$captions.data_type_string|eq( 'ezbinaryfile' ), $item.data_map.$captions.has_content )}
                    ,captionUrl: "{concat("content/download/",$item.data_map.$captions.contentobject_id,"/",$item.data_map.$captions.content.contentobject_attribute_id,"/",$item.data_map.$captions.content.original_filename)|ezurl(no,full)}"
                {/if}
            {rdelim}
            {delimiter},{/delimiter}
        {/foreach}
        ],
        plugins:
            {ldelim}
                controls: {ldelim}
                    url: {ocmp('controls','flash')},
                    all: null,
                    height: 1
                {rdelim},
                viral: {ldelim}
                    url: {ocmp('viral','flash')},
                    email: false
                {rdelim},
                captions:
                {ldelim}
                    url: {ocmp('captions','flash')},
                    captionTarget: 'content'
                {rdelim},        
                content:
                {ldelim}
                    url: {ocmp('content','flash')},
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
    {rdelim}).controls("videocontrols-{$node.node_id}").playlist("#playlist-{$node.node_id}");
{rdelim})
</script>		


</div>
<div id="playlist-{$node.node_id}" class="playlist">
    {literal}
    <a href="${url}">
        ${title}
        <img src="${image}" />
    </a>
    {/literal}
</div>

{undef}
