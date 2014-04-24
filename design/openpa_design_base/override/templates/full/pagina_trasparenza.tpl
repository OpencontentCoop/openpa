{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $style = 'col-odd'
     
     $classi_trasparenza = array( 'pagina_trasparenza' )     
     $classi_note_trasparenza = array( 'nota_trasparenza' )     
     
     $nota = fetch( 'content', 'list', hash( 'parent_node_id', $node.node_id,
                                                   'class_filter_type', 'include',
                                                   'class_filter_array', $classi_note_trasparenza,
                                                   'sort_by', array( 'published', false() ),
                                                   'limit', 1 ) )
     
     $conteggio_figli = fetch( 'content', 'list_count', hash( 'parent_node_id', $node.object.main_node_id,
                                                              'sort_by', $node.sort_array,
                                                              'class_filter_type', 'exclude',
                                                              'class_filter_array', $classi_trasparenza|merge( $classi_note_trasparenza ) ) )
     
     $conteggio_figli_pagina_trasparenza = fetch( 'content', 'list_count', hash( 'parent_node_id', $node.node_id,
                                                                                 'sort_by', $node.sort_array,
                                                                                 'class_filter_type', 'include',
                                                                                 'class_filter_array', $classi_trasparenza ) )}

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
    
            
        {* Guida al cittadino *}
        {include name = guida_al_cittadino
                 node = $node             
                 uri = 'design:parts/openpa/amminsitrazione_trasparente/guida_al_cittadino.tpl'}
                 
        {* Guida al redattore *}
        {include name = guida_al_cittadino
                 node = $node             
                 uri = 'design:parts/openpa/amminsitrazione_trasparente/guida_al_redattore.tpl'}
              
    
        {* Nota: una sola nota *}
        {if $nota|count()|gt(0)}
        <div class="block">
            {include name=edit node=$nota[0] uri='design:parts/openpa/edit_buttons.tpl'}    
            <em>{attribute_view_gui attribute=$nota[0].data_map.testo_nota}</em>
        </div>
        {/if}
        
        {* Figli *}
        <div class="attributi-base">
            
            {if $conteggio_figli_pagina_trasparenza|gt(0)}                
                {def $figli_pagina_trasparenza = fetch( 'content', 'list', hash( 'parent_node_id', $node.node_id,
                                                                                 'sort_by', $node.sort_array,
                                                                                 'class_filter_type', 'include',
                                                                                 'class_filter_array', $classi_trasparenza ) )}                
                {include uri='design:parts/openpa/amminsitrazione_trasparente/children.tpl'
                         nodes=$figli_pagina_trasparenza
                         nodes_count=$conteggio_figli_pagina_trasparenza}            
            
            {elseif $conteggio_figli|gt(0)}            
                {def $figli = fetch( 'content', 'list', hash( 'parent_node_id', $node.object.main_node_id,
                                                              'sort_by', $node.sort_array,                                                              
                                                              'class_filter_type', 'exclude',
                                                              'class_filter_array', $classi_trasparenza|merge( $classi_note_trasparenza ) ) )}            
                
                {* In base al remoteid vengono caricate le visualizzazioni tabellari o normali *}
                
                {switch match=$node.object.remote_id}
                    
                    {* Consulenti e collaboratori *}
                    {case match='b5df51b035ee30375db371af76c3d9fb'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'                                 
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class='consulenza'}
                    {/case}
                    
                    {* Incarichi amminsitrativi di vertice *}
                    {case match='efc995388bebdd304f19eef17aab7e0d'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class='dipendente'}
                    {/case}
                    
                    {* Dirigenti *}
                    {case match='9eed77856255692eca75cdb849540c23'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class='dipendente'}
                    {/case}
                    
                    {* Tassi di assenza *}
                    {case match='c46fafba5730589c0b34a5fada7f3d07'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class='tasso_assenza'}
                    {/case}
                    
                    {* Incarichi conferiti e autorizzati ai dipendenti *}
                    {case match='b7286a151f027977fa080f78817c895a'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class='incarico'}
                    {/case}
                    
                    {* Atti di concessione *}
                    {case match='90b631e882ab0f966d03aababf3d9f15'} 
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children_table.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli
                                 class=array( 'sovvenzione_contributo', 'determinazione', 'deliberazione' )}
                    {/case}
                        
                    {* visualizzazione figli default *}
                    {case}
                        {include uri='design:parts/openpa/amminsitrazione_trasparente/children.tpl'
                                 nodes=$figli
                                 nodes_count=$conteggio_figli}
                    {/case}
                {/switch}
            
            {elseif $nota|count()|eq(0)}
            {* se non c'è nemmeno la nota occorre esporre un alert *}
                <div class="warning message-warning">
                    <p>Sezione in allestimento</p>
                </div>
                
            {/if}
        </div>
	
    </div>
</div>

</div>
</div>
