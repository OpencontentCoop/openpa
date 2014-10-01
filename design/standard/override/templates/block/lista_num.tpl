{def $openpa = object_handler($block)}
{set_defaults(hash('show_title', true(), 'items_per_row', 2))}

<div class="relative carousel-top-control {if or( $show_title|not(), $block.name|eq('') )}title-placeholder{/if} {$block.view}">

{if and( $show_title, $block.name|ne('') )}
    <h3 class="widget_title"><a href={$openpa.root_node.url_alias|ezurl()}>{$block.name|wash()}</a></h3>
{/if}

{include uri='design:atoms/carousel.tpl'
         css_id=$block.id
         items=$openpa.content
         root_node=$openpa.root_node
         i_view=panel
         autoplay=0
         image_class=squaremedium
         items_per_row=$items_per_row}
</div>
{unset_defaults(array('show_title','items_per_row'))}
