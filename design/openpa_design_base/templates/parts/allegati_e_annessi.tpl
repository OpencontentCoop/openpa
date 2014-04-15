{*
	TEMPLATE ALLEGATI E ANNESSI

	node	nodo di riferimento
	title	titolo del blocco
	attributi_rilevanti	array degli attributi da evidenziare
*}

{def $trovati_allegati = false()
     $style='col-odd'}
     
{set-block variable=allegati}
    <div class="marked-attributes">
    {foreach $node.object.contentobject_attributes as $attribute}
        {if and($attributi_rilevanti|contains($attribute.contentclass_attribute_identifier), $attribute.has_content)}
            {if $attribute.data_type_string|ne('ezselection')}
                {set $trovati_allegati=true()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                <div class="{$style} col float-break {$attribute.contentclass_attribute_identifier}">
                    <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                    <div class="col-content"><div class="col-content-design">
                        {attribute_view_gui attribute=$attribute}
                    </div></div>
                </div>
            {else}
                {set $trovati_allegati=true()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                <div class="{$style} col float-break {$attribute.contentclass_attribute_identifier}">
                    <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                    <div class="col-content"><div class="col-content-design">
                        {attribute_view_gui attribute=$attribute}
                    </div></div>
                </div>
            {/if}
        {/if}
    {/foreach}
    </div>
{/set-block}

{if $trovati_allegati}
<div class="oggetti-correlati allegati-e-annessi">
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
            {$allegati}
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>
</div>		
{/if}