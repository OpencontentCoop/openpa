{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $user_struttura_attribute_ID = openpaini( 'ControlloUtenti', concat('user_',$node.class_identifier,'_attribute_ID') )
	 $attributi_classificazione_strutture = openpaini( 'DisplayBlocks', 'attributi_classificazione_strutture' )		
	 $classes_parent_to_edit=array('file_pdf', 'news')
	 $classi_da_non_commentare=array('news', 'comment')
	 $current_user = fetch( 'user', 'current_user' )
     $attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_da_escludere' )
     $oggetti_senza_label = openpaini( 'GestioneAttributi', 'oggetti_senza_label' )
     $attributi_senza_link = openpaini( 'GestioneAttributi', 'attributi_senza_link' )
     $attributi_da_evidenziare = openpaini( 'GestioneAttributi', 'attributi_da_evidenziare' )
}

{ezscript_require( array( 'ezjsc::jquery', 'jexcerpt.js', 'excerpt.js' ) )}

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
	
	<div class="attributi-base">
	{def $style='col-odd' 
		 $attribute=''}
         
    {if $node.data_map.orario.has_content}
        {set $attribute=$node.data_map.orario }
        <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
            <div class="col-content"><div class="col-content-design">
                {attribute_view_gui attribute=$attribute}
            </div></div>
        </div>
    {/if}

{* ------------------------------- articolazioni interne ------------------------------- *}
    {include node=$node icon=true uri='design:parts/articolazioni_interne.tpl'}

{* ------------------------------- responsabile ------------------------------- *}	
	{include struttura=$node style=$style icon=true uri='design:parts/ruoli_per_struttura.tpl'}    		


{* ------------------------------- personale ------------------------------- *}
	{include struttura=$node style=$style icon=true uri='design:parts/personale_per_struttura.tpl'}	


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