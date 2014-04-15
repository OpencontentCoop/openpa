{*
	OGGETTI INVERSAMENTE CORRELATI
	node	nodo a cui si riferisce
	title	titolo del blocco
	

*}

{def $classi_che_producono_contenuti = openpaini( 'GestioneClassi', 'classi_che_producono_contenuti' )
     $mostro_oggetti_inversamente_correlati= false()}

{if $classi_che_producono_contenuti|contains($node.class_identifier)|not()}

    {def $objects=fetch( 'content', 'reverse_related_objects', 
                          hash( 'object_id',$node.object.id, 
                                'sort_by',  array( 'name', true() ),
                                'all_relations', true() ) ) 
         $objects_count=$objects|count()}
    {foreach $objects as $object}	
        {if $object.can_read}
        {set $mostro_oggetti_inversamente_correlati= true()}
            {break}
        {/if}
    {/foreach}

{elseif $node.class_identifier|eq(politico)}
    {def $objects=fetch( 'content', 'reverse_related_objects', 
                              hash( 'object_id',$node.object.id, 
                                    'sort_by',  array( 'name', true() ),
                                    'attribute_identifier', 'politico/membri' 
                                    ) ) 
         $objects_count=$objects|count()}
    {foreach $objects as $object}
        {if $object.can_read}
        {set $mostro_oggetti_inversamente_correlati= true()}
        {break}
        {/if}
    {/foreach}	

{elseif and($node.class_identifier|eq(organo_politico),$node.parent_node_id|eq( openpaini( 'Nodi', 'Circoscrizioni', 213591 ) ) )}
    {def $objects=fetch( 'content', 'reverse_related_objects', 
                              hash( 'object_id',$node.object.id, 
                                    'sort_by',  array( 'name', true() ),
                                    'attribute_identifier', 'gemellaggio/circoscrizione' 
                                    ) ) 
         $objects_count=$objects|count()}
    {foreach $objects as $object}
        {if $object.can_read}
        {set $mostro_oggetti_inversamente_correlati= true()}
        {break}
        {/if}
    {/foreach}
{/if}

{if $mostro_oggetti_inversamente_correlati}

    {def $style='col-odd'
         $canread = false()
         $done = array()}
    {if $objects_count|gt(0)}
        {if $objects_count|lt(100)}
            <div class="oggetti-correlati oggetti-inv-correlati{if $objects|count()|not()} nocontent{/if}">
                <div class="border-header border-box box-trans-blue box-allegati-header">
                    <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                    <div class="border-ml"><div class="border-mr"><div class="border-mc">
                    <div class="border-content">
                            <h2>{$title}</h2>
                    </div>
                    </div></div></div>
                </div>
                <div class="border-body border-box box-violet box-allegati-content">
                    <div class="border-ml"><div class="border-mr"><div class="border-mc">
                    <div class="border-content">
                            {foreach $objects as $object}
                            {if $done|contains( $object.id )|not()}
                                {set $canread = fetch( 'content', 'access', hash( 'access', 'read','contentobject', $object ) )}
                                {if $canread}
                                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                                    <div class="{$style} col float-break col-notitle">
                                    <div class="col-content"><div class="col-content-design">
                                            {node_view_gui content_node=$object.main_node view=simplified_line}
                                    </div></div>
                                    </div>
                                {/if}
                                {set $done = $done|append( $object.id )}
                            {/if}
                            {/foreach}
                    </div>
                    </div></div></div>
                    <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
            </div>
        {else}
    
            {set-block variable=reverse_servizio}
            {def $prev_class_identifier='' $counter_c=0 $prev_class_id=0}
            {foreach $objects as $object}
                {set $canread = fetch( 'content', 'access', hash( 'access', 'read','contentobject', $object ) )}
                {if $canread}
                    {if $object.class_name|eq($prev_class_identifier)}
                                    {set $counter_c=$counter_c|sum(1)}
                    {else}
                            
                        {set-block variable=start_class_check}
                            {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                            <div class="{$style} col float-break col-notitle">
                            <div class="col-content"><div class="col-content-design">
                        {/set-block}
                        
                        {set-block variable=end_class_check}
                            </div></div></div>
                        {/set-block}
                        
                        {if $prev_class_identifier|ne('')}
                            {if $node.class_identifier|eq('servizio')}
                                {switch match=$prev_class_identifier}
                                    {case in=array('Deliberazione', 'Determinazione', 'Ordinanza','Decreto', 'Ufficio', 'Regolamento', 'Avviso', 'Bilancio di settore', 'Utente')}
                                        {$start_class_check}
                                            <a href="/content/search?facet_field=class&amp;SearchText=&amp;SearchButton=Cerca&amp;Servizi[]={$node.name}&amp;Filtri[]=contentclass_id:{$prev_class_id}">{$prev_class_identifier} &raquo;</a>
                                        {$end_class_check}
                                    {/case}
                                {/switch}
                                {set $counter_c=1}
                            {/if}
                        {/if}
                    {/if}
                    {set $prev_class_identifier=$object.class_name
                         $prev_class_id=$object.contentclass_id}
                {/if}
                {/foreach}				
            {/set-block}
            {if $reverse_servizio|trim()}
                <div class="oggetti-correlati oggetti-inv-correlati{if $objects|count()|not()} nocontent{/if}">
                    <div class="border-header border-box box-trans-blue">
                        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                        <div class="border-ml"><div class="border-mr"><div class="border-mc">
                        <div class="border-content">
                            <h2>{$title}</h2>
                        </div>
                        </div></div></div>
                    </div>
                    <div class="border-body border-box box-violet">
                        <div class="border-ml"><div class="border-mr"><div class="border-mc">
                        <div class="border-content">
                        {$reverse_servizio}
                        </div>
                        </div></div></div>
                        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                    </div>
                </div>
            {/if}
            
        {/if}
    
    {/if}

{/if}
