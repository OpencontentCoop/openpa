{debug-log msg='Plausible domain' var=openpaini('Seo', 'PlausibleDomain', '(not-enabled)')}
{if openpaini('Seo', 'PlausibleDomain', false())}
<script defer
        data-domain="{openpaini('Seo', 'PlausibleDomain')}"
        event-logged_in="{cond(fetch('user','current_user').is_logged_in, 'true', 'false')}"
        event-domain="{'/'|ezurl(no,full)}"
        event-tenant="{openpa_instance_identifier()}"
        src="https://plausible.io/js/script.pageview-props.outbound-links.file-downloads.tagged-events.js"></script>
{/if}