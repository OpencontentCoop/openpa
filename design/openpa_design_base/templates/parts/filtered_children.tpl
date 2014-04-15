{*
	FIGLI filtrati
	
	node			nodo di riferimento
	title			titolo del blocco
	classes_figli		array di classi per cui filtrare
	classes_figli_escludi	array di classi da escludere
	
*}

{def $sezioni_per_tutti= openpaini( 'GestioneSezioni', 'sezioni_per_tutti', array())
     $current_user = fetch( 'user', 'current_user' )}

{def $children=fetch('content', 'list', hash('parent_node_id', $node.node_id,
                                            'sort_by', $node.sort_array,
                                            'class_filter_type', 'include',
                                            'class_filter_array', $classes_figli) )}

{if $children|count()|gt(0)}
    {def $style='col-odd'}
		<div class="oggetti-correlati filter-children">
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
                {foreach $children as $figlio}
					    {if $sezioni_per_tutti|contains($figlio.object.section_id)}
                            {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                            <div class="{$style} col col-notitle float-break">
                        {else}
                            <div class="square-box-soft-gray">
					    {/if}
						<div class="col-content-design">							
							{node_view_gui content_node=$figlio view='line'}
						</div>
						</div>
                {/foreach}
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>	
{/if}