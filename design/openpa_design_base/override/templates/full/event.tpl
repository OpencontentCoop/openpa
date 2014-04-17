{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $classes_parent_to_edit=array('file_pdf', 'news')
	 $current_user = fetch( 'user', 'current_user' )}

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

    <div class="attributi-principali float-break col col-notitle">
        <div class="col-content"><div class="col-content-design">
            <div class="main-image left">
            
            {if and( is_set($node.data_map.image), $node.data_map.image.has_content )}
                {attribute_view_gui attribute=$node.data_map.image image_class='large'}
            {/if}
            </div>
                
            {if $node.data_map.abstract.has_content}
                {attribute_view_gui attribute=$node.data_map.abstract}            
            {/if}
                        
            {foreach array( 'periodo_svolgimento', 'orario_svolgimento', 'luogo_svolgimento' ) as $identifier}
            {if and( $node.data_map[$identifier].has_content, $node.data_map[$identifier].content|ne('0') )}
            <p>
                {if $identifier|ne( 'file' )}
                <strong>{$node.data_map[$identifier].contentclass_attribute_name}</strong>
                {/if}
                {attribute_view_gui attribute=$node.data_map[$identifier] show_flip=false()}
            </p>
            {/if}
            {/foreach}
            
            
        </div></div>
    </div>
    
