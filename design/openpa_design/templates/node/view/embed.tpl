<div class="content-view-embed">
    {if $node.class_identifier|eq('link')}
        <a title="Vai al link {$node.data_map.location.content}" target="_blank" href={$node.data_map.location.content|ezurl()}>{$node.name|wash()}</a>
    {else}
        {if is_set( $node.url_alias )}
            <a href="{$node.url_alias|ezurl('no')}" title="Visualizza {$node.name|wash()}">{$node.name|wash()}</a>
        {else}
            {$node.name|wash()}
        {/if}
    {/if}
</div>
