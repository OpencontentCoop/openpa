{*
	Oggetti correlati a partire da un elenco

	node			nodo di riferimento
	title			titolo del blocco
	oggetti_correlati	array di class_indentifier
	visualizzazione		'estesa' oppure non popolato
*}

{def $sezioni_per_tutti= openpaini( 'GestioneSezioni', 'sezioni_per_tutti', array())
     $has_content = false()
     $style = 'col-odd'}

{if is_set( $view )|not()}
    {def $view = 'classificazione'}
{/if}

{if $oggetti_correlati|count()|gt(0)}

    {set-block variable=correlati}
    {foreach $oggetti_correlati|unique() as $oggetto_correlato}
        {def $classi_attributi = wrap_user_func('getClassAttributes', array(array($oggetto_correlato)) )
             $classe_attributo = false()}             
        {foreach $classi_attributi as $ca}
            {if $ca.identifier|eq($node.object.class_identifier)}
                    {set $classe_attributo = true() }
            {/if}
        {/foreach}        
        {if $classe_attributo}
            {def $res_fetch=fetch( 'content', 'related_objects', hash( 'object_id', $node.object.id, 'attribute_identifier', concat( $node.object.class_identifier,'/',$oggetto_correlato) ) ) }
        {else}
            {def $res_fetch = array()}
        {/if}
        
        {if $res_fetch|count()|gt(0)}            

            {def $current_label = ''}
            {foreach $res_fetch as $valore}            
            {if $valore.can_read}
                {set $has_content=true()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}		        
                <div class="{$style} col float-break">
                <div class="col-title"><span class="label">                    
                    {if $current_label|ne($valore.class_name)}
                        {$valore.class_name}
                        {set $current_label = $valore.class_name}
                    {/if}
                </span></div>
                <div class="col-content"><div class="col-content-design">		
                    {node_view_gui content_node=$valore.main_node view=$view show_image='nessuna'}
                </div></div>
                </div>
            {/if}            
            {/foreach}
            {undef $current_label}
            				
        {/if}
        {undef $res_fetch $classi_attributi $classe_attributo}
    {/foreach}
    {/set-block}


    {if $has_content}
        <div class="oggetti-correlati">
            <div class="border-header border-box box-violet-gray box-allegati-header">
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
                    {$correlati}
                </div>
                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
            </div>
        </div>
    {/if}
    {undef $has_content}

{/if}
