{if $attribute.data_text|contains( 'http://' )}
<a href="{$attribute.data_text|wash( xhtml )}" title="Visita il sito di {$attribute.object.name|wash()}">
    <strong>{$attribute.data_text|wash( xhtml )}</strong>
</a>
{else}
    {$attribute.data_text|wash( xhtml )}
{/if}