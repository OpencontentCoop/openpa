{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $oggetti_classificazione = array('organo_politico')
	 $oggetti_correlati_centro = array('struttura')
	 $classes_parent_to_edit=array( 'file_pdf', 'news')
	 $classi_da_non_commentare=array( 'news', 'comment')
	 $current_user = fetch( 'user', 'current_user' )
}

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
             title = 'Struttura di riferimento'
             oggetti_correlati = $oggetti_correlati_centro 
             uri = 'design:parts/related_objects_attributes.tpl'}

	{* ALLEGATI E ANNESSI DI ATTI RELAZIONATI: iter, pareri, allegati di ATTI ecc *}
	{include name = allegati_e_annessi
             node = $node 
             title = 'Relazioni'
             attributi_rilevanti = openpaini( 'GestioneAttributi', 'attributi_allegati_atti' )
             uri = 'design:parts/allegati_e_annessi.tpl'}
  

	{* OGGETTI CORRELATI rispetto ad attributi specifici - oggetti_classificazione *}   
	{include name=related_objects_attributes 
				node=$node 
				title="Organi e strutture di riferimento"
				oggetti_correlati=$oggetti_classificazione 
				uri='design:parts/related_objects_attributes.tpl'}

    {* FIGLI *}
    {include name = filtered_children 
             node = $node.object.main_node 
             object = $node.object
             classes_figli = array('politico')
             classes_figli_escludi = array()
             classes_parent_to_edit = $classes_parent_to_edit
             title='Membri'
             classi_da_non_commentare = openpaini( 'GestioneClassi', 'classi_da_non_commentare', array( 'news', 'comment' ) )
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati' )
             uri = 'design:parts/filtered_children.tpl'}

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

    {include name = filtered_children 
             node = $node.object.main_node 
             object = $node.object
             classes_figli = array('news')
             classes_figli_escludi = array()
             classes_parent_to_edit = $classes_parent_to_edit
             title='News'
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

	
    </div>
</div>

</div>
</div>
