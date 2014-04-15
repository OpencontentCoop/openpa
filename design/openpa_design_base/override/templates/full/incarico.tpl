{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $user_struttura_attribute_ID = openpaini( 'ControlloUtenti', concat('user_',$node.class_identifier,'_attribute_ID') )
	 $oggetti_classificazione = openpaini( 'DisplayBlocks', 'oggetti_classificazione' )	
	 $role_folder_responsabili_incarico = openpaini( 'ControlloUtenti', 'role_folder_responsabili_incarico' )
	 $role_struttura_attribute_ID = openpaini( 'ControlloUtenti', 'role_struttura_attribute_ID' )
	 $attributi_classificazione_strutture = openpaini( 'DisplayBlocks', 'attributi_classificazione_strutture' )		
	 $oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati' )
	 $oggetti_correlati_centro = openpaini( 'DisplayBlocks', 'oggetti_correlati_centro' )
	 $oggetti_senza_label = openpaini( 'GestioneAttributi', 'oggetti_senza_label' )
	 $attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_da_escludere' )
	 $attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
	 $attributi_a_destra = openpaini( 'GestioneAttributi', concat('attributi_a_destra_',$node.class_identifier) )
	 $classes = openpaini( 'GestioneAttributi', 'classi_figlie_da_escludere' )
	 $classes_figli = openpaini( 'GestioneAttributi', 'classi_figlie_da_includere' )
	 $classi_commentabili = openpaini( 'EnabledBlocks', 'classi_commentabili' )
	 $classes_parent_to_edit=array('file_pdf', 'news')
	 $classi_da_non_commentare=array('news', 'comment')
	 $current_user = fetch( 'user', 'current_user' )}

{ezscript_require( array( 'ezjsc::jquery', 'jexcerpt.js', 'excerpt.js' ) )}

