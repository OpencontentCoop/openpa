{*
	OGGETTI INVERSAMENTE CORRELATI
	node	nodo a cui si riferisce
	title	titolo del blocco
	classe	classe su cui filtrare la ricerca
	attrib	attribute su cui filtrare la ricerca
	href	se =nolink non fa vedere il link all'oggetto inversamente correlato
	

*}

{if $classe|eq("struttura")}
	{def $sortby = array( 'class_identifier', true() )}
{else}
	{def $sortby = array( 'name', true() )}
{/if}


{debug-log var=concat($classe,"/",$attrib) msg='classe/attributo' }
{def $stringa_ricerca = concat($classe,"/",$attrib)
     $objects = fetch( 'content', 'reverse_related_objects', hash( 'object_id',$node.object.id, 'attribute_identifier', $stringa_ricerca, 'sort_by',  $sortby ) ) 
     $objects_count = $objects|count()
     $my_node = ""}


{def $style='col-odd'}
{if $objects_count|gt(0)}

	{if $objects_count|lt(100)}

		<div class="oggetti-correlati oggetti-inv-correlati{if $objects|count()|not()} nocontent{/if}">
			<div class="border-header border-box box-trans-blue box-allegati-header">
				<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					<h2>{$title}</h2>
				</div>
				</div></div></div>
			</div>
			<div class="border-body border-box box-violet box-allegati-content">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
		        {foreach $objects as $object}
				  {if $object.can_read}
					{if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
						<div class="{$style} col float-break col-notitle">
						<div class="col-content"><div class="col-content-design">
							{if $classe|eq('ruolo')}
								{$object.name}
								  
                                {if $object.data_map.descrizione_ruolo_speciale.has_content}
                                    {attribute_view_gui attribute=$object.data_map.descrizione_ruolo_speciale}
                                {else}
                                    {if $object.name|contains('Direttore Generale')}
                                    {elseif $object.name|contains('Dirigente con Incarico Speciale')}
                                        presso {attribute_view_gui  href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {elseif $object.name|contains('Capoufficio')}
                                        presso {attribute_view_gui  href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {elseif $object.name|contains('Responsabile del polo sociale')}
                                        presso {attribute_view_gui href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {elseif $object.name|contains('Segretario di circoscrizione')}
                                        presso {attribute_view_gui  href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {elseif $object.name|contains('Funzionario di sezione')}
                                        presso {attribute_view_gui href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {elseif $object.name|contains("Dirigente dell'area")}
                                        "{attribute_view_gui  href=nolink attribute=$object.data_map.struttura_di_riferimento}"
                                    {elseif $object.name|contains('Dirigente del servizio')}
                                        "{attribute_view_gui href=nolink attribute=$object.data_map.struttura_di_riferimento}"
                                    {else}
                                        presso {attribute_view_gui  href=nolink attribute=$object.data_map.struttura_di_riferimento}
                                    {/if}
								    {/if}
							{elseif $classe|eq('struttura')} 
                                {set $my_node=fetch( 'content', 'node', hash( 'node_id', $object.data_map.tipo_struttura.content.relation_list[0].node_id) )}
								   <img class="image-line"  src={concat('icons/crystal/64x64/mimetypes/',$my_node.name,'.png')|ezimage()} alt="{$my_node.name|wash()}" title="{$my_node.name|wash()}" /> 
								   <a href={$object.url_alias|ezurl()}>{$my_node.name|wash()} "{$object.name}"</a>	
							{else}
								{if is_set($href)}
									{if $href|eq("nolink")}
										{$object.name}
									{/if}
								{else}
									{node_view_gui content_node=$object.main_node view='simplified_line'}
								{/if}
							{/if}

						</div></div>
						</div>
				  {/if}
                {/foreach}
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
		</div>
	
	{/if}

{/if}

