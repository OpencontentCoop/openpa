{if $openpa.content_contacts.has_content}
    <div class="content-detail">
        {if $openpa.content_contacts.show_label}
            <h4 class="panel-title"><strong>{$openpa.content_contacts.label}</strong></h4>
        {/if}
        <ul>
        {foreach $openpa.content_contacts.attributes as $openpa_attribute}
            <li>
                {if $openpa_attribute.full.show_label}
                    <strong>{$openpa_attribute.label}: </strong>
                {/if}
                {attribute_view_gui attribute=$openpa_attribute.contentobject_attribute href=cond($openpa_attribute.full.show_link|not, 'no-link', '')}
            </li>
        {/foreach}
        </ul>
    </div>
{/if}
