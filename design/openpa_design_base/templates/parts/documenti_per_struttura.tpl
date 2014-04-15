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

{def $enable_link=true()
     $filter=array()
     $filters=array()
     $custom_filter=array()
     $classes_name= openpaini( 'GestioneClassi', 'classi_da_escludere_da_blocco_ezfind' )
     $classi_con_questa_relazione_array = wrap_user_func('getClassConstraintListAsArray', array( $node.class_identifier ) )
     $temp = array()}

{foreach $classi_con_questa_relazione_array as $class => $classi_con_questa_relazione}
    {foreach $classi_con_questa_relazione as $attribute}
        {if and( $classes_name|contains( $class )|not(), $temp|contains( $attribute.attribute_identifier )|not() )}
            {set $filter = setFilterParameter( concat( "submeta_", $attribute.attribute_identifier, "___main_node_id_si" ), $node.object.main_node_id )
                 $temp = $temp|append( $attribute.attribute_identifier )}
        {/if}
    {/foreach}
{/foreach}
    
{def $facetParameters  = array( hash( 'field', 'class' ) )}
{set $filter = getFilterParameters( false(), 'or' )}
{def $search_hash = hash( 
                          'subtree_array', array( ezini( 'NodeSettings', 'RootNode', 'content.ini' ) ),
                          'limit', 1,
                          'facet', $facetParameters,
                          'filter', $filter )}

{def $search=fetch( ezfind, search, $search_hash )}
{if $search['SearchCount']|gt(0)}
    {$open}
    <ul>        
        {set $temp = array()}
        {foreach $search['SearchExtras'].facet_fields.0.nameList as $facetID => $name}
            {if $classes_name|contains( $name|wash() )|not()}
                {foreach $classi_con_questa_relazione_array as $class => $classi_con_questa_relazione}
                    {foreach $classi_con_questa_relazione as $attribute}                                                
                        {if and( $attribute.class_name|eq( $name ), $temp|contains( $name|wash() )|not() )}
                        {set $temp = $temp|append( $name|wash() )}
                            <li>
                                <a href={concat( "content/advancedsearch?filter[]=subattr_", $attribute.attribute_identifier, "___name____s", ':', concat( '"', $node.name, '"')|urlencode, '&filter[]=', $search['SearchExtras'].facet_fields.0.queryLimit[$facetID], "&SearchButton=Cerca")|ezurl()} title="Link a {$name|wash}">{$name|wash} ({$search['SearchExtras'].facet_fields.0.countList[$facetID]})</a>
                            </li>
                        {/if}
                    {/foreach}
                {/foreach}
            {/if}            
        {/foreach}
    </ul>
{$close}
{/if}
