{if is_set( $node.is_container )}
    <a href="{concat('content/browse/',$node.node_id)|ezurl('no')}" title="{$node.path_identification_string}">
        <strong>{$node.name|wash()}</strong>
    </a>
{else}
    <strong><span title="{$node.path_identification_string}">{$node.name|wash()}</span></strong>
{/if}
