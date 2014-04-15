{*?template charset=utf-8?*}
{*
	TEMPLATE BLOCK LINE

	node	nodo di riferimento
	mode	modalita' in cui visualizzare i link
*}
{def 	$classi_senza_data_inline = openpaini( 'GestioneClassi', 'classi_senza_data_inline')
	$classi_senza_correlazioni_inline = openpaini( 'GestioneClassi', 'classi_senza_correlazioni_inline')
 	$attributes_to_show=array('organo_competente', 'circoscrizione')
	$attributes_with_title=array('servizio','argomento')

}
{if is_set($mode)}
	{def $mode_link=$mode}
{else}
	{def $mode_link=''}
{/if}

 <div class="attribute-header">
	<h2>
		{if $node.class_identifier|eq('link')}
			<a href={$node.data_map.location.content|ezurl()} title="{$node.name|wash()}">{$node.name|wash()}</a>
		{else}
			<a href={$node.url_alias|ezurl()}>{$node.name|wash()}</a>
		{/if}
	</h2>
</div>

{* mostro (eventualmente) la data di pubblicazione (indotta) *}		
{if $classi_senza_data_inline|contains($node.class_identifier)|not}
	{if $classi_senza_data_inline|contains($node.class_identifier)|not}
		<div class="attribute-small">di {$node.object.published|l10n(date)}</div>
	{/if}
{/if}
					
{if is_set($node.data_map.image)}
	{if $node.data_map.image.has_content}
		<div class="attribute-image no-js-hide">
			{attribute_view_gui attribute=$node.data_map.image image_class=lista_accordion}
		</div>
	{else}
	  	<img class="image-default" src={concat('icons/crystal/64x64/mimetypes/',$node.class_identifier,'.png')|ezimage()} 
		     alt="{$node.class_identifier}" title="{$node.class_identifier}" />
	{/if}
{else}
	<img class="image-default" src={concat('icons/crystal/64x64/mimetypes/',$node.class_identifier,'.png')|ezimage()} 
	     alt="{$node.class_identifier}" title="{$node.class_identifier}" />
{/if}
					
{if is_set($node.data_map.abstract)}
	{if $node.data_map.abstract.has_content}
		{attribute_view_gui attribute=$node.data_map.abstract}
	{/if}	
{elseif is_set($node.data_map.oggetto)}
	{if $node.data_map.oggetto.has_content}
		<div class="attribute-object">
			{attribute_view_gui attribute=$node.data_map.oggetto}
		</div>
	{/if}
{else}
	<div class="attribute-node">
		{node_view_gui content_node=$node view='line'}
	</div>
{/if}

{* mostro gli altri attributi *}
	{foreach $node.data_map as $attribute}
			{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
					{attribute_view_gui attribute=$attribute}
				{/if}
			{elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
				{if $classi_senza_correlazioni_inline|contains($node.class_identifier)|not}
					<strong>{$attribute.contentclass_attribute_name}: </strong>
					{attribute_view_gui attribute=$attribute}
				{/if}
				{/if}
			{/if}
			
	{/foreach}