{def $attributi_da_escludere = openpaini( 'GestioneAttributi', 'attributi_event_da_escludere' )
     $oggetti_senza_label = openpaini( 'GestioneAttributi', 'oggetti_senza_label' )
     $attributi_senza_link = openpaini( 'GestioneAttributi', 'attributi_senza_link' )}
     
    <div class="attributi-base">
        {def $style='col-odd'}
        {foreach $node.object.data_map as $attribute}
            
            {if and( $attribute.has_content, $attribute.content|ne('0') )}
            
                {if $attributi_da_escludere|contains( $attribute.contentclass_attribute_identifier )|not()}
                    
                    {if and( flip_exists( $attribute.contentobject_id ), $attribute.contentclass_attribute_identifier|eq( 'file' ) )}
                        {set $oggetti_senza_label = $oggetti_senza_label|append( $attribute.contentclass_attribute_identifier )}
                    {/if}
                    
                    {if $style|eq( 'col-even' )}{set $style = 'col-odd'}{else}{set $style = 'col-even'}{/if}
                    
                    {if $oggetti_senza_label|contains( $attribute.contentclass_attribute_identifier )|not()}
                       <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}">
                            <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                            <div class="col-content"><div class="col-content-design">
                                {if $attributi_senza_link|contains( $attribute.contentclass_attribute_identifier )}
                                    {attribute_view_gui href='nolink' attribute=$attribute}
                                {else}
                                    {attribute_view_gui attribute=$attribute}
                                {/if}
                            </div></div>
                       </div>
                    {else}
                       <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                        <div class="col-content"><div class="col-content-design">
                            {if $attributi_senza_link|contains( $attribute.contentclass_attribute_identifier )}
                                {attribute_view_gui href='nolink' attribute=$attribute show_flip=true()}
                            {else}
                                {attribute_view_gui attribute=$attribute show_flip=true()}
                            {/if}
                        </div></div>
                       </div>
                    {/if}
                {/if} 
            {else}
                {if $attribute.contentclass_attribute_identifier|eq('ezflowmedia')}
                    {if $style|eq('col-even')}{set $style='col-odd'}{else}{set $style='col-even'}{/if}
                    <div class="{$style} col float-break attribute-fullbase-{$attribute.contentclass_attribute_identifier} {if $oggetti_senza_label|contains($attribute.contentclass_attribute_identifier)}col-notitle{/if}">
                        <div class="col-content"><div class="col-content-design">
                        {attribute_view_gui attribute=$attribute}
                        </div></div>
                    </div>
                {/if}		
            {/if}
        {/foreach}
    </div>


	{* ALLEGATI E ANNESSI DI ATTI RELAZIONATI: iter, pareri, allegati di ATTI ecc *}
	{include name = allegati_e_annessi
             node = $node 
             title = 'Allegati'
             attributi_rilevanti = openpaini( 'GestioneAttributi', 'attributi_allegati_atti' )
             uri = 'design:parts/allegati_e_annessi.tpl'}


    {* FIGLI *}
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
             classes_figli = openpaini( 'GestioneClassi', 'classi_edizioni_figli' )
             classes_figli_escludi = openpaini( 'GestioneClassi', 'classi_figlie_da_escludere' )
             classes_parent_to_edit = $classes_parent_to_edit
             title = 'Edizioni'            
             classi_da_non_commentare = openpaini( 'GestioneClassi', 'classi_da_non_commentare', array( 'news', 'comment' ) )
             oggetti_correlati = openpaini( 'DisplayBlocks', 'oggetti_correlati' )
             uri = 'design:parts/filtered_children.tpl'}

	{* GALLERIA fotografica *}   
	{def $galleries = fetch('content', 'list_count', hash( 'parent_node_id', $node.node_id,
                                                           'class_filter_type', 'include',
                                                           'class_filter_array', array('image') ) )}
	{if $galleries|gt(0)}
        <div class="block">
		{include name=galleria node=$node uri='design:node/view/line_gallery.tpl'}
        </div>
	{/if}
    
    {def $iniziativa = false()}
    {if $node.data_map.iniziativa.has_content}        
        {set $iniziativa = fetch( 'content', 'node', hash( 'node_id', $node.data_map.iniziativa.content.relation_list[0].node_id ) )}
        {def $calendarData = fetch( openpa, calendario_eventi, hash( 'calendar', $node.parent, 'params', hash( 'interval', 'P1Y',
                                                                                                        'filter', array( concat( '-meta_id_si:', $node.contentobject_id ) ),
                                                                                                        'Manifestazione', concat( '"', $iniziativa.name, '"' ) ) ) )}
    {else}
        {def $calendarData = fetch( openpa, calendario_eventi, hash( 'calendar', $node.parent, 'params', hash( 'interval', 'P1Y',
                                                                                                        'filter', array( concat( '-meta_id_si:', $node.contentobject_id ) ),
                                                                                                        'Manifestazione', concat( '"', $node.name, '"' ) ) ) )}
    {/if}
    {*debug-log var=$data.parameters msg='Parametri eventi manifestazione'}
    {debug-log var=$data.fetch_parameters msg='Fetch eventi manifestazione'*}
    
    {if $calendarData.search_count|gt(0)}
        <div class="oggetti-correlati">
            <div class="border-header border-box box-trans-blue box-allegati-header">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc">
                <div class="border-content">
                    <h2>
                        {if and( $iniziativa, $iniziativa.contentobject_id|ne( $node.contentobject_id ) )}
                        Questo evento fa parte della manifestazione <a href={$iniziativa.url_alias|ezurl()} title="Vedi il dettaglio di {$iniziativa.name|wash()}">{$iniziativa.name|wash()}</a> <br />
                        {/if}
                        <small>I prossimi appuntamenti in programma:</small>
                    </h2>
                </div>
                </div></div></div>
            </div>
            <div class="border-body border-box box-violet box-allegati-content">
                <div class="border-ml"><div class="border-mr"><div class="border-mc">
                <div class="border-content">
                    <div class="calendar-day-program float-break">
                        <div class="block">
                        {foreach $calendarData.events as $event sequence array( 'left', 'right' ) as $_style}
                        <div class="calendar-event {$_style}">
                            {include name="calendar-item" uri="design:calendar/program_item.tpl" event=$event}
                        </div>
                        {/foreach}
                        </div> 
                    </div> 
                
                    {*
                    {foreach $calendarData.day_by_day as $calendarDay}    
                        {if $calendarDay.count|gt(0)}
                            
                            <div class="calendar-day-program float-break">
                                
                                <h2>
                                    {if $calendarDay.is_today}Oggi
                                    {elseif $calendarDay.is_tomorrow}Domani
                                    {elseif and( $calendarDay.is_in_week, $calendarDay.is_in_month )}{$calendarDay.start|datetime( 'custom', '%l' )}
                                    {else}{$calendarDay.start|l10n( 'date' )}
                                    {/if}
                                </h2>
                        
                                <div class="block">
                                {foreach $calendarDay.events as $event sequence array( 'left', 'right' ) as $_style}
                                <div class="calendar-event {$_style}">
                                    {include name="calendar-item" uri="design:calendar/program_item.tpl" event=$event}
                                </div>
                                {/foreach}
                                </div>
                            
                            </div>
                        {/if}
                    {/foreach}
                    *}
                </div>
                </div></div></div>
                <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
            </div>
        </div>
    {elseif and( $iniziativa, $iniziativa.contentobject_id|ne( $node.contentobject_id ) )}
        <div class="attributi-principali float-break col col-notitle">
            <div class="col-content"><div class="col-content-design">
            <p>Questo evento fa parte della manifestazione <a href={$iniziativa.url_alias|ezurl()} title="Vedi il dettaglio di {$iniziativa.name|wash()}">{$iniziativa.name|wash()}</a></p>
            </div></div>
        </div>
    {/if}


    {* TIP A FRIEND *}
    {include name=tipafriend node=$node uri='design:parts/common/tip_a_friend.tpl'}


    </div>
</div>

</div>
</div>
