{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}
{set-block scope=root variable=cache_ttl}0{/set-block}

{def $view = $node.data_map.view.class_content.options[$node.data_map.view.value[0]].name|downcase()}
{if is_set( $view_parameters.view )}
    {set $view = $view_parameters.view}
{/if}

<div class="border-box">
    <div class="global-view-full content-view-full">
        <div class="class-event-calendar event-calendar-calendarview">

            <div class="attribute-header">
                <h1>{$node.name|wash()}</h1>
            </div>
            
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
            
            {include uri=concat("design:calendar/",$view,".tpl") }
            
        </div>
    </div>
</div>
