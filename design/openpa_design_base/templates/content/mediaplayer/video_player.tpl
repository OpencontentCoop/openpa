{* $attribute var is required *}

{def $defaults = ezini( 'DefaultSettings', 'Settings', 'ocmediaplayer.ini' )}
{if $attribute.content.width|gt( 0 )}
    {def $width = $attribute.content.width}
{else}
    {def $width = $defaults.width}
{/if}

{if $attribute.content.height|gt( 0 )}
    {def $height = $attribute.content.height}
{else}
    {def $height = $defaults.height}
{/if}

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


{def $cover_image_class = ezini( 'DefaultSettings', 'VideoCoverImageClass', 'ocmediaplayer.ini' )
     $identifiers = ezini( 'AttributeIdentifiers', 'Identifier', 'ocmediaplayer.ini' )
     $object = $attribute.object
     $file = concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/video")|ezurl
     $cover = false()
     $advert = false()
     $captions = false()}
     
{foreach $identifiers as $key => $identifier}
    {switch match=$key}
        {case match='cover'}                    
            {if and( is_set( $object.data_map.$identifier ), $object.data_map.$identifier.data_type_string|eq( 'ezimage' ), $object.data_map.$identifier.has_content )}                                
                {set $cover = $object.data_map.$identifier}
            {/if}
        {/case}
        {case match='advert'}
            {if and( is_set( $object.data_map.$identifier ), $object.data_map.$identifier.data_type_string|eq( 'ezxmltext' ), $object.data_map.$identifier.has_content )}
                {set $advert = $object.data_map.$identifier.content.output.output_text}
            {/if}
        {/case}
        {case match='captions'}
            {if and( is_set( $object.data_map.$identifier ), $object.data_map.$identifier.data_type_string|eq( 'ezbinaryfile' ), $object.data_map.$identifier.has_content )}
                {set $captions = concat("content/download/",$object.data_map.$identifier.contentobject_id,"/",$object.data_map.$identifier.content.contentobject_attribute_id,"/video")|ezurl}
            {/if}
        {/case}
        {case}{/case}
    {/switch}
{/foreach}

<div class="video">

    <a	class="player no-js-hide"
        href={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/video")|ezurl}
        style="display:block;width:{$width}px;height:{$height}px;"
        title="{$object.name|wash()}"
        id="video-{$attribute.contentobject_id}">
            {if $cover}
                {attribute_view_gui attribute=$cover image_class=$cover_image_class}
            {else}
                <img class='default' src={'play.png'|ezimage()} alt="{$object.name|wash()}" />
            {/if}
    </a>
    

{ezscript_require(array( 'ezjsc::jquery', ocmp('flowplayer','js'), ocmp('ipad','js') ) )}
<script type="text/javascript">
$(document).ready(function(){ldelim}
    flowplayer("video-{$attribute.contentobject_id}", {ocmp('flowplayer','flash')},
    {ldelim}
        clip:
            {ldelim}
            scaling:'fit',
            {if $is_autoplay}autoPlay:true,{else}autoPlay:false,{/if}
            {if $captions}captionUrl:{$captions},{/if}
            autoBuffering: true
            {rdelim},
        plugins:
            {ldelim}
                controls: {ldelim}
                    url: {ocmp('controls','flash')}
                {rdelim},
                {*viral: {ldelim}
                    url: {ocmp('viral','flash')},
                    email: false
                {rdelim},*}
            {if $captions}
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
            {/if}
            {if $advert}
                {if $captions},{/if}
                advert:
                {ldelim}
                    url: {ocmp('content','flash')},
                    stylesheet: {'stylesheets/advert.css'|ezdesign()},
                    html: '{$advert|oneline()|wash(javascript)}',
                    onClick: function() {ldelim}
                        this.hide();
                    {rdelim}
                {rdelim}
            {/if}        
            {rdelim}
    {rdelim}).ipad();
{rdelim})
</script>		


</div>


{undef}
