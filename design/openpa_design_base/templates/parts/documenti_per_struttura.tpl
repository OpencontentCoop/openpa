{*

	classe_filtro classe per cui cercare con ezfind gli oggetti correlati, con facette
	node= nodo di provenienza

*}


{set-block variable=$open}
<h2 class="block-title">Riferibili a {$node.name}</h2>
<div class="border-box box-gray block-doc">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
{/set-block}

{set-block variable=$close}
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
</div>
{/set-block}

{def $items = fetch( 'openpa', 'faccette_classi_oggetti_correlati_inversi', hash( 'object', $node.object,
                                                                                     'class_filter_type', 'exclude',
                                                                                     'class_filter_array', openpaini( 'GestioneClassi', 'classi_da_escludere_da_blocco_ezfind', array() )
                                                                                    ) )}

{if count( $items )|gt(0)}
    {$open}
    <ul>        
        {foreach $items as $item}
            {foreach $item as $data}
                <li>
                    <a href={concat( "content/advancedsearch?filter[]=subattr_", $data.attribute_identifier, "___name____s", ':', concat( '"', $node.name, '"')|urlencode, '&filter[]=contentclass_id:', $data.class_id, "&SearchButton=Cerca")|ezurl()} title="Link a {$data.class_name|wash}">{$data.class_name|wash} {if count($item)|gt(1)}<small>{$data.attribute_name}</small>{/if} ({$data.value})</a>
                </li>
            {/foreach}
        {/foreach}
    </ul>
{$close}
{/if}
