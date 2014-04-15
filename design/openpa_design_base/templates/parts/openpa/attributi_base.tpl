{def $attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_da_escludere' )
     $oggetti_senza_label = openpaini( 'GestioneAttributi', 'oggetti_senza_label' )
     $attributi_senza_link = openpaini( 'GestioneAttributi', 'attributi_senza_link' )
     $attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
     $attributi_con_zero = openpaini( 'GestioneAttributi', 'zero_is_content', array( 'ente_controllato/onere_complessivo' ) )}

{if is_set( $node.data_map.oggetto )}
    {set $attributi_da_escludere = $attributi_da_escludere|append( 'oggetto' )}
{elseif and( is_set( $node.data_map.abstract ), $node.data_map.abstract.has_content )}
    {set $attributi_da_escludere = $attributi_da_escludere|append( 'abstract' )}
{elseif and( is_set( $node.data_map.short_description ), $node.data_map.short_description.has_content )}
    {set $attributi_da_escludere = $attributi_da_escludere|append( 'short_description' )}
{/if}

<div class="attributi-base">
	{def $style='col-odd'}
   	{foreach $node.object.contentobject_attributes as $attribute}
		
        {if $attribute.has_content}
        
            {if and( $attribute.data_type_string|eq('ezselection'), $attribute.data_text|eq('') )}
            {skip}
            {/if}
            
            {if and( $attributi_con_zero|contains(concat($attribute.object.class_identifier, '/', $attribute.contentclass_attribute_identifier))|not(), $attribute.content|eq('0') )}
            {skip}
            {/if}
		
        	{if $attributi_da_escludere|contains( $attribute.contentclass_attribute_identifier )|not()}
                
                {if and( flip_exists( $attribute.contentobject_id ), $attribute.contentclass_attribute_identifier|eq( 'file' ) )}
                    {set $oggetti_senza_label = $oggetti_senza_label|append( $attribute.contentclass_attribute_identifier )}
                {/if}
				
                {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
				
                {if $oggetti_senza_label|contains( $attribute.contentclass_attribute_identifier )|not()}
				   <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
						<div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
						<div class="col-content"><div class="col-content-design">
							{if $attributi_senza_link|contains( $attribute.contentclass_attribute_identifier )}
								{attribute_view_gui href='nolink' attribute=$attribute}
							{else}
								{attribute_view_gui attribute=$attribute}
							{/if}
						</div></div>
				   </div>
				{else}
				   <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
					<div class="col-content"><div class="col-content-design">
						{if $attributi_senza_link|contains( $attribute.contentclass_attribute_identifier )}
							{attribute_view_gui href='nolink' attribute=$attribute show_flip=true()}
						{else}
							{attribute_view_gui attribute=$attribute show_flip=true()}
						{/if}
					</div></div>
				   </div>
				{/if}
			{/if} 
		{else}
			{if $attribute.contentclass_attribute_identifier|eq('ezflowmedia')}
				{if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                <div class="{$style} col float-break attribute-fullbase-{$attribute.contentclass_attribute_identifier} {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)}col-notitle{/if}">
                    <div class="col-content"><div class="col-content-design">
                    {attribute_view_gui attribute=$attribute}
                    </div></div>
                </div>
			{/if}		
		{/if}
	{/foreach}
</div>
