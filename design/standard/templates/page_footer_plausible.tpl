{debug-log msg='Plausible domain' var=openpaini('Seo', 'PlausibleDomain', '(not-enabled)')}
{debug-log msg='Analytics segments' var=concat('logged_in, domain, tenant, ', openpaini('Seo', 'AnalyticsSegments', array())|implode(', '))}
{if openpaini('Seo', 'PlausibleDomain', false())}
{def $segments = openpaini('Seo', 'AnalyticsSegments', array())}
<script defer
        data-domain="{openpaini('Seo', 'PlausibleDomain')}"
        event-logged_in="{cond(fetch('user','current_user').is_logged_in, 'true', 'false')}"
        event-domain="{'/'|ezurl(no,full)}"
        event-tenant="{openpa_instance_identifier()}"
        {foreach $segments as $segment}
        {if is_set($module_result.content_info.persistent_variable[concat('analytics_', $segment)])}
            {def $value = $module_result.content_info.persistent_variable[concat('analytics_', $segment)]}
            {if is_array($value)}
                {foreach $value as $item}
                    {debug-log msg=concat('Analytics segment: ', $segment) var=$item|wash()}{*
        *}event-{$segment}="{$item|wash()}"{*
                *}{/foreach}
            {else}
                {debug-log msg=concat('Analytics segment: ', $segment) var=$value|wash()}{*{*
        *}event-{$segment}="{$value|wash()}"{*
            *}{/if}{undef $value}
        {/if}
        {/foreach}
        src="https://plausible.io/js/script.pageview-props.outbound-links.file-downloads.tagged-events.js"></script>
{undef $segments}
{/if}