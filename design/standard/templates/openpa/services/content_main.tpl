{if $openpa.content_main.has_content}
<div class="main">
    <div class="abstract">
      {if is_set( $openpa.content_main.parts.abstract )}
          {attribute_view_gui attribute=$openpa.content_main.parts.abstract.contentobject_attribute}
      {/if}
    </div>
    {if is_set( $openpa.content_main.parts.image )}
      <div class="text-center">
        {include uri='design:atoms/image.tpl' item=$node image_class=imagefull css_classes="main_image" image_css_class="media-object tr_all_long_hover"}
      </div>
    {/if}    
    {if is_set( $openpa.content_main.parts.full_text )}
        {attribute_view_gui attribute=$openpa.content_main.parts.full_text.contentobject_attribute}
    {/if}
</div>
{/if}
