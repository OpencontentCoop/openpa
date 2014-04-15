{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $gruppo_dipendenti = openpaini( 'ControlloUtenti', 'gruppo_dipendenti' )
	$gruppo_amministratori = openpaini( 'ControlloUtenti', 'gruppo_amministratori' )
	$oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati' )
	$oggetti_classificazione = openpaini( 'DisplayBlocks', 'oggetti_classificazione' )
	$oggetti_correlati_centro = openpaini( 'DisplayBlocks', 'oggetti_correlati_centro' )
	$oggetti_senza_label = openpaini( 'GestioneAttributi', 'oggetti_senza_label' )
	$attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_da_escludere' )
	$attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
	$attributi_a_destra = openpaini( 'GestioneAttributi', 'attributi_a_destra' )
	$attributi_allegati_atti = openpaini( 'GestioneAttributi', 'attributi_allegati_atti' )
	$classes = openpaini( 'GestioneClassi', 'classi_figlie_da_escludere' )
	$classes_figli = openpaini( 'GestioneClassi', 'classi_figlie_da_includere' )
	$classes_figli_escludi = openpaini( 'GestioneClassi', 'classi_figlie_da_escludere' )
	$classi_commentabili = openpaini( 'GestioneClassi', 'classi_commentabili' )
	$classes_parent_to_edit=array( 'file_pdf', 'news')
	$classi_da_non_commentare=array( 'news', 'comment')
	
	$classi_senza_correlazioni_inverse= openpaini( 'GestioneClassi', 'classi_senza_correlazioni_inverse' )
	$current_user = fetch( 'user', 'current_user' )
	$has_servizio='none'
	$servizio = array()
	$is_dipendente = false()
	$servizio_utente = fetch( 'content', 'related_objects',  
				hash( 'object_id', $current_user.contentobject_id, 'attribute_identifier', openpaini( 'ControlloUtenti', 'user_servizio_attribute_ID', 909 ),'all_relations', false() )) 
}

{def $classi_con_servizi = wrap_user_func('getClassAttributes', array(array('servizio')) )
	 $parent_con_servizio = false()
	 $classe_con_servizio = false()
}

{foreach $classi_con_servizi as $ccs}
	{if $ccs.identifier|eq($node.parent.class_identifier)}
		{set $parent_con_servizio = true() }
	{/if}
	{if $ccs.identifier|eq($node.class_identifier)}
		{set $classe_con_servizio = true() }
	{/if}
{/foreach}

{if $classes_parent_to_edit|contains($node.class_identifier)}
	{if $parent_con_servizio}
		{set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.parent.object.id, 
					'attribute_identifier', concat($node.parent.class_identifier, '/servizio'),'all_relations', false() )) }
		{if $servizio|gt(0)}
			{set $has_servizio='ok'}
		{/if}
	{/if}
{else}
	{if $classe_con_servizio}
		{set $servizio = fetch( 'content', 'related_objects',  hash( 'object_id', $node.object.id, 
				'attribute_identifier', concat($node.class_identifier, '/servizio'),'all_relations', false() )) }
		{if $servizio|gt(0)}
			{set $has_servizio='ok'}
		{/if}
	{/if}
{/if}	

