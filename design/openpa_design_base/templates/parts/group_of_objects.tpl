{*
	Oggetti passati tramite un elenco

	node			nodo di riferimento
	title			titolo del blocco
	oggetti			array di class_indentifier
*}
{if $oggetti|count()|gt(0)}
	{def $has_content=false()}

	{def $style='col-odd'}
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
		                {foreach $oggetti as $object}
                            {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                            <div class="{$style} col float-break col-notitle">
                            <div class="col-content"><div class="col-content-design">
                                <a href={$object.url_alias|ezurl()}>{$object.name}</a>
                            </div></div>
                            </div>
                		{/foreach}
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>
	{undef $has_content}
{/if}
