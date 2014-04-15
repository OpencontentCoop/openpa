{def $materie_tree = materia_make_tree( $attribute.content.relation_list )}
{foreach $materie_tree as $materia}
{fetch( 'content', 'node', hash( node_id, $materia.node_id ) ).name|wash()}
{if is_set( $materia.children_node_ids )} ({foreach $materia.children_node_ids as $sotto_materia}{fetch( 'content', 'node', hash( node_id, $sotto_materia ) ).name|wash()}{delimiter}, {/delimiter}{/foreach}){/if}
{delimiter}, {/delimiter}
{/foreach}