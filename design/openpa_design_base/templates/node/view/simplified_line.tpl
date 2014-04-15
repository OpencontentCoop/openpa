{*?template charset=utf-8?*}
{*
	TEMPLATE VIDE LINE

	node	nodo di riferimento
	mode	modalita' in cui visualizzare i link
*}

{def 	
	$classi_senza_image = array('file_pdf')
	$classi_senza_data_inline = openpaini( 'GestioneClassi', 'classi_senza_data_inline')
	$classi_senza_correlazioni_inline = openpaini( 'GestioneClassi', 'classi_senza_correlazioni_inline')
 	$attributes_to_show=array('organo_competente', 'circoscrizione','struttura','altra_struttura')
	$attributes_with_title=array('servizio','incarico','ufficio','argomento')
}


{if is_set($mode)}
	{def $mode_link=$mode}
{else}
	{def $mode_link=''}
{/if}


 <div class="class-documento">

	<div class="blocco-titolo-oggetto">    
 		<div class="titolo-blocco-titolo">
			
			{if $node.class_identifier|eq('link')}
        			<h3><a href={$node.data_map.location.content|ezurl()} title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
			{else}
				{if is_set( $node.url_alias )}
					{if $mode_link|eq('virtual')}
         				   <h3>
						<a href={concat("/",$original_link,"/(node)/",$node.node_id)} title="{$node.name|wash()}">
							{$node.name|wash()}
						</a>
					   </h3>
					{else}
         				   <h3><a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a></h3>
					{/if}
				{else}
          				<h3>{$node.name|wash()}</h3>
				{/if}
			{/if}

		</div>

{* mostro (eventualmente) la data di pubblicazione (indotta) *}
		
		{if $classi_senza_data_inline|contains($node.class_identifier)|not}
		<div class="attribute-small">
			di {$node.object.published|l10n(date)}
		</div>
		{/if}

{* mostro gli attributi da mostrare *}

		<div class="attribute-short">

					{if is_set($node.data_map.image)}
					{if $node.data_map.image.has_content}
					<div class="attribute-image">
						{attribute_view_gui attribute=$node.data_map.image href=$node.url_alias|ezurl() 
								    image_class=lista_accordion}
					</div>
					{elseif $classi_senza_image|contains($node.class_identifier)|not()}
					   {if $node.class_identifier|eq('struttura')}
						{def $my_node=fetch( 'content', 'node', 
									hash( 'node_id', $node.data_map.tipo_struttura.content.relation_list[0].node_id) )}
				
						<img class="image-default" src={concat('icons/crystal/64x64/mimetypes/',$my_node.name,'.png')|ezimage()}
 						     alt="{$my_node.name|wash()}" title="{$my_node.name|wash()}" />
					   {else}
						<img class="image-default" 
						     src={concat('icons/crystal/64x64/mimetypes/',$node.object.class_identifier,'.png')|ezimage()}
		 				     alt="{$node.object.class_identifier}" title="{$node.object.class_identifier}" />
						{*
						<img class="image-default" src={concat('/extension/retecivica/design/retecivica/images/icons/crystal/64x64/mimetypes/',$node.class_identifier,'.png')|ezimage()} alt="{$node.class_identifier}" title="{$node.class_identifier}" />
						*}
					   {/if}
					{/if}
					{/if}

					{if $node.class_identifier|eq('user')}
		 			    <div class="servizio-blocco-attributi">

					    {*OGGETTI INVERSAMENTE CORRELATI - RUOLI *}
					    {if is_area_tematica()}

					    {include name=reverse_related_objects_specific_class_and_attribute_asText
						node=$node
						classe='ruolo'
						attrib='utente' 						
						title="Ruolo"
						href="nolink"
						uri='design:parts/reverse_related_objects_specific_class_and_attribute_asText.tpl'}	
					    {else}
					    {include name=reverse_related_objects_specific_class_and_attribute_asText
						node=$node
						classe='ruolo'
						attrib='utente' 
						title="Ruolo"
						href="nolink"
						uri='design:parts/reverse_related_objects_specific_class_and_attribute_asText.tpl'}	

					    {/if}
					    </div>
					{*/if*}

					{elseif $node.class_identifier|eq('politico')}
						{def $ruolo=false()}
						{if $node.data_map.ruolo.has_content}
							{set $ruolo = $node.data_map.ruolo}
						{/if}
						{if $ruolo}
							{attribute_view_gui attribute=$node.data_map.ruolo}
						{else}
							{if $node.data_map.ruolo2.has_content}
								{attribute_view_gui attribute=$node.data_map.ruolo2}
							{elseif $node.data_map.abstract.has_content}		
								{attribute_view_gui attribute=$node.data_map.abstract}
							{/if}
						{/if}
					
					{elseif is_set($node.data_map.abstract)}
						{if $node.data_map.abstract.has_content}
							{attribute_view_gui attribute=$node.data_map.abstract}
						{/if}	
					{elseif is_set($node.data_map.oggetto)}
						{if $node.data_map.oggetto.has_content}
							<div class="attribute-object">
								{attribute_view_gui attribute=$node.data_map.oggetto}
							</div>
						{/if}
					{elseif is_set($node.data_map.testata)}
					   <div class="abstract-line">
					   {if $node.data_map.testata.has_content}
						<p>Tratto da: 
						<strong> {attribute_view_gui  href=nolink attribute=$node.data_map.testata} </strong>
					   	   {if $node.data_map.pagina.content|ne(0)}
							a pag. {attribute_view_gui attribute=$node.data_map.pagina}
					        	{if $node.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui attribute=$node.data_map.pagina_continuazione}
							{/if}
					   	   {/if}
						   {if $node.data_map.autore.has_content}
			 				(di {attribute_view_gui attribute=$node.data_map.autore})
					    	   {/if}
						</p>
					   {/if}    
					   {if $node.data_map.argomento_articolo.has_content}
			 			<p>Su: 
						 <strong>
						 {attribute_view_gui href=nolink attribute=$node.data_map.argomento_articolo}
						 </strong>
						</p>
					   {/if}
					   </div>

					{elseif is_set($node.data_map.utente)}
						{if $node.data_map.utente.has_content}
							<div class="attribute-object">
								{attribute_view_gui attribute=$node.data_map.utente}
							</div>
						{/if}
                    {elseif and( is_set( $node.data_map.abstract ), $node.data_map.abstract.has_content )}
                        {attribute_view_gui attribute=$node.data_map.abstract}
					{elseif $node|has_abstract()}	
						<div class="attribute-node">
							{$node|abstract()|openpa_shorten(300)}
						</div>						
					{/if}
					{if $node.class_identifier|eq('applicativo')}
						{attribute_view_gui attribute=$node.data_map.location_applicativo}
					{/if}
					

				</div>
	</div>
 </div>

{* mostro gli altri attributi *}
	{foreach $node.data_map as $attribute}
			{if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
                    {attribute_view_gui href=nolink attribute=$attribute}
				{/if}
			{elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
				{if $attribute.has_content}
				{if $classi_senza_correlazioni_inline|contains($node.class_identifier)|not}
					<strong>{$attribute.contentclass_attribute_name}: </strong>
                    {attribute_view_gui href=nolink attribute=$attribute}
				{/if}
				{/if}
			{/if}
			
	{/foreach}


 <div class="break"></div>
