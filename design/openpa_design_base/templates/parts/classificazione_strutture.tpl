{*
	Oggetti correlati a partire da un elenco
	node				nodo di riferimento
	title				titolo del blocco
	attributi_classificazione	array di attributi
*}


{def $has_content=false()
	 $style='col-odd' }


{def $attributes = $node.object.contentobject_attributes}
{foreach $attributes as $attribute}
{if $attributi_classificazione|contains($attribute.contentclass_attribute_identifier)}
    {if $attribute.has_content}
        {set $has_content = true()}
    {/if}
{/if}
{/foreach}

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

             {foreach $attributes as $attribute}
                {if $attributi_classificazione|contains($attribute.contentclass_attribute_identifier)}
                    {if $attribute.has_content}
                        {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
                {/if}
                {/foreach}
            </div>
            </div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
        </div>
    </div>
{/if}
{undef $has_content $attributes}