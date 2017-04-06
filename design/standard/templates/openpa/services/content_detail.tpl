{if $openpa.content_detail.has_content}
<div class="content-detail">
{foreach $openpa.content_detail.attributes as $openpa_attribute}
    <div>
        {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label|not() )}
            <h4><strong>{$openpa_attribute.label}</strong></h4>
        {/if}
        <div>
            {if and( $openpa_attribute.full.show_label, $openpa_attribute.full.collapse_label )}
                <strong>{$openpa_attribute.label}</strong>
            {/if}
            {attribute_view_gui attribute=$openpa_attribute.contentobject_attribute href=cond($openpa_attribute.full.show_link|not, 'no-link', '')}
        </div>
    </div>
{/foreach}
</div>
{/if}

{include uri=$openpa.content_attachment.template}

{include uri=$openpa.content_gallery.template}
