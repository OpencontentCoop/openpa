{def $metadata = ezini( 'SiteSettings', 'MetaDataArray' )}

{def $author = false()
     $copyright = false()
     $description = false()
     $keywords = false()}

{if openpaini( 'Seo', 'metaAuthor' )}
    {set $author = openpaini( 'Seo', 'metaAuthor' )}
{elseif is_set($module_result.content_info.persistent_variable['author'])}
    {set $author = $module_result.content_info.persistent_variable['author']}
{elseif and(is_set($metadata['author']), $metadata['author']|ne('eZ Systems'))}
    {set $author = $metadata['author']}
{else}
    {set $author = 'OpenContent Scarl'}
{/if}

{if openpaini( 'Seo', 'metaCopyright' )}
    {set $copyright = openpaini( 'Seo', 'metaCopyright' )}
{elseif is_set($module_result.content_info.persistent_variable['copyright'])}
    {set $copyright = $module_result.content_info.persistent_variable['copyright']}
{elseif and(is_set($metadata['copyright']), $metadata['copyright']|ne('eZ Systems'))}
    {set $copyright = $metadata['copyright']}
{else}
    {set $copyright = ezini( 'SiteSettings', 'SiteName' )}
{/if}

{if openpaini( 'Seo', 'metaDescription' )}
    {set $description = openpaini( 'Seo', 'metaDescription' )}
{elseif is_set($module_result.content_info.persistent_variable['description'])}
    {set $description = $module_result.content_info.persistent_variable['description']}
{elseif and(is_set($metadata['description']), $metadata['description']|ne('Content Management System'))}
    {set $description = $metadata['description']}
{else}
    {set $description = concat(ezini( 'SiteSettings', 'SiteName' ), ' - sito istituzionale')}
{/if}

{if openpaini( 'Seo', 'metaKeywords' )}
    {set $keywords = openpaini( 'Seo', 'metaKeywords' )}
{elseif is_set($module_result.content_info.persistent_variable['keywords'])}
    {set $keywords = $module_result.content_info.persistent_variable['keywords']}
{elseif and(is_set($metadata['keywords']), $metadata['keywords']|ne('cms, publish, e-commerce, content management, development framework'))}
    {set $keywords = $metadata['keywords']}
{/if}

{if $author}
    <meta name="author" content="{$author|wash()}" />
{/if}
{if $copyright}
    <meta name="copyright" content="{$copyright|wash()}" />
{/if}
{if $description}
    <meta name="description" content="{$description|wash()}" />
{/if}
{if $keywords}
    <meta name="keywords" content="{$keywords|wash()}" />
{/if}

{foreach $site.meta as $key => $item}
    {if and(array('author', 'copyright', 'description', 'keywords')|contains($key)|not(), is_set( $module_result.content_info.persistent_variable[$key] ))}
        <meta name="{$key|wash}" content="{$module_result.content_info.persistent_variable[$key]|wash}" />
    {/if}
{/foreach}

{undef $metadata $author $copyright $description $keywords}