{if eq( $node.class_identifier, 'link')}
    <a data-contentnode="{$node.node_id}" href="{$node.data_map.location.content}"{if and( is_set( $node.data_map.open_in_new_window ), $node.data_map.open_in_new_window.data_int )} target="_blank"{/if}{if $class} class="{$class|implode(" ")}"{/if} title="{$node.data_map.location.data_text|wash}" class="menu-item-link" rel={$node.url_alias|ezurl}>
        <span>{$node.name|wash()}</span>
    </a>
{else}
    <a data-contentnode="{$node.node_id}" href={if $node.node_id|eq($node.main_node_id)}{$node.url_alias|ezurl}{elseif $node.class_identifier|eq('area_tematica')}{$node.object.main_node.url_alias|ezurl}{else}{$node.url_alias|ezurl}{/if}{if $class} class="{$class|implode(" ")}"{/if}>
        <span>{$node.name|wash()}</span>
    </a>
{/if}