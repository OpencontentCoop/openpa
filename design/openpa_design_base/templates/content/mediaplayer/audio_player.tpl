{* $attribute var is required *}

{def $defaults = ezini( 'DefaultSettings', 'Settings', 'ocmediaplayer.ini' )}

{if $attribute.content.is_autoplay}
    {def $is_autoplay = true()}
{else}
    {def $is_autoplay = $defaults.is_autoplay|eq( 'enabled' )}
{/if}

{if $attribute.content.is_loop}
    {def $is_loop = true()}
{else}
    {def $is_loop = $defaults.is_loop|eq( 'enabled' )}
{/if}

{if $attribute.content.has_controller}
    {def $has_controller = true()}
{else}
    {def $has_controller = $defaults.has_controller|eq('enabled')}
{/if}


{def $identifiers = ezini( 'AttributeIdentifiers', 'Identifier', 'ocmediaplayer.ini' )
     $object = $attribute.object
     $file = concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/audio")|ezurl
     $cover = false()}

{foreach $identifiers as $key => $identifier}
    {switch match=$key}
        {case match='cover'}
            {if and( is_set( $object.data_map.$identifier ), $object.data_map.$identifier.data_type_string|eq( 'ezimage' ), $object.data_map.$identifier.has_content )}
                {set $cover = $object.data_map.$identifier}
            {/if}
        {/case}        
        {case}{/case}
    {/switch}
{/foreach}

<div class="audio">

    <a	class="player no-js-hide"
        href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/audio")|ezurl}
        style="display:block;width:100%px;height:30px;"
        title="{$object.name|wash()}"
        id="audio-{$attribute.contentobject_id}">
    </a>


{ezscript_require(array( 'ezjsc::jquery', ocmp('flowplayer','js') ) )}
{ezcss_require( 'controls-audio.css' )}
<script type="text/javascript">
$(document).ready(function(){ldelim}
    flowplayer("audio-{$attribute.contentobject_id}", {ocmp('flowplayer','flash')},
    {ldelim}
        clip:
            {ldelim}
            {if $is_autoplay}autoPlay:true{else}autoPlay:false{/if}
            {rdelim},
        plugins:
            {ldelim}
                controls: {ldelim}
                    fullscreen: false,
                    height: 30,
                    autoHide: false
                {rdelim},
                audio: {ldelim}
                    url: {ocmp('audio','flash')},
                    provider: 'audio',
                {rdelim},
            {rdelim}
    {rdelim});
{rdelim})
</script>


{*
    <div id="audiocontrols-{$attribute.contentobject_id}" class="controls no-js-hide"></div>
    <div id="audiocontrolsdetail-{$attribute.contentobject_id}" class="player-detail no-js-hide"></div>

{ezscript_require(array( 'ezjsc::jquery', ocmp('flowplayer','js'), ocmp('controls','js') ) )}
{ezcss_require( 'controls-audio.css' )}
<script type="text/javascript">
$(document).ready(function(){ldelim}
    flowplayer("audio-{$attribute.contentobject_id}", {ocmp('flowplayer','flash')},
    {ldelim}
        clip:
            {ldelim}
            {if $is_autoplay}autoPlay:true{else}autoPlay:false{/if},
            onStart: function(song) {ldelim}
                    $('#audiocontrolsdetail-{$attribute.contentobject_id}').html('<span class="artist">'+song.metaData.TPE1+'</span> <span class="title">'+song.metaData.TIT2+'</span>');
                    $('#audiocontrolsdetail-{$attribute.contentobject_id}').appendTo($('#audiocontrols-{$attribute.contentobject_id}'));
                {rdelim}	
            {rdelim},
        plugins:
            {ldelim}
                controls: {ldelim}
                    url: {ocmp('controls','flash')},
                    all: null,
                    height: 1
                {rdelim},
                audio: {ldelim}
                    url: {ocmp('audio','flash')},
                    provider: 'audio',
                {rdelim},
            {rdelim}
    {rdelim}).controls("audiocontrols-{$attribute.contentobject_id}");
{rdelim})
</script>

*}

</div>


{undef}
