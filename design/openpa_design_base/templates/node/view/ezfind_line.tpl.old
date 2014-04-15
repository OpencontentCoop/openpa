{*def $valore as ''*}
{def $valore = ''}
<tr class="{$sequence}">

<td style="text-align:center;width:5%;"> 
	{if $node.class_identifier|eq('articolo')}
		<div class="immagine-blocco-titolo">
			{attribute_view_gui attribute=$node.data_map.image image_class='small'}

		</div>
	{else}
	{if is_set( $node.class_identifier )}
		{*$node.class_identifier|class_icon('large')*}
		{*$node.class_identifier|mimetype_icon($node.name)*}
		<div class="immagine-blocco-titolo">
			<img class="image-ezfind" src={concat('icons/crystal/64x64/mimetypes/'
								,$node.class_identifier,'.png')|ezimage()}
			     alt="{$node.object.class_identifier}" title="{$node.object.class_identifier}" />

		</div>
	{/if}
	{/if}
</td>
<td>
	{if $node.class_identifier|ne('telefono')}
	<h3>
		{if $node.class_identifier|ne('file_pdf')}
			{*
			{if is_set($node.data_map.name)}
				{if $node.data_map.name.has_content}
					<a href={$node.url_alias|ezurl()}>{attribute_view_gui attribute=$node.data_map.name}</a>
				{else}
					<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
				{/if}
			{elseif is_set($node.data_map.titolo)}
				{if $node.data_map.titolo.has_content}
					<a href={$node.url_alias|ezurl()}>{attribute_view_gui attribute=$node.data_map.titolo}</a>
				{else}
					<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
				{/if}
			{else}
				<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
			{/if}
			*}
			<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
		{else}
			<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
		{/if}		
	</h3>
	{else}
		{* FILTRO oggetti in $nodo_ricerca (Dip comune=54603) di classe "telefono"-attrib "Persona cui si riferisce"-utente,attr_ID=1508 *}
		{def $res_fetch= fetch( 'content', 'related_objects',
					 hash( 'object_id', $node.object.id,
					       'attribute_identifier', concat( $node.object.class_identifier,'/','utente')
                                      	      ) ) }

		{if $res_fetch|count()|gt(0)}
	 		{foreach $res_fetch as $valore}
		 		<h3>{$node.name|wash()}{*<a href="{$valore.main_node.url_alias|ezurl()}"></a>*}</h3> 
				<div class="blocco-attributi-utente">
					{$valore.main_node.name}	
				</div>
				<span class="label"> Telefono </span> {attribute_view_gui href=nolink attribute=$node.data_map.tipo_telefono}
	  		{/foreach}
		{/if}

	{/if}

	{if eq($node.class_identifier,'user') }

				
	 			    <div class="servizio-blocco-attributi">
				    {*OGGETTI INVERSAMENTE CORRELATI - RUOLI *}
				    {include name=reverse_related_objects_specific_class_and_attribute_asText
					node=$node
					classe='ruolo'
					attrib='utente' 					
					title="Ruolo"
					href="nolink"
					uri='design:parts/reverse_related_objects_specific_class_and_attribute_asText.tpl'}	
				    </div>
		





 		{* FILTRO oggetti in $nodo_ricerca (Telefoni=306324) di classe "telefono"-attributo "Persona cui si riferisce"-utente,attribute_ID=1508 *}
                {def $params=array(1508,$node.object.id)}
                {def $nodo_ricerca=306324}
                {def $telefoni_correlati=fetch('content', 'list',
                                                 hash('parent_node_id', $nodo_ricerca,
                                                      'extended_attribute_filter', hash('id', 'ObjectRelationFilter', 'params', $params)
                                                      ) )}

		<div class="blocco-attributi-utente">	  
                  {if $telefoni_correlati|count()}
			<span class="label">Telefono: </span> 
			  {foreach $telefoni_correlati as $nodo_correlato}
				{$nodo_correlato.name}, 
			  {/foreach}
		  {/if}

		  {if $node.data_map.email.has_content}
			<span class="label"> e-mail: </span> {attribute_view_gui attribute=$node.data_map.email}
	  	  {/if}
		</div>


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
			{elseif $node.data_map.abstract.has_content}}			
				{attribute_view_gui attribute=$node.data_map.abstract}
			{/if}
		{/if}
					

	{else}

		{* RIMUOVERE: non esiste!! *}
	 	 {*if $node.highlight.has_content}
	     		<span class="label">Contenuti riscontrati: </span>
			<< {$node.highlight} >>
		 {/if*}
	
		{if eq($node.class_identifier,'mozione') }
		<div class="attribute-mozione">
		 {if is_set($node.data_map.data_consiglio)}
		  {if and($node.data_map.data_consiglio.has_content, $node.data_map.data_consiglio.content.timestamp|gt(0) )}
			<strong>In consiglio:</strong>
			{attribute_view_gui attribute=$node.data_map.data_consiglio}
		  {/if}
		 {/if}
		 {if is_set($node.data_map.note)}
		  {if $node.data_map.note.has_content}
			- <strong> Esito: </strong>
			{attribute_view_gui attribute=$node.data_map.note}
		  {/if}
		 {/if}
		</div>
		{/if}

		{if is_set($node.data_map.abstract)}
		 {if $node.data_map.abstract.has_content}
			<div class="attribute-abstract">
				{attribute_view_gui attribute=$node.data_map.abstract}
			</div>
		 {/if}
		{/if}


		{if is_set($node.data_map.oggetto)}
		 {if $node.data_map.oggetto.has_content}
			<div class="attribute-oggetto">
				{attribute_view_gui attribute=$node.data_map.oggetto}
			</div>
		 {/if}
		{/if}

		{if is_set($node.data_map.testata)}
		 		 {if $node.data_map.testata.has_content}
					<p>Tratto da: 
					<strong> {attribute_view_gui href=nolink attribute=$node.data_map.testata} </strong>
				   	   {if $node.data_map.pagina.content|ne(0)}
						a pag. {attribute_view_gui attribute=$node.data_map.pagina}
					        {if $node.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui attribute=$node.data_map.pagina_continuazione}
						{/if}
					   {/if}
					   {if $node.data_map.autore.has_content}
		 				(di {attribute_view_gui attribute=$node.data_map.autore})
				    	   {/if}
					</p>
					{if $node.data_map.argomento_articolo.has_content}
		 			<p>Su: 
					 <strong>
					 {attribute_view_gui href=nolink attribute=$node.data_map.argomento_articolo}
					 </strong>
					</p>
				    {/if}
				 {/if}
		{/if}


	{/if}

		<div class="blocco-attributi-oggetto">
	     	  	
        	  	{if is_set($node.data_map.telefono)}
				  {if $node.data_map.telefono.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Telefono: </span> </strong>
						{attribute_view_gui attribute=$node.data_map.telefono}
					</div>
				  {/if}
				{elseif is_set($node.data_map.telefoni)}  
				  {if $node.data_map.telefoni.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Telefono: </span> </strong>
						{attribute_view_gui attribute=$node.data_map.telefoni}
					</div>
				  {/if}
				{/if}
        	  		{if is_set($node.data_map.incarico)}
			  	{if $node.data_map.incarico.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Incarico: </span></strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.incarico}
					</div>
				{/if}
			  	{/if}
        	  		{if is_set($node.data_map.ufficio)}
				{if $node.data_map.ufficio.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Ufficio: </span> </strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.ufficio}
					</div>
			  	{/if}
			  	{/if}
				{if is_set($node.data_map.organo_competente)}
				{if $node.data_map.organo_competente.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Competenza: </span> </strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.organo_competente}
					</div>
			  	{/if}
			  	{/if}
				{if is_set($node.data_map.circoscrizione)}
				{if $node.data_map.circoscrizione.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Circoscrizione: </span></strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.circoscrizione}
					</div>
			  	{/if}
			  	{/if}
        	  		{if is_set($node.data_map.struttura)}
				{if $node.data_map.struttura.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Struttura: </span> </strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.struttura}
					</div>
			  	{/if}
			  	{/if}

				{if is_set($node.data_map.altra_struttura)}
				{if $node.data_map.altra_struttura.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Struttura interna: </span> </strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.altra_struttura}
					</div>
			  	{/if}
			  	{/if}

				{if is_set($node.data_map.lista_elettorale)}
				{if $node.data_map.lista_elettorale.has_content}
					<div class="servizio-blocco-attributi">
						<strong><span class="label">Lista: </span> </strong>
						{attribute_view_gui href=nolink attribute=$node.data_map.lista_elettorale}
					</div>
			  	{/if}
			  	{/if}

        	  		{if is_set($node.data_map.file)}
				{if and($node.data_map.file.has_content, $node.class_identifier|eq('file_pdf'))}
					  {attribute_view_gui attribute=$node.data_map.file icon_size='medium' icon_title=$node.name icon='yes'}
			  	{/if}
			  	{/if}


			  	

	  	</div>


</td>

<td>
	{if is_set($node.data_map.servizio)}
		{if $node.data_map.servizio.has_content}
			<div class="servizio-blocco-attributi">
				{attribute_view_gui href=nolink attribute=$node.data_map.servizio}
			</div>
		{/if}
	{elseif eq($node.class_identifier,'telefono') }
		<div class="servizio-blocco-attributi">
			{attribute_view_gui href=nolink attribute=$valore.main_node.data_map.servizio}
		</div>
	{/if}
</td>

<td>
	{if is_set($node.data_map.argomento)}
	{if $node.data_map.argomento.has_content}
		<div class="argomento-blocco-attributi">
			{attribute_view_gui href=nolink attribute=$node.data_map.argomento}
		</div>
	{/if}
	{/if}
</td>

<td>
		<div class="argomento-blocco-attributi">
			{$node.object.published|l10n(date)}
		</div>
</td>

</tr>
