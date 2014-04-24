<div class="content-view-embed">
    <h3>
    {if $node.class_identifier|begins_with( 'tipo' )|not()}
        <a href={$node.url_alias|ezurl()}>{$node.name|wash()}</a>
    {else}
        {$node.name|wash()}
    {/if}
    </h3>
    {if $node|has_abstract()}
        <div class="attibute-text">{$node|abstract()|openpa_shorten( 200 )}</div>
    {/if}
</div>