<div class="border-box">
<div class="border-content">

  <div class="global-view-full content-view-full">
   <div class="class-{$node.object.class_identifier}">

    <h1>{$node.name|wash()}</h1>
    <div class="last-modified">Ultima modifica: <strong>{$node.object.modified|l10n(date)}</strong></div>
	{*if and( is_set( $node.object.data_map.cod_servizio ), is_set( $node.object.data_map.cod_incarico ), is_set( $node.object.data_map.cod_ufficio ), is_set( $node.object.data_map.cod_struttura ), is_set( $node.object.data_map.cod_altrastruttura ) )}
    <div class="last-modified">Codice: 
		<strong>
			{attribute_view_gui attribute=$node.object.data_map.cod_servizio} 
			{if gt($node.object.data_map.cod_incarico,0)} .{attribute_view_gui attribute=$node.object.data_map.cod_incarico} {/if}
			{if gt($node.object.data_map.cod_ufficio,0)} .{attribute_view_gui attribute=$node.object.data_map.cod_ufficio} {/if}
			{if gt($node.object.data_map.cod_struttura,0)} .{attribute_view_gui attribute=$node.object.data_map.cod_struttura} {/if}
			{if gt($node.object.data_map.cod_altrastruttura,0)} .{attribute_view_gui attribute=$node.object.data_map.cod_altrastruttura} {/if}
		</strong>
	</div>
    {/if*}

    {* EDITOR TOOLS *}
	{include name = editor_tools
             node = $node             
             uri = 'design:parts/openpa/editor_tools.tpl'}
	
	{* ATTRIBUTI : mostra i contenuti del nodo *}
    {include name = attributi_principali
             uri = 'design:parts/openpa/attributi_principali.tpl'
             node = $node}
	
	<div class="attributi-base">
        {def $style='col-odd' 
             $attribute=''}
        
    {* ------------------------------- sede ------------------------------- *}
        {if $node.data_map.sede.has_content}
        {set $attribute=$node.data_map.sede }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}
        
    {* ------------------------------- indirizzo ------------------------------- *}
        {if $node.data_map.indirizzo.has_content}
        {set $attribute=$node.data_map.indirizzo }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}
        
    {* ------------------------------- telefoni-------------------------------  *}
        {if $node.data_map.telefoni.has_content}
        {set $attribute=$node.data_map.telefoni }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}
    
    
    {* ------------------------------- fax -------------------------------  *}
        {if $node.data_map.fax.has_content}
        {set $attribute=$node.data_map.fax }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}	
        
    {* ------------------------------- email -------------------------------  *}
        {if $node.data_map.email.has_content}
        {set $attribute=$node.data_map.email }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}		
        
    {* ------------------------------- email secondaria-------------------------------  *}
        {if $node.data_map.email2.has_content}
        {set $attribute=$node.data_map.email2 }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}		
        
    {* ------------------------------- email certificata-------------------------------  *}
        {if $node.data_map.email_certificata.has_content}
        {set $attribute=$node.data_map.email_certificata }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}		
    
    
    {* ------------------------------- responsabile dal ruolo------------------------------- *}
        {* Ricerca del Responsabile tramite gli oggetti correlati inversamente secondo 'extended_attribute_filter' *}
    
        {def $resp_correlati_byrole = fetch('content','list', hash('parent_node_id', $role_folder_responsabili_incarico, 'extended_attribute_filter', 
                                         hash('id', 'ObjectRelationFilter', 
                                          'params', array($role_struttura_attribute_ID,$node.object.id) 
                                          ) ) )}
        
        {if $resp_correlati_byrole|count()}
            {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
            <div class="{$style} col float-break attribute-responsabile">
                <div class="col-title"><span class="label">Dirigente</span></div>
                <div class="col-content"><div class="col-content-design">
                    {if $resp_correlati_byrole|count()}
                        {foreach $resp_correlati_byrole as $object_correlato}			
                             <a href= {$object_correlato.url_alias|ezurl()}>
                                {attribute_view_gui attribute=$object_correlato.data_map.utente}
                            </a>
                            {delimiter} <span class="delimiter">-</span> {/delimiter}
                        {/foreach}
                    {else}
                        {if $node.data_map.responsabile.has_content}
                            {attribute_view_gui attribute=$node.data_map.responsabile}
                        {/if}
                    {/if}		
                </div></div>
            </div>	
        {/if}
    
    
        
    {* ------------------------------- orario ------------------------------- *}
        {if $node.data_map.orario.has_content}
        {set $attribute=$node.data_map.orario }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">						
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}		
    
    {* ------------------------------- articolazioni interne ------------------------------- *}
    {include node=$node uri='design:parts/articolazioni_interne.tpl'}
    
            
    {* ------------------------------- riferimenti_utili-------------------------------  *}
        {if $node.data_map.riferimenti_utili.has_content}
        {set $attribute=$node.data_map.riferimenti_utili }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}	
    
    {* ------------------------------- competenze ------------------------------- *}
        {if $node.data_map.competenze.has_content}
        {set $attribute=$node.data_map.competenze }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui  attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}		
    
    {* ------------------------------- personale ------------------------------- *}
        {def $dipendenti_correlati=fetch( 'content', 'reverse_related_objects',
                        hash( 
                            'object_id', $node.object.id,
                            'attribute_identifier', $user_struttura_attribute_ID, 	
                            'sort_by',  array( 'name', true() )
                        ) 
                    )}	
        
        {def $informatici_correlati=fetch( 'content', 'list',
                        hash(
                            'parent_node_id',  openpaini( 'ControlloUtenti', 'referenti_informatici' ),
                            'extended_attribute_filter', hash('id', 'ObjectRelationFilter', 
                                'params', array( $user_struttura_attribute_ID, $node.object.id )),
                            'sort_by',  array( 'name', true() )
                        ) 
                    )}		
    
        {def $editor_correlati=fetch( 'content', 'list',
                        hash(
                            'parent_node_id',  openpaini( 'ControlloUtenti', 'redattori' ),
                            'extended_attribute_filter', hash('id', 'ObjectRelationFilter', 
                                'params', array( $user_struttura_attribute_ID, $node.object.id )),
                            'sort_by',  array( 'name', true() )
                        ) 
                    )}		
    
        {if $dipendenti_correlati|count()}	
        {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
        <div class="{$style} col float-break attribute-personale">
            <div class="col-title"><span class="label">Personale</span></div>
            <div class="col-content"><div class="col-content-design">					
        
            <ul>
            {foreach $dipendenti_correlati as $object_correlato}
                 <li><a href={$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
    
                {def $telefoni_correlati=fetch('content', 'list',
                            hash('parent_node_id', openpaini( 'ControlloUtenti', 'telefoni' ),
                                 'extended_attribute_filter', hash('id', 'ObjectRelationFilter', 
                                    'params', array(openpaini( 'ControlloUtenti', 'utente_telefono_attribute_ID' ), $object_correlato.id) ) ) )}
                {if $telefoni_correlati|count()}
                    {foreach $telefoni_correlati as $tel_correlato}
                        <small>
                        {$tel_correlato.name} 					
                        {if $tel_correlato.data_map.numero_interno.has_content}
                            (interno: {attribute_view_gui attribute=$tel_correlato.data_map.numero_interno})
                        {/if}
                        </small>
                    {/foreach}
                {elseif is_set( $object_correlato.data_map.telefono )}
                    <small>{attribute_view_gui attribute=$object_correlato.data_map.telefono}</small>
                {/if}
                {undef $telefoni_correlati}
                </li>    
            {/foreach}
            </ul>
            
            {if $informatici_correlati|count()}	
            <h5>Referenti informatici</h5>
            <ul>
            {foreach $informatici_correlati as $object_correlato}
                 <li><a href={$object_correlato.url_alias|ezurl()}>{$object_correlato.name}</a></li>     
            {/foreach}
            </ul>
            {/if}
            
            {if $editor_correlati|count()}	
            <h5>Redattori sito/intranet</h5>
            <ul>
            {foreach $editor_correlati as $object_correlato}
                 <li><a href={$object_correlato.url_alias|ezurl()}>{$object_correlato.name}</a></li>        
            {/foreach}
            </ul>
            {/if}
        
            </div></div>
        </div>
        
        {/if}		
    
    {* ------------------------------- gps (mappa) -------------------------------  *}
        {if $node.data_map.gps.has_content}
        {set $attribute=$node.data_map.gps }
            {if $attributi_da_escludere|contains($attribute.contentclass_attribute_identifier)|not()}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)|not()}
                        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {else}
                        <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-content"><div class="col-content-design">						
                                {attribute_view_gui attribute=$attribute}
                            </div></div>
                        </div>
                    {/if}
            {/if}		
        {/if}
    
    
        {* CLASSIFICAZIONE - POSIZIONAMENTO NELL'ORGANIGRAMMA*}
        {* OGGETTI CORRELATI rispetto ad attributi specifici - oggetti_classificazione *}   
        {include name=classificazione_strutture 
                    node=$node 
                    title="Posizionamento nell'organigramma"
                    attributi_classificazione=$attributi_classificazione_strutture
                    uri='design:parts/classificazione_strutture.tpl'}
	</div>	

	</div>
  </div>
</div>
</div>
