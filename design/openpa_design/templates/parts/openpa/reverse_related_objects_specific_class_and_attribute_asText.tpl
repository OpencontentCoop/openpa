{*
	OGGETTI INVERSAMENTE CORRELATI AS TEXT (mette il risultato come testo senza blocco)
	node	nodo a cui si riferisce
	title	titolo del blocco
	classe	classe su cui filtrare la ricerca
	attrib	attribute su cui filtrare la ricerca
	href	se =nolink non fa vedere il link all'oggetto inversamente correlato
	

*}

{def 
 	$stringa_ricerca=concat($classe,"/",$attrib)
	$objects=fetch( 'content', 'reverse_related_objects', 
	      		  hash( 'object_id',$node.object.id, 
		    		'attribute_identifier', $stringa_ricerca,
				'sort_by',  array( 'name', true() ),
				) ) 
     	$objects_count=$objects|count()
}



{def $style='col-odd'}
{if $objects_count|gt(0)}
	{if $objects_count|lt(100)}

		<div class="oggetti-correlati-text oggetti-inv-correlati{if $objects|count()|not()} nocontent{/if}">		
		                  {foreach $objects as $object}
				  {if $object.can_read}
					<p>
							{if $classe|eq('ruolo')}
								{$object.name}
								    {* 
								    {if $object.main_node.data_map.descrizione_ruolo_speciale.has_content}
							  		{attribute_view_gui is_area_tematica=$is_area_tematica attribute=$object.main_node.data_map.descrizione_ruolo_speciale}
								    {else}
								    *}
								     {if $object.name|contains('Direttore Generale')}
									.
								     {elseif $object.name|contains('Dirigente con Incarico Speciale')}
								  	   presso {attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}
								     {elseif $object.name|contains('Capoufficio')}
								  	   presso {attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}
								     {elseif $object.name|contains('Responsabile del polo sociale')}
								  	   presso {attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}
								     {elseif $object.name|contains('Segretario di circoscrizione')}
								  	      presso {attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}
								     {elseif $object.name|contains('Funzionario di sezione')}
								  	       presso {attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}
								     {elseif $object.name|contains("Dirigente dell'area")}
								  	       "{attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}"
								     {elseif $object.name|contains('Dirigente del servizio')}
								  	         "{attribute_view_gui is_area_tematica=$is_area_tematica  href=nolink
								  		   attribute=$object.main_node.data_map.struttura_di_riferimento}"

								     {/if}
								    {*  {/if} *}
							{elseif $classe|eq('struttura')} 
							  {def $my_node=fetch( 'content', 'node', 
							   		hash( 'node_id', $object.main_node.data_map.tipo_struttura.content.relation_list[0].node_id) )}
								   <img class="image-line" 
									src={concat('icons/crystal/64x64/mimetypes/',$my_node.name,'.png')|ezimage()}
 						     			alt="{$my_node.name|wash()}" title="{$my_node.name|wash()}" /> 
								   <a href={$object.main_node.url_alias|ezurl()}>{$my_node.name|wash()} "{$object.name}"</a>	
							{else}

								{if $href|eq("nolink")}
									{$object.name}
								{else}

							  	  {if $is_area_tematica}
									{def    $BNode_id=module_params().parameters.NodeID
                								$local_link=fetch(content,node,hash(node_id,$BNode_id))}
									<a href={concat($local_link.url_alias, '/(reference)/',$object.main_node_id)|ezurl()}>{$object.name}</a>
							  	  {else}
									<a href={$object.main_node.url_alias|ezurl()}>{$object.name}</a>
								
							  	  {/if}
								{/if}
							{/if}
							
					</p>
				{/if}
                		{/foreach}
		</div>
	
	{/if}


{/if}

