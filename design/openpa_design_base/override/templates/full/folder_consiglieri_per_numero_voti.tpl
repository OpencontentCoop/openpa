{* Folder - Full view *}
{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $current_user = fetch( 'user', 'current_user' )
     $classes_parent_to_edit=array('file_pdf', 'news')
     $sezioni_per_tutti= openpaini( 'GestioneSezioni', 'sezioni_per_tutti' )
	 $style='col-odd'
}

<div class="border-box">
<div class="global-view-full content-view-full">
    <div class="class-folder">

        <h1>{attribute_view_gui attribute=$node.data_map.name}</h1>
	
        {* DATA e ULTIMAMODIFICA *}
        {*include name = last_modified
                 node = $node             
                 uri = 'design:parts/openpa/last_modified.tpl'*}
    
        {* EDITOR TOOLS *}
        {include name = editor_tools
                 node = $node             
                 uri = 'design:parts/openpa/editor_tools.tpl'}
    
        {* ATTRIBUTI : mostra i contenuti del nodo *}
        {include name = attributi_principali
                 uri = 'design:parts/openpa/attributi_principali.tpl'
                 node = $node}

        <div class="attributi-principali float-break col col-notitle">
            {if and( is_set($node.data_map.description), $node.data_map.description.has_content )}
            <div class="col-content-design">
                {attribute_view_gui attribute=$node.data_map.description}
            </div>
            {/if}
        </div>
        

        {* FIGLI *}
        {def $page_limit = 100
             $show_items=false()
             $children=array()
             $tmp_children=array()
             $children_count=''
             $classes=''
             $include_exclude=''
             $search=''}
    
        
            {set $classes =  openpaini( 'ExcludedClassesAsChild', 'FromFolder' )
                 $search = fetch( 'ezfind', 'search', hash( 'subtree_array', array( $node.node_id ),
                                                              'offset', $view_parameters.offset,
                                                              'filter', array( concat( 'meta_depth_si:', $node.depth|inc() ) ),
                                                              'sort_by', hash( 'attr_voti_si', 'desc' ),
                                                              'limit', $page_limit ) )
                 $children_count = $search['SearchCount']
                 $children = $search['SearchResult']}

        {if $children_count|gt(0)}
            {foreach $children as $child }
            {if $sezioni_per_tutti|contains($child.object.section_id)}
                {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                <div class="{$style} col col-notitle float-break">
            {else}
                <div class="square-box-gray float-break no-sezioni_per_tutti">
            {/if}
                    <div class="col-content"><div class="col-content-design">
                        {node_view_gui view='line' show_image='no' content_node=$child}
                    </div></div>
                </div>
            {/foreach}
            {include name=navigator
                     uri='design:navigator/google.tpl'
                     page_uri=$node.url_alias
                     item_count=$children_count
                     view_parameters=$view_parameters
                     item_limit=$page_limit}
        {/if}         
        
        {* TIP A FRIEND *}
        {include name=tipafriend node=$node uri='design:parts/common/tip_a_friend.tpl'}

    </div>
</div>
</div>