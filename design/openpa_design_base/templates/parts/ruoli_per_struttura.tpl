{def $roles = fetch( 'openpa', 'ruoli', hash( 'struttura_object_id', $struttura.contentobject_id ) )}    
{if or( $roles|count(), and( is_set( $struttura.data_map.responsabile ), $struttura.data_map.responsabile.has_content ) )}
    {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
    
    {if $roles|count()}
        {foreach $roles as $item}
        <div class="{$style} col float-break attribute-responsabile">
        <div class="col-title"><span class="label">{$item.name|wash()}</span></div>
        <div class="col-content"><div class="col-content-design">	
            <a href= {$item.url_alias|ezurl()}>
                {attribute_view_gui attribute=$item.data_map.utente}
            </a>					
        </div></div>
        </div>
        {/foreach}
        
    {elseif and( is_set( $struttura.data_map.responsabile ), $struttura.data_map.responsabile.has_content )}
        <div class="{$style} col float-break attribute-responsabile">
        <div class="col-title"><span class="label">{$struttura.data_map.responsabile.contentclass_attribute_name}</span></div>
        <div class="col-content"><div class="col-content-design">
        {if $struttura.data_map.responsabile.has_content}
            {attribute_view_gui attribute=$struttura.data_map.responsabile}
        {/if}
        </div></div>
        </div>
    {/if}    
{/if}