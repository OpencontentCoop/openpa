{*?template charset=utf-8?*}
{*
	TEMPLATE VIDE LINE
	mode	modalita' in cui visualizzare i link
*}
{def $attributes_to_show=array('testo_news','intro','location','circoscrizione')
	 $classi_senza_data_inline = openpaini( 'GestioneClassi', 'classi_senza_data_inline' )
	 $classi_senza_correlazioni_inline = openpaini( 'GestioneClassi', 'classi_senza_correlazioni_inline' )
	 $classi_con_immagine_inline = openpaini( 'GestioneClassi', 'classi_con_immagine_inline' )
	 $attributes_with_title=array('servizio','incarico','ufficio','struttura', 'argomento','capogruppo','vicecapogruppo')}

{if is_set($mode)}
	{def $mode_link=$mode}
{else}
	{def $mode_link=''}
{/if}
{if $classes_parent_to_edit|contains($node.class_identifier)}
	{if is_set($node.data_map.servizio)}
        {set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.parent.object.id,
                                'attribute_identifier', concat($node.parent.class_identifier, '/servizio'),'all_relations', false() )) }
        {/if}
        {if $servizio|gt(0)}
                {set $has_servizio='ok'}
        {/if}
{else}
	{if is_set($node.data_map.servizio)}
        {set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.object.id,
                        'attribute_identifier', concat($node.class_identifier, '/servizio'),'all_relations', false() )) }
	{/if}
        {if $servizio|gt(0)}
                {set $has_servizio='ok'}
         {/if}
{/if}

{if is_set($show_image)}
	{def $show_icon_image=$show_image}
{else}
	{def $show_icon_image=''}
{/if}

 <div class="class-documento">
	{if $show_icon_image|ne('nessuna')}
    {if $classi_con_immagine_inline|contains($node.class_identifier)}
		{if $node.data_map.image.has_content}
            <div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
		{/if}
	{/if}
	{/if}
	<div class="blocco-titolo-oggetto">    
 		<div class="titolo-blocco-titolo">
			{if $node.class_identifier|eq('link')}
        			<h3><a href={$node.data_map.location.content|ezurl()} target="_blank" title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
			{else}
				{if is_set( $node.url_alias )}
					{if $mode_link|eq('virtual')}
         				<h3><a href={concat("/",$original_link,"/(node)/",$node.node_id)} title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
					{else}
         				<h3><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
					{/if}
				{else}
          				<h3>{$node.name|wash()}</h3>
				{/if}
			{/if}
		</div>

        {* mostro la data di pubblicazione (indotta) *}
		
		{if $classi_senza_data_inline|contains($node.class_identifier)|not}
		<div class="published">
			di {$node.object.published|l10n(date)}
			{if eq($node.class_identifier,'mozione') }
				{if and($node.data_map.data_consiglio.has_content, 
					$node.data_map.data_consiglio.content.timestamp|gt(0) )}
			 		- <strong>in consiglio:  </strong>
					{attribute_view_gui attribute=$node.data_map.data_consiglio}
				{/if}
				{if $node.data_map.note.has_content}
					- <strong>{attribute_view_gui attribute=$node.data_map.note}</strong>
				{/if}
			{/if}
		</div>
		{/if}

        {* mostro abstract o oggetto *}
			
        {if is_set($node.data_map.abstract)}
            {if $node.data_map.abstract.has_content}
                <div class="abstract-line">
                    {attribute_view_gui attribute=$node.data_map.abstract}
                </div>
            {/if}
        {elseif is_set($node.data_map.oggetto)}
            {if $node.data_map.oggetto.has_content}
                <div class="abstract-line">
                {attribute_view_gui attribute=$node.data_map.oggetto}
                </div>
            {/if}
        {/if}

        {* mostro gli altri attributi *}
		{foreach $node.data_map as $attribute}
			{if $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
				{if $classi_senza_correlazioni_inline|contains($node.class_identifier)|not}
					<strong>{$attribute.contentclass_attribute_name}: </strong>
					{attribute_view_gui attribute=$attribute}
				{/if}
				{/if}
			{/if}
			{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
					{attribute_view_gui attribute=$attribute}
				{/if}
			{/if}
		{/foreach}

	</div>
 </div>
 <div class="break"></div>
