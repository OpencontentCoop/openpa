{def $personale_assigned_policies = fetch( 'user', 'user_role', hash( 'user_id', $node.contentobject_id ) )}
{if count($personale_assigned_policies)|gt(0)}
    {* Policies *}
    <li id="node-tab-personalepolicies" class="{if $last}last{else}middle{/if}{if $node_tab_index|eq('personalepolicies')} selected{/if}">
        {if $tabs_disabled}
            <span class="disabled" title="{'Tab is disabled, enable with toggler to the left of these tabs.'|i18n( 'design/admin/node/view/full' )}">{'Policies (%count)'|i18n( 'design/admin/node/view/full',, hash( '%count', $personale_assigned_policies|count ) )}</span>
        {else}
            <a href={concat( $node_url_alias, '/(tab)/policies' )|ezurl} title="{'Show policy overview.'|i18n( 'design/admin/node/view/full' )}">{'Policies (%count)'|i18n( 'design/admin/node/view/full',, hash( '%count', $personale_assigned_policies|count ) )}</a>
        {/if}
    </li>
{/if}