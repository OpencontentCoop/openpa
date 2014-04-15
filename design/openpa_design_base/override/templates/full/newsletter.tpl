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
    {include name = attributi_base
             uri = 'design:parts/openpa/attributi_base.tpl'
             node = $node}
    
    {def $children = array()
         $classes = openpaini( 'ExcludedClassesAsChild', 'FromFolder' )
         $children_count = ''}
        
    {set $children_count=fetch_alias( 'children_count', hash( 'parent_node_id', $node.node_id,
                                                              'class_filter_type', 'exclude',
                                                              'class_filter_array', $classes ) )}
    
    <div class="content-view-children">
        {def $style='col-odd'}
        {if $children_count}
            {foreach fetch_alias( 'children', hash( 'parent_node_id', $node.node_id,
                                                    'class_filter_type', 'exclude',
                                                    'class_filter_array', $classes,
                                                    'sort_by', $node.sort_array ) ) as $child }
               
            {if $child.class_identifier|eq( 'folder' )}
                
                <div class="col float-break col-notitle">
                <div class="col-content"><div class="col-content-design">
                <h2><strong>{$child.name|wash()}</strong></h2>
                </div></div>
                </div>
                
                {foreach $child.children as $subchild}
                    <div class="col float-break col-notitle">
                    <div class="col-content"><div class="col-content-design">
                        {node_view_gui view='line' content_node=$subchild}
                    </div></div>
                    </div>                    
                {/foreach}    
            {else}
                <div class="col float-break col-notitle">
                <div class="col-content"><div class="col-content-design">
                    {node_view_gui view='line' content_node=$child}
                </div></div>
                </div>
            
            {/if}
            {/foreach}
        {/if}
    </div>
    
    {* TIP A FRIEND *}
    {include name=tipafriend node=$node uri='design:parts/common/tip_a_friend.tpl'}    
	
    </div>
</div>

</div>
</div>
