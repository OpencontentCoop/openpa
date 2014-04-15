{*

	oggetti_correlati	array di class_indentifier
*}

{def 
	$classi_che_producono_contenuti = openpaini( 'GestioneClassi', 'classi_che_producono_contenuti' )
	$mostro_oggetti_inversamente_correlati= false()
}

<h1>Elenco filtrato per <b>{$node.name}</b></h1>

{if $classi_che_producono_contenuti|contains($node.class_identifier)|not()}

	{def $objects=fetch( 'content', 'reverse_related_objects', 
			      hash( 'object_id',$node.object.id, 
				    'sort_by',  array( 'name', true() ),
				    'all_relations', true() ) ) 
 	     $objects_count=$objects|count()
	}
	{set $mostro_oggetti_inversamente_correlati= true()}
{/if}

<div class="attributi-base">

{* ------------------------------- ATTRIBUTI BASE - INIZIO -------------------------------  *}
	



{if $mostro_oggetti_inversamente_correlati}

{def $style='col-odd'}
{if $objects_count|gt(0)}

		<div class="oggetti-correlati oggetti-inv-correlati{if $objects|count()|not()} nocontent{/if}">
			<div class="border-header border-box box-trans-blue box-allegati-header">
				<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					<h2>Risultati trovati</h2>
				</div>
				</div></div></div>
			</div>
			<div class="border-body border-box box-violet box-allegati-content">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">

	{foreach $objects as $object}
		{if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
		<div class="{$style} col float-break col-notitle">
			<div class="col-content"><div class="col-content-design-ristretto">
				<a title="{$object.data_map.abstract.content.output.output_text|explode("<br />")|implode(" ")|strip_tags()|trim()}" href={$object.main_node.url_alias|ezurl()}>{$object.name}</a>
			</div></div>
		</div>
	{/foreach}

				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>
				

{/if}
{/if}
</div>
