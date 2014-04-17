{* variabili attese: $root_node_id *}
{def $inimenu = openpaini( 'SideMenu', 'IdentificatoriMenu', array() )
     $root_node=fetch( 'content', 'node', hash( 'node_id', $root_node_id ) )
     $left_menu_items = fetch( 'content', 'list', hash( 'parent_node_id', $root_node.node_id,
                                                        'sort_by', $root_node.sort_array,
                                                        'data_map_load', false(),
                                                        'class_filter_type', 'include',
                                                        'class_filter_array', $inimenu ) )
     $left_menu_items_count = $left_menu_items|count()
     $li_class = array()
     $li_class1 = array()
     $li_class2 = array()
     $li_class3 = array()
     $a_class = array()
     $a_class1 = array()
     $a_class2 = array()
     $a_class3 = array()}

{if $left_menu_items_count}
    <ul class="menu-list">
    {foreach $left_menu_items as $key => $item}
        {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $item.node_id )|not()}
            {set $a_class = array()
                 $li_class = cond( $key|eq(0), array("firstli"), array() )}
            
            {if $left_menu_items_count|eq( $key|inc )}
                {set $li_class = $li_class|append("lastli")}
            {/if}
            
            <li{if $li_class} class="{$li_class|implode(" ")}"{/if}>
                
                {def $sub_menu_items = fetch( 'content', 'list', hash( 'parent_node_id', $item.node_id,
                                                                       'sort_by', $item.sort_array,
                                                                       'data_map_load', false(),
                                                                       'class_filter_type', 'include',
                                                                       'class_filter_array', $inimenu ) )
                     $sub_menu_items_count = $sub_menu_items|count}
                     
                <div class="second_level_menu">                    
                    <span class="handler {if $a_class}{$a_class|implode(" ")}{/if}{if $sub_menu_items_count} activable{/if}"></span>
                    {include uri='design:menu/cached/leftmenu_item.tpl' node=$item class=$a_class}
                </div>
                    
                {if $sub_menu_items_count}
                    <ul class="submenu-list-1">
                        
                        {foreach $sub_menu_items as $subkey => $subitem}
                            {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem.node_id )|not()}   	
                                
                                {set $a_class1 = array()
                                     $li_class1 = cond( $subkey|eq(0), array("firstli"), array() )}
                                
                                {if $sub_menu_items_count|eq( $subkey|inc )}
                                    {set $li_class1 = $li_class|append("lastli")}                                
                                {/if}
                                
                                <li{if $li_class1} class="{$li_class1|implode(" ")}"{/if}>

                                    {def $sub_menu_items2 = fetch( 'content', 'list', hash( 'parent_node_id', $subitem.node_id,
                                                                                            'sort_by', $subitem.sort_array,
                                                                                            'data_map_load', false(),
                                                                                            'class_filter_type', 'include',
                                                                                            'class_filter_array', $inimenu ) )
                                         $sub_menu_items_count2 = $sub_menu_items2|count}
                                
                                    <div class="third_level_menu">                                        
                                        <span class="handler {if $a_class1}{$a_class1|implode(" ")}{/if}{if $sub_menu_items_count2} activable{/if}"></span>
                                        {include uri='design:menu/cached/leftmenu_item.tpl' node=$subitem class=$a_class1}
                                    </div>
                                        
                                        {if $sub_menu_items_count2} 
                                            <ul class="submenu-list-2">
                                                
                                                {foreach $sub_menu_items2 as $subkey2 => $subitem2}
                                                    {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem2.node_id )|not()}
                                                        
                                                        {set $a_class2 = array()
                                                             $li_class2 = cond( $subkey2|eq(0), array("firstli"), array() )}
                                                        
                                                        {if $sub_menu_items_count2|eq( $subkey|inc )}
                                                            {set $li_class2 = $li_class2|append("lastli")}
                                                        {/if}
                                                                                                            
                                                        <li{if $li_class2} class="{$li_class2|implode(" ")}"{/if}>
                                                            
                                                            {def $sub_menu_items3 = fetch( 'content', 'list', hash( 'parent_node_id', $subitem2.node_id,
                                                                                                                    'sort_by', $subitem2.sort_array,
                                                                                                                    'data_map_load', false(),
                                                                                                                    'class_filter_type', 'include',
                                                                                                                    'class_filter_array', $inimenu ) )
                                                                 $sub_menu_items_count3 = $sub_menu_items3|count}
                                                            
                                                            <div class="fourth_level_menu">
                                                                <span class="handler {if $a_class2}{$a_class2|implode(" ")}{/if}{if $sub_menu_items_count3} activable{/if}"></span>
                                                                {include uri='design:menu/cached/leftmenu_item.tpl' node=$subitem2 class=$a_class2}
                                                            </div>
                                
                                                            {if $sub_menu_items_count3}
                                                                <ul class="submenu-list-3">
                                                                    {foreach $sub_menu_items3 as $subitem2 => $subitem3}
                                                                        {if openpaini( 'SideMenu', 'NascondiNodi', array() )|contains( $subitem3.node_id )|not()}
                                                                            {set $a_class3 = array()
                                                                                 $li_class3 = cond( $subitem2|eq(0), array("firstli"), array() )}
                                                                            
                                                                            {if $sub_menu_items_count3|eq( $subkey|inc )}
                                                                                {set $li_class3 = $li_class3|append("lastli")}
                                                                            {/if}
                                                                            
                                                                            <li{if $li_class3} class="{$li_class3|implode(' ')}"{/if}>
                                                                                <div class="fifth_level_menu">											
                                                                                    {include uri='design:menu/cached/leftmenu_item.tpl' node=$subitem3 class=$a_class3}
                                                                                </div>
                                                                            </li>
                                                                        {/if}
                                                                    {/foreach}
                                                                </ul>
                                                            {/if}
                                                            {undef $sub_menu_items3 $sub_menu_items_count3}
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ul>
                                        {/if}
                                    {undef $sub_menu_items2 $sub_menu_items_count2}                                                            
                                
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
                {undef $sub_menu_items $sub_menu_items_count}        
            </li>
        {/if}
    {/foreach}
    </ul>
{/if}
{undef}