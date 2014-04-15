<head profile="http:/dublincore.org/documents/dcq-html/i">
<link rel='schema.DC' href='http://purl.org/dc/elements/1.1/' />
{def $content = ''}
{if is_set( $pagedata.persistent_variable.dublincore_meta )}
    {def $dub_core_contents = $pagedata.persistent_variable.dublincore_meta|explode(",")}
    {foreach $dub_core_contents as $dub_core_item}
        {$dub_core_item}
    {/foreach}
{else}
    {def $class_identifier= ezini('Dubcore_ClassMap', 'Class_identifier', 'dubcore.ini')}
    {foreach $class_identifier as $class_item}
        {if is_set( $module_result.content_info )}
        {if $module_result.content_info.class_identifier|eq($class_item)}
            {set $content = ezini($class_item, 'Content', 'dubcore.ini')}
            <meta name="DC.Description" content="{$content|upfirst}">
        {/if}
        {/if}
    {/foreach}
{/if}

 
{if is_set( $module_result.node_id )}
    {def $rss_export = fetch( 'rss', 'export_by_node', hash( 'node_id', $module_result.node_id ) )}
    {if $rss_export}

    <link rel="alternate" type="application/rss+xml" title="RSS" href="{concat( '/rss/feed/', $rss_export.access_url )|ezurl( 'no' )}" />

    {/if}
    {undef $rss_export}
{/if}
