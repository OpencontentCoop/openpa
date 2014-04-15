{*
	TEMPLATE EXTRA INFO
	node	nodo principale
*}

{*
	DISPOSIZIONE DEI BLOCCHI
	- navigazione
	- motore di ricerca basato sulla classe
	- 1 colonna definita in ezflow del folder
	oppure:
	- 1 colonna definita in ezflow del folder antenato
	oppure:
	- global layout (se definito entro il QUINTO LIVELLO) 

*}


{def $layout1zone = '1ZonesLayoutFolder'
	$node = fetch(content, node, hash(node_id, $module_result.node_id))
	$enabled_container = openpaini( 'GestioneClassi', 'escludere_da_extra_info' )
	$folder =''
    $folder_virtuale = false()
	$classi_filtro = openpaini( 'GestioneClassi', 'classi_che_producono_contenuti' )
	$classe_filtro = false()}


{if $classi_filtro|contains($node.class_identifier)}
	{set $classe_filtro = $node.class_identifier}
{/if}
{if $enabled_container|contains($node.class_identifier)}
	{if is_set($node.data_map.classi_filtro)}
		{if $node.data_map.classi_filtro.content|ne('')}
			{set $folder = $node}
		{/if}
	{/if}
{elseif $enabled_container|contains($node.parent.class_identifier)}
	{set $folder = $node.parent}
{/if}

{if and( is_set( $folder.data_map.classi_filtro ), $folder.data_map.classi_filtro.content|ne('') )}
    {set $folder_virtuale = true()}
{/if}

{def $sort_order=$node.parent.sort_array[0][1]
     $sort_column=$node.parent.sort_array[0][0]
     $sort_column_value=cond( $sort_column|eq( 'published' ), $node.object.published,
                             $sort_column|eq( 'modified' ), $node.object.modified,
                             $sort_column|eq( 'name' ), $node.object.name,
                             $sort_column|eq( 'priority' ), $node.priority,
                             $sort_column|eq( 'modified_subnode' ), $node.modified_subnode,
                             false() ) }
{if $sort_column_value|eq( false() )}
    {set $sort_column_value = $node.object.published
         $sort_column = 'published'}
{/if}

{set-block variable=$open}<div class="extrainfo-box">{/set-block}
{set-block variable=$close}</div>{/set-block}


{* CALENDARIO *}
{if $node.class_identifier|eq('event')}
	<div class="extrainfo-box calendar">
		{include node=$node			
                 uri='design:parts/calendar.tpl' }
    </div>
{/if}

{* BLOCCO DI RICERCA
    compare solo nei folder e negli oggetti con padre folder
	qualora il campo 'engine' sia valorizzato la ricerca viene estesa a tutto il database *}
{if $folder_virtuale}
	<div class="extrainfo-box search_class_and_attributes">
		{include 
			name=searchbox
            node=$node 			
			folder=$folder.name|wash()
			class_filters=$folder.data_map.classi_filtro.content|explode(',')
			uri='design:parts/search_class_and_attributes.tpl' }
	</div>
{/if}


{* BLOCCO PER LE STRUTTURE *}
{if $classe_filtro}
{set-block variable=$blocco_strutture}		
{include name=documenti 
         node=$node
         classe_filtro = $classe_filtro
         uri='design:parts/documenti_per_struttura.tpl' }
{/set-block}			
	{if ne($blocco_strutture|trim(), '')}
		<div class="extrainfo-box documenti_per_struttura">
            {$blocco_strutture}
        </div>
	{/if}
{/if}



