{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
	 $attributi_a_destra = openpaini( 'GestioneAttributi', 'attributi_a_destra' )
	 $classes_parent_to_edit = openpaini( 'GestioneClassi', 'classi_figlie_da_editare' )}

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
    {def $attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_da_escludere' )}
    
    {if and( is_set( $node.data_map.short_description ), $node.data_map.short_description.has_content )}
        {set $attributi_da_escludere = $attributi_da_escludere|append( 'short_description' )}
    {/if}
    
    <div class="attributi-base">
        {def $style='col-even'}
        {foreach $node.object.contentobject_attributes as $attribute}            
            {if and( $attribute.has_content, $attribute.content|ne('0') )}            
                {if $attributi_da_escludere|contains( $attribute.contentclass_attribute_identifier )|not()}
                    <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                    <div class="col-content"><div class="col-content-design">
                        {if $attribute.contentclass_attribute_identifier|eq( 'author' )}
                            <strong>{attribute_view_gui attribute=$attribute show_flip=true()}</strong>
                        {else}
                            {attribute_view_gui attribute=$attribute show_flip=true()}
                        {/if}
                    </div></div>
                    </div>                    
                {/if} 
            {/if}
        {/foreach}
    </div>
    
	{* CORRELAZIONI - OGGETTI DIRETTAMENTE CORRELATI rispetto ad attributi specifici - DisplayBlocks/oggetti_correlati_centro *}   
	{include name = related_objects_attributes_spec 
             node = $node
             title = 'Informazioni correlate:'
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati_centro' )
             uri = 'design:parts/related_objects_attributes.tpl'}


    {* FIGLI: ALLEGATI *}
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
             

    {*OGGETTI INVERSAMENTE CORRELATI*}
    {include name = reverse_related_objects 
             node = $node 
             title = 'Riferimenti:'
             uri = 'design:parts/reverse_related_objects.tpl'}

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
