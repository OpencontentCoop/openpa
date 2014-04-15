{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}
{def $ServiziIndipendenti = openpaini( 'Nodi', 'ServiziIndipendenti' )
     $Aree = openpaini( 'Nodi', 'Aree' )}
     
<div class="border-box">
<div class="global-view-full content-view-full mappa_organizzazione">
    <div class="class-folder">

    <h1>{$node.name|wash()}</h1>

    <div class="attributi-principali float-break col">
        <div class="col-content-design float-break">
            {if $node.data_map.image.has_content}
                <div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='medium'}</div>
            {/if}
            {if $node|has_abstract()}
                {$node|abstract()}
            {/if}
        </div>
    </div>

	{def $children=''
         $servizi_area=''}

    
		{set $children=fetch('content', 'list',hash('parent_node_id', $ServiziIndipendenti, 
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array('servizio','area'), 
                                                    'sort_by', array('priority', true()) ))}
        {if $children|count()|gt(0)}
        {foreach $children as $child }
        	<div class="servizio-top">
                <h2>{*$child.object.class_identifier*}{node_view_gui view='toolline' content_node=$child.object.main_node}</h2>
                {include node=$child title=false() icon=false() uri='design:parts/articolazioni_interne.tpl'}
            </div>
        {/foreach}
        {/if}
    


    
		{set $children=fetch('content', 'list',hash('parent_node_id', $Aree,
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', array('servizio','area'), 
                                                    'sort_by', array('priority', true())  ))}

		{if $children|count()|gt(0)}
        
		{foreach $children as $child }
        <div class="servizio-top">
            <h2>{node_view_gui view='toolline' content_node=$child}</h2>
            
            {include node=$child title=false() icon=false() no_servizi=true uri='design:parts/articolazioni_interne.tpl'}
            
            {set $servizi_area=fetch( 'content', 'reverse_related_objects', hash( 'object_id', $child.contentobject_id,
                                                                                'attribute_identifier', 'servizio/area',
                                                                                'sort_by', array('name', true())))}
            
            {if count($servizi_area)|gt(0)}        
            <ul class="servizio_area">
                {foreach $servizi_area as $servizio}
                    {if $servizio.id|ne($child.contentobject_id)}
                        {if openpaini( 'GestioneSezioni', 'sezioni_per_tutti', array() )|contains($servizio.section_id)}
                            <li>
                                {*$servizio.main_node.object.class_identifier*}
                                {node_view_gui view='toolline' content_node=$servizio.main_node}
                                {include node=$servizio.main_node title=false() icon=false() uri='design:parts/articolazioni_interne.tpl'}
                            </li>
                        {/if}
                    {/if}
                {/foreach}
            </ul>    
            {/if}

        </div>
        {/foreach}		
        
        {/if}
    

	</div>
</div>
</div>
