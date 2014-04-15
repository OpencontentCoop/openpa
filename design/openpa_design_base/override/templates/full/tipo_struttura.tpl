{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="border-content">

  <div class="global-view-full content-view-full">
    <div class="struttura">

		<h1>Strutture di tipo "{$node.name|wash()}"</h1>
	
        {* EDITOR TOOLS *}
        {include name = editor_tools
                 node = $node             
                 uri = 'design:parts/openpa/editor_tools.tpl'}
    
        {* ATTRIBUTI : mostra i contenuti del nodo *}
        {include name = attributi_principali
                 uri = 'design:parts/openpa/attributi_principali.tpl'
                 node = $node}

        {if $node.name|eq('Servizio')}
            {def $classes_figli = array('servizio')}
            {def $oggetti=fetch(content, list,  hash(parent_node_id, openpaini( 'Nodi', 'ServiziAttivi', 0 ), 
                                                    'sort_by',  array( 'name', true() ),
                                                    'class_filter_type', 'include', 
                                                    'class_filter_array', $classes_figli ) )} 
        
            {include name=group_of_objects
                     node=$node              
                     title='Strutture di questa tipologia:'|i18n('retecivica/view')
                     oggetti=$oggetti 
                     uri='design:parts/group_of_objects.tpl'}
        
                
        {else}
        
            {include name=reverse_related_objects 
                    node=$node 
                    title='Strutture di questa tipologia:'|i18n('retecivica/view')
                    uri='design:parts/reverse_related_objects.tpl'}
        {/if}


    </div>
</div>


</div>
</div>