{def $valore = ''}
<tr class="{$sequence}">

<td>
{if $node.class_identifier|ne('telefono')}
	
    <h3>
		<a href={$node.url_alias|ezurl()}>{$node.name|wash}</a>
	</h3>
    
	{else}
		{* FILTRO oggetti in $nodo_ricerca (Dip comune=54603) di classe "telefono"-attrib "Persona cui si riferisce"-utente,attr_ID=1508 *}
		{def $res_fetch = fetch( 'content', 'related_objects', hash( 'object_id', $node.object.id, 'attribute_identifier', concat( $node.object.class_identifier,'/','utente') ) ) }

		{if $res_fetch|count()|gt(0)}
	 		{foreach $res_fetch as $valore}
		 		<h3>{$node.name|wash()}</h3> 
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

        {if openpaini( 'Nodi', 'Telefoni', false() )}
 		{* FILTRO oggetti in $nodo_ricerca (Telefoni=306324) di classe "telefono"-attributo "Persona cui si riferisce"-utente,attribute_ID=1508 *}
        {def $params=array( 1508, $node.object.id )
             $nodo_ricerca = openpaini( 'Nodi', 'Telefoni' )
             $telefoni_correlati=fetch('content', 'list',
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
        {/if}

	{else}
	
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
                
        {if $node|has_abstract()}
        <div class="attribute-abstract">
            {$node|abstract()|openpa_shorten( 200 )}
        </div>
        {/if}


		{if is_set($node.data_map.oggetto)}
            {if $node.data_map.oggetto.has_content}
                <div class="attribute-oggetto">
                    {attribute_view_gui attribute=$node.data_map.oggetto}
                </div>
            {/if}
		{/if}
	
    {/if}

		<div class="blocco-attributi-oggetto">
            
            {if and( is_set($node.data_map.periodo_svolgimento), $node.data_map.periodo_svolgimento.has_content )}
                <div class="servizio-blocco-attributi">
                    <strong><span class="label">{$node.data_map.periodo_svolgimento.contentclass_attribute_name}</span> </strong>
                    {attribute_view_gui href=nolink attribute=$node.data_map.periodo_svolgimento}
                </div>
            {/if}
            
            {if and( is_set($node.data_map.luogo_svolgimento), $node.data_map.luogo_svolgimento.has_content )}
                <div class="servizio-blocco-attributi">
                    <strong><span class="label">{$node.data_map.luogo_svolgimento.contentclass_attribute_name}</span> </strong>
                    {attribute_view_gui href=nolink attribute=$node.data_map.luogo_svolgimento}
                </div>
            {/if}    
            
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

            {if and( is_set($node.data_map.servizio), $node.data_map.servizio.has_content )}
                <div class="servizio-blocco-attributi">
                    <strong><span class="label">Servizio: </span> </strong>
                    {attribute_view_gui href=nolink attribute=$node.data_map.servizio}
                </div>
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
            
            {if is_set($node.data_map.lista_elettorale)}
                {if $node.data_map.lista_elettorale.has_content}
                    <div class="servizio-blocco-attributi">
                        <strong><span class="label">Lista: </span> </strong>
                        {attribute_view_gui href=nolink attribute=$node.data_map.lista_elettorale}
                    </div>
                {/if}
            {/if}
            
            {if and( is_set($node.data_map.argomento), $node.data_map.argomento.has_content )}
                <div class="servizio-blocco-attributi">
                    <strong><span class="label">Argomento: </span> </strong>
                    {attribute_view_gui href=nolink attribute=$node.data_map.argomento}
                </div>
            {/if}
            
            {if is_set($node.data_map.file)}
                {if and($node.data_map.file.has_content, $node.class_identifier|eq('file_pdf'))}
                    {attribute_view_gui attribute=$node.data_map.file icon_size='medium' icon_title=$node.name icon='yes'}
                {/if}
            {/if}
	  	</div>

</td>


<td>
    <div class="argomento-blocco-attributi">
        {$node.object.published|l10n(date)}
    </div>
</td>

</tr>
