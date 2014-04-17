{def $classiAreeTematiche = array( 'area_tematica' )}

{def $title = concat( 'Link a ', $node.name|wash() )}
{if $node|has_abstract()}
    {set $title = $node|abstract()|openpa_shorten(30)}
{/if}

{def $href = $node.url_alias|ezurl(no)}
{if and( is_set( $node.data_map.location ), $node.data_map.location.has_content )}
    {set $href = $node.data_map.location.content}    
{elseif $classiAreeTematiche|contains( $node.class_identifier )}
    {set $href = $node.object.main_node.url_alias|ezurl(no)}
{/if}

{def $target = false()}
{if and( is_set( $node.data_map.open_in_new_window ), $node.data_map.open_in_new_window.data_int )}
    {set $target = "_blank"}
{/if}

{if is_set( $class )|not()}
    {def $class = $node.name|slugize()}
{/if}

<a data-contentnode="{$node.node_id}" class="{$class}" href="{$href}" {if $target}target="{$target}"{/if} title="{$title}">
    <span>{$node.name|wash()}</span>     
</a>

{undef $title $href $target $class}