{* COLONNA DEFINITA NEL EZFLOW DEL FOLDER (O DELL'ANTENATO) *}
{if $enabled_container|contains($node.class_identifier)}
	
    {if is_set($node.data_map.layout)}		
		{if count( $node.data_map.layout.content )}
			{if $node.data_map.layout.content.zone_layout|ne('0ZonesLayoutFolder')}
				{if and($node.data_map.layout.content.zone_layout|eq($layout1zone), $node.depth|gt(2))}
                    <div class="extrainfo-box data_map_layout">
					{attribute_view_gui attribute=$node.data_map.layout}
                    </div>					
				{/if}
			{/if}
		{/if}
	{/if}
{/if}

{def $findglobal = $node|find_global_layout()}
{if $findglobal}
    <div class="extrainfo-box findglobal">
        {attribute_view_gui attribute=$findglobal.data_map.page}
        {if or( $findglobal.object.can_edit, $findglobal.object.can_remove )}
            <div class="controls square-box-soft-gray info-dipendente float-break" style="position: relative">
                <div style='position: absolute; right: 3px; top: 3px; font-family: sans-serif; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'>X</a></div>
                {if $findglobal.parent_node_id|ne( $node.node_id )}
                    <p>
                        <small>
                            Questo menu di destra &egrave; ereditato da <a href={$findglobal.parent.url_alias|ezurl()}>{$findglobal.parent.name|wash()}</a><br />
                            <strong>Attenzione:</strong> le modifiche apportate a questo menu interverrando anche nel menu dgli oggetti genitori. Per creare un menu dedicato usa il bottone "Crea un menu dedicato"                            
                        </small>
                    </p>
                    {if fetch( 'content', 'access', hash( 'access', 'create', 'contentclass_id', 'global_layout', 'contentobject', $node ) )}
                    <form method="post" action={"content/action"|ezurl} class="left">
                        <input type="hidden" name="HasMainAssignment" value="1" />
                        <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
                        <input type="hidden" name="NodeID" value="{$node.node_id}" />
                        <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
                        <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
                        <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
                        <input type="hidden" value="global_layout" name="ClassIdentifier" />
                        <input type="submit" value="Crea un menu dedicato" name="NewButton" />
                        <input type="hidden" name="RedirectIfDiscarded" value="{$node.url_alias}" />
                        <input type="hidden" name="RedirectURIAfterPublish" value="{$node.url_alias}" />
                    </form>
                    {/if}
                {/if}  
                <form action={"/content/action"|ezurl} method="post">                
                    {if $findglobal.object.can_edit}
                        <input type="submit" name="EditButton" value="Modifica menu" title="Modifica {$findglobal.name|wash()}" />
                        <input type="hidden" name="ContentObjectLanguageCode" value="{$findglobal.object.current_language}" />
                    {/if}
                    {if $findglobal.object.can_remove}
                        <input type="submit" name="ActionRemove" value="Elimina menu" alt="Elimina {$findglobal.name|wash()}" title="Elimina {$findglobal.name|wash()}" />
                    {/if}
                        <input type="hidden" name="ContentObjectID" value="{$findglobal.object.id}" />
                        <input type="hidden" name="NodeID" value="{$findglobal.node_id}" />
                        <input type="hidden" name="ContentNodeID" value="{$findglobal.node_id}" />
                        <input type="hidden" name="RedirectIfDiscarded" value="{$node.url_alias}" />
                        <input type="hidden" name="RedirectURIAfterPublish" value="{$node.url_alias}" />
                </form>
            </div>
        {/if}    
    </div>
{/if}

{if $findglobal|not()}

    {if openpaini( 'MoreLikeThis', 'AbilitaInExtraInfo' )|eq( 'enabled' )}
        {* BLOCCO RICERCA AUTOMATICA MORE LIKE THIS - con filtro sulla classe *}
        {include name=morelikethis 
                 node=$node
                 title='Analoghi a questo'
                 class_filter=$node.class_identifier
                 uri='design:parts/related_contents.tpl' }
    
        {* BLOCCO RICERCA AUTOMATICA MORE LIKE THIS *}
        {include name=morelikethis 
                 node=$node
                 excluded_class_filter=$node.class_identifier
                 title='Forse ti può interessare'
                 uri='design:parts/related_contents.tpl'}
    {elseif fetch( 'content', 'access', hash( 'access', 'create', 'contentclass_id', 'global_layout', 'contentobject', $node ) )}
        <div class="controls square-box-soft-gray info-dipendente float-break" style="position: relative">
            <div style='position: absolute; right: 3px; top: 3px; font-family: sans-serif; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'>X</a></div>
            <form method="post" action={"content/action"|ezurl} class="left">
                <input type="hidden" name="HasMainAssignment" value="1" />
                <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
                <input type="hidden" name="NodeID" value="{$node.node_id}" />
                <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
                <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
                <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
                <input type="hidden" value="global_layout" name="ClassIdentifier" />
                <input type="submit" value="Crea un menu dedicato" name="NewButton" />
            </form>
        </div>
    {/if}
{/if}		



