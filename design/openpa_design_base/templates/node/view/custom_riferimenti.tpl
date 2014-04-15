{*

	oggetti_correlati	array di class_indentifier
*}

{def 	
	$gruppo_dipendenti = openpaini( 'ControlloUtenti', 'gruppo_dipendenti' )
	$dirigenti_struttura = openpaini( 'ControlloUtenti', concat('dirigenti_',$node.class_identifier) )
	$user_struttura_attribute_ID = openpaini( 'ControlloUtenti', concat('user_',$node.class_identifier,'_attribute_ID') )
	$oggetti_classificazione = openpaini( 'DisplayBlocks', 'oggetti_classificazione' )	
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
	$current_user = fetch( 'user', 'current_user' )
	$has_servizio='none'
	$servizio = array()
	$is_dipendente = false()
	$servizio_utente = fetch( 'content', 'related_objects', hash( 'object_id', $current_user.contentobject_id, 'attribute_identifier',   openpaini( 'ControlloUtenti', 'user_servizio_attribute_ID', 909 ),'all_relations', false() )) 
}


<h1>Riferimenti relativi a <a href="{concat('/',$node.url_alias)}">{$node.name}</a></h1>

<div class="attributi-base">

{* ------------------------------- ATTRIBUTI BASE - INIZIO -------------------------------  *}
	
	{def $style='col-odd' 
	     $attribute=''}



{* ------------------------------- telefoni-------------------------------  *}
	{if $node.data_map.telefoni.has_content}
	{set $attribute=$node.data_map.telefoni }
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


{* ------------------------------- fax -------------------------------  *}
	{if $node.data_map.fax.has_content}
	{set $attribute=$node.data_map.fax }
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
	
{* ------------------------------- email -------------------------------  *}
	{if $node.data_map.email.has_content}
	{set $attribute=$node.data_map.email }
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
	

{* ------------------------------- email secondaria-------------------------------  *}
	{if $node.data_map.email2.has_content}
	{set $attribute=$node.data_map.email2 }
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
	
{* ------------------------------- email certificata-------------------------------  *}
	{if $node.data_map.email_certificata.has_content}
	{set $attribute=$node.data_map.email_certificata }
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


{* ------------------------------- responsabile ------------------------------- *}
	{* Ricerca del Responsabile tramite gli oggetti correlati inversamente secondo 'extended_attribute_filter'*}
	
	{def $resp_correlati = fetch( 'content', 'list', hash( 'parent_node_id', $dirigenti_struttura, 'extended_attribute_filter', 
								hash(   'id', 'ObjectRelationFilter', 
									'params', array( $user_struttura_attribute_ID, $node.object.id ) ) ) )}								
	{if or($resp_correlati|count(),$node.data_map.responsabile.has_content)}
	{if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}									  
	<div class="{$style} col float-break attribute-responsabile">
		<div class="col-title"><span class="label">Dirigente</span></div>
		<div class="col-content"><div class="col-content-design">
			{if $resp_correlati|count()}
				{foreach $resp_correlati as $object_correlato}
					<a href= {$object_correlato.main_node.url_alias|ezurl()}>{$object_correlato.name}</a>
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

{* ------------------------------- personale ------------------------------- *}
	{def $dipendenti_correlati=fetch( 'content', 'reverse_related_objects',
					hash( 
						'object_id', $node.object.id,
						'attribute_identifier', concat('user/',$node.class_identifier), 	
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
			{/if}
			{undef $telefoni_correlati}
			</li>    
		{/foreach}
		</ul>
			
		</div></div>
	</div>
	
	{/if}	

{* ------------------------------- ATTRIBUTI BASE - FINE -------------------------------  *}

{* ------------------------------- LINK AD OGGETTO PRINCIPALE - INIZIO -----------------------------  *}

	<div class="{$style} col float-break attribute-responsabile">
		<div class="col-title"><span class="label">Per ulteriori informazioni</span></div>
		<div class="col-content"><div class="col-content-design">
			<a href="{concat('/',$node.url_alias)}">Leggi tutto su: {$node.name}</a>
		</div></div>
	</div>	


{* ------------------------------- LINK AD OGGETTO PRINCIPALE - FINE -------------------------------  *}


</div>	


