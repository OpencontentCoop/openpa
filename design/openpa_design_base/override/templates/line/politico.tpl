{*?template charset=utf-8?*}
{*
	TEMPLATE VIDE LINE
	mode	modalita' in cui visualizzare i link
*}
{def $classi_con_immagine_inline = openpaini( 'GestioneClassi', 'classi_con_immagine_inline' )
	 $attributes_to_show= openpaini( 'GestioneAttributi', 'attributes_to_show_politici', array())
	 $attributes_structure=array('lista_elettorale','gruppo_politico')
	 $attributes_with_title=openpaini( 'GestioneAttributi', 'attributes_with_title_politici', array())
}
{if is_set($mode)}
	{def $mode_link=$mode}
{else}
	{def $mode_link=''}
{/if}

{if is_set($show_image)}
	{def $show_icon_image=$show_image}
{else}
	{def $show_icon_image=''}
{/if}

<div class="class-politico float-break">
 	{if $show_icon_image|ne('nessuna')}
    {if $classi_con_immagine_inline|contains($node.class_identifier)}
		{if $node.data_map.image.has_content}
			<div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
		{/if}
	{/if}
	{/if}
	<div class="blocco-titolo-oggetto">    
 		<div class="titolo-blocco-titolo">            
            {if is_set( $node.url_alias )}
                <h3><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
            {else}
                <h3>{$node.name|wash()}</h3>
            {/if}
		</div>
        <div class="published">
            {foreach $node.data_map as $attribute}
                {if $attributes_structure|contains($attribute.contentclass_attribute_identifier)}
                    {if $attribute.has_content}
                        {if eq($attribute.data_type_string,'ezdate')}					                            
                            {if $attribute.content.is_valid}
                                {$attribute.contentclass_attribute_name}: 
                                {attribute_view_gui attribute=$attribute}
                            {/if}
                        {else}
                            {$attribute.contentclass_attribute_name}: 
                            {attribute_view_gui attribute=$attribute}
                        {/if}
                    {/if}
                {/if}
            {/foreach}
        </div>
        
        {if $node|has_abstract}
            <div class="abstract-line">{$node|abstract}</div>
        {/if}

        {* mostro gli altri attributi *}
		{if count($attributes_to_show)|gt(0)}
        {foreach $node.data_map as $attribute}		
			{if $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
					</p><strong>{$attribute.contentclass_attribute_name}: </strong></p>
					{attribute_view_gui attribute=$attribute}
				{/if}
			{/if}
			{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
					<p>{attribute_view_gui attribute=$attribute}</p>
				{/if}
			{/if}	
		{/foreach}
        {/if}
	</div>
</div>
