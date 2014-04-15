{def $style = 'col-odd'
     $identifiers = array( 'applicabilita',
                           'riferimenti_normativi',
                           'contenuto_obbligo' )
     $group_has_content = false()}     

{foreach $node.data_map as $identifier => $attribute}
    {if and( $identifiers|contains( $identifier ), $attribute.has_content )}
        {set $group_has_content = true()}
        {break}
    {/if}
{/foreach}
{if $group_has_content}
<div class="oggetti-correlati"  style="font-size: .85em">        
    <div class="border-body border-box box-violet box-allegati-content">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
            <div class="attributi-base">
                {foreach $node.data_map as $identifier => $attribute}                
                {if and( $identifiers|contains( $identifier ), $attribute.has_content, $attribute.data_text|ne('') )}
                    {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
                    <div class="{$style} col float-break">
                        <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                        <div class="col-content"><div class="col-content-design">
                        {attribute_view_gui attribute=$attribute}
                        </div></div>
                    </div>
                {/if}
                {/foreach}
            </div>
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>
</div>
{/if}