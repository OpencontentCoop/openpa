{def $link_classes = array( 'servizio', 'ufficio', 'area', 'organo_politico', 'politico' )}
<div class="content-view-embed">
    <h3>
    {if $link_classes|contains( $node.class_identifier )}
        <a href={$node.url_alias|ezurl()}>{$node.name|wash()}</a>
    {else}
        {$node.name|wash()}
    {/if}
    </h3>
    {if $node|has_abstract()}
        <div class="attibute-text">{$node|abstract()|openpa_shorten( 200 )}</div>
    {/if}
</div>
