{if $attribute.data_text}
<a href="{$attribute.content|wash( xhtml )}" target="_blank" title="apri il link in una pagina esterna (si lascerà il sito)">{$attribute.data_text|wash( xhtml )}</a>
{else}
<a href="{$attribute.content|wash( xhtml )}" target="_blank" title="apri il link in una pagina esterna (si lascerà il sito)">{$attribute.content|wash( xhtml )}</a>
{/if}
