{def $content = $attribute.content}
{if $content|begins_with('http')|not()}
    {set $content = concat( 'http://', $content )}
{/if}
{if $attribute.data_text}
<a href="{$content|wash( xhtml )}" target="_blank" title="apri il link in una pagina esterna (si lascerà il sito)">{$attribute.data_text|wash( xhtml )}</a>
{else}
<a href="{$content|wash( xhtml )}" target="_blank" title="apri il link in una pagina esterna (si lascerà il sito)">{$attribute.content|wash( xhtml )}</a>
{/if}