<div class="border-box">
<div class="border-content">

 <div class="global-view-full content-view-full">
  <div class="class-{$node.object.class_identifier}">

	<h1>{$node.name|wash()}</h1>
	{* DATA e ULTIMAMODIFICA *}
	{include name = last_modified
             node = $node             
             uri = 'design:parts/openpa/last_modified.tpl'}
	
	{* EDITOR TOOLS *}
	{include name = editor_tools
             node = $node             
             uri = 'design:parts/openpa/editor_tools.tpl'}

	{* ATTRIBUTI : mostra i contenuti del nodo *}
    {include name = attributi_principali
             uri = 'design:parts/openpa/attributi_principali.tpl'
             node = $node}
	
	{* ATTRIBUTI BASE: mostra i contenuti del nodo *}
    {include name = attributi_base
             uri = 'design:parts/openpa/attributi_base.tpl'
             node = $node}
    
    {* CORRELAZIONI - OGGETTI DIRETTAMENTE CORRELATI rispetto ad attributi specifici - DisplayBlocks/oggetti_correlati_centro *}   
	{include name = related_objects_attributes_spec 
             node = $node
             title = 'Ulteriori informazioni correlate:'
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati_centro' )
             uri = 'design:parts/related_objects_attributes.tpl'}

	{* OGGETTI CORRELATI SPECIFICI - CLASSIFICAZIONE rispetto ad attributi specifici - oggetti_classificazione *}   
	{include name = related_objects_attributes 
             node = $node 
             title = "Classificazione dell'informazione"
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_classificazione' )
             uri = 'design:parts/related_objects_attributes.tpl'}

    {*OGGETTI INVERSAMENTE CORRELATI - COME PRESIDENTE DI UN ORGANO POLITICO *}
	{include name=reverse_related_objects_specific_class_and_attribute
             node=$node
             classe='organo_politico'
             attrib='presidente' 
             title="Presidente di:"
             uri='design:parts/reverse_related_objects_specific_class_and_attribute.tpl'}	

    {*OGGETTI INVERSAMENTE CORRELATI - COME VICEPRESIDENTE DI UN ORGANO POLITICO *}
    {include name=reverse_related_objects_specific_class_and_attribute
             node=$node
             classe='organo_politico'
             attrib='vicepresidente' 
             title="Vicepresidente di:"
             uri='design:parts/reverse_related_objects_specific_class_and_attribute.tpl'}	

    {*OGGETTI INVERSAMENTE CORRELATI - COME MEMBRO DI UN ORGANO POLITICO *}
    {include name=reverse_related_objects_specific_class_and_attribute
             node=$node
             classe='organo_politico'
             attrib='membri' 
             title="Membro di:"
             uri='design:parts/reverse_related_objects_specific_class_and_attribute.tpl'}	

             
    {* FIGLI *}
    {include name = filtered_children 
             node = $node.object.main_node 
             object = $node.object
             classes_figli = openpaini( 'GestioneClassi', 'classi_figlie_da_includere' )
             classes_figli_escludi = openpaini( 'GestioneClassi', 'classi_figlie_da_escludere' )
             classes_parent_to_edit = $classes_parent_to_edit
             title='Allegati'
             classi_da_non_commentare = openpaini( 'GestioneClassi', 'classi_da_non_commentare', array( 'news', 'comment' ) )
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati' )
             uri = 'design:parts/filtered_children.tpl'}

    {* GALLERIA fotografica *}   
	{def $galleries = fetch('content', 'list_count', hash( 'parent_node_id', $node.node_id,
                                                           'class_filter_type', 'include',
                                                           'class_filter_array', array('image') ) )}
	{if $galleries|gt(0)}
		{include name=galleria node=$node uri='design:node/view/line_gallery.tpl'}
	{/if}


    {* TIP A FRIEND *}
    {include name=tipafriend node=$node uri='design:parts/common/tip_a_friend.tpl'}
        
	{* VISUALIZZAZIONE E CREAZIONE DI NEWS *}
    {if $node.object.content_class.is_container}
		{include name = create_comment 
				 node = $node
                 object=$node.object
				 classes_parent_to_edit = $classes_parent_to_edit
				 classi_da_non_commentare = openpaini( 'GestioneClassi', 'classi_da_non_commentare', array( 'news', 'comment' ) )
				 uri = 'design:parts/websitetoolbar/create_news.tpl'}

    {/if}

	{* COMMENTI *}
    {if openpaini( 'GestioneClassi', 'classi_commentabili' )|contains($node.class_identifier)}
		{include name=create_comment node=$node uri='design:parts/websitetoolbar/create_comment.tpl'}
  	{/if}
	
    </div>
</div>

</div>
</div>