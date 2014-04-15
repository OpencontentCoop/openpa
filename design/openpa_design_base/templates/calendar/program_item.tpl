<div class="event-wrapper">
{def $node = $event.node}

{if $node.data_map.image.has_content}
    <div class="event-image object-left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
{/if}

<div class="event-content">
<h3>
    <!--<small>{$event.from|l10n( 'shortdate' )} - {$event.to|l10n( 'shortdate' )}</small>-->
    <a href={$node.url_alias|ezurl()} title="Leggi il dettaglio di '{$node.name|wash()}'">{$node.name|wash()}</a>
    {if and( is_set($node.data_map.materia), $node.data_map.materia.has_content )}
        {if $node.data_map.materia.data_type_string|eq( 'ezobjectrelationlist' )}
        <span>[{foreach $node.data_map.materia.content.relation_list as $relation}{fetch( content, object, hash( 'object_id', $relation['contentobject_id'] ) ).name|wash()}{delimiter}, {/delimiter}{/foreach}]</span>
        {else}
        <span>[{$node.data_map.materia.content.keyword_string}]</span>
        {/if}
    {/if}
</h3>

{if and( is_set($node.data_map.abstract), $node.data_map.abstract.has_content )}        
    {attribute_view_gui attribute=$node.data_map.abstract}
{/if}
    
{if and( is_set($node.data_map.periodo_svolgimento), $node.data_map.periodo_svolgimento.has_content )}    
    <p>
        <strong>{$node.data_map.periodo_svolgimento.contentclass_attribute_name}</strong>
        {attribute_view_gui attribute=$node.data_map.periodo_svolgimento}
    </p>
{/if}

{if and( is_set($node.data_map.orario_svolgimento), $node.data_map.orario_svolgimento.has_content )}    
    <p>
        <strong>{$node.data_map.orario_svolgimento.contentclass_attribute_name}</strong>
        {attribute_view_gui attribute=$node.data_map.orario_svolgimento}
    </p>
{/if}

{if and( is_set($node.data_map.luogo_svolgimento), $node.data_map.luogo_svolgimento.has_content )}    
    <p>
        <strong>{$node.data_map.luogo_svolgimento.contentclass_attribute_name}</strong>
        {attribute_view_gui attribute=$node.data_map.luogo_svolgimento}
    </p>
{/if}


</div>

</div>