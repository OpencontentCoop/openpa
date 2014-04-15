{* Event Calendar - Full Calendar view *}
{def

    $event_node    = $node
    $event_node_id = $event_node.node_id

    $curr_ts = currentdate()
    $curr_today = $curr_ts|datetime( custom, '%j')
    $curr_year = $curr_ts|datetime( custom, '%Y')
    $curr_month = $curr_ts|datetime( custom, '%n')

    $temp_ts = cond( and(ne($view_parameters.month, ''), ne($view_parameters.year, '')), makedate($view_parameters.month, cond(ne($view_parameters.day, ''),$view_parameters.day, eq($curr_month, $view_parameters.month), $curr_today, 1 ), $view_parameters.year), currentdate() )

    $temp_month = $temp_ts|datetime( custom, '%n')
    $temp_year = $temp_ts|datetime( custom, '%Y')
    $temp_today = $temp_ts|datetime( custom, '%j')

    $days = $temp_ts|datetime( custom, '%t')

    $first_ts = makedate($temp_month, 1, $temp_year)
    $dayone = $first_ts|datetime( custom, '%w' )

    $last_ts = makedate($temp_month, $days, $temp_year)
    $daylast = $last_ts|datetime( custom, '%w' )

    $span1 = $dayone
    $span2 = sub( 7, $daylast )

    $dayofweek = 0

    $day_array = " "
    $loop_dayone = 1
    $loop_daylast = 1
    $day_events = array()
    $loop_count = 0
    }


{if ne($temp_month, 12)}
    {set $last_ts=makedate($temp_month|sum( 1 ), 1, $temp_year)}
{else}
    {set $last_ts=makedate(1, 1, $temp_year|sum(1))}
{/if}

{def $ezfind_month_first = $first_ts|sum(1)|datetime( 'custom', '%Y-%m-%dT%H:%i:%sZ' )
     $ezfind_month_last = $last_ts|sub(1)|datetime( 'custom', '%Y-%m-%dT%H:%i:%sZ' )
     $subtree_array = array( $event_node_id )}
{if and( is_set( $node.data_map.subtree_array ), $node.data_map.subtree_array.has_content )}
    {set $subtree_array = array()}
    {foreach $node.data_map.subtree_array.content.relation_list as $item}
        {set $subtree_array = $subtree_array|append($item.node_id)}
    {/foreach}
{/if}


{def $hash = hash('subtree_array', $subtree_array,
                  'limit', 100,
                  'sort_by', hash( 'attr_from_time_dt', 'desc' ),
                  'class_id', array( 'event' ),
                  'filter', array(
                    'or',
                        concat( 'attr_from_time_dt:[', $ezfind_month_first, ' TO ', $ezfind_month_last, ']' ),
                        concat( 'attr_to_time_dt:[', $ezfind_month_first, ' TO ', $ezfind_month_last, ']' ),
                        array( 'and',
                            concat( 'attr_from_time_dt:[ * TO ', $ezfind_month_first, ']' ),
                            concat( 'attr_to_time_dt:[', $ezfind_month_last, ' TO * ]' )
                        )
                    )
                 )
     $search = fetch( ezfind, search, $hash )
     $events = $search['SearchResult']
     $events_count  = $search['SearchCount']

     $url_reload=concat( $event_node.url_alias, "/(day)/", $temp_today, "/(month)/", $temp_month, "/(year)/", $temp_year, "/offset/2")
     $url_back=concat( $event_node.url_alias,  "/(month)/", sub($temp_month, 1), "/(year)/", $temp_year)
     $url_forward=concat( $event_node.url_alias, "/(month)/", sum($temp_month, 1), "/(year)/", $temp_year)
}

{if eq($temp_month, 1)}
    {set $url_back=concat( $event_node.url_alias,"/(month)/", "12", "/(year)/", sub($temp_year, 1))}
{elseif eq($temp_month, 12)}
    {set $url_forward=concat( $event_node.url_alias,"/(month)/", "1", "/(year)/", sum($temp_year, 1))}
{/if}

{def $manifestazioni=array()
     $eventi=array()
     $no_events=hash('Manifestazioni','Nessuna manifestazione prevista per oggi','Eventi','Nessun evento previsto per oggi')}

{foreach $events as $event}
  
  {if or(  or(
                $event.data_map.tipologia.content|downcase|eq('e'),
                $event.data_map.tipologia.has_content|not()
           ),
           or(
                $event.data_map.tipo_evento.content|downcase|eq('e'),
                $event.data_map.tipo_evento.has_content|not()
           ),           
           and( $event.data_map.tipo_evento2.has_content,
                $event.data_map.tipo_evento2.content.relation_list.0.contentobject_id|eq( opepaini( 'OggettiClassificazioni', 'TipoEventoEvento', 499 ) )
               )
         )}
    {if eq($temp_month|int(), $event.data_map.from_time.content.month|int())}
        {set $loop_dayone = $event.data_map.from_time.content.day}
    {else}
        {set $loop_dayone = 1}
    {/if}
    {if $event.data_map.to_time.content.is_valid}
       {if eq($temp_month|int(), $event.data_map.to_time.content.month|int())}
            {set $loop_daylast = $event.data_map.to_time.content.day}
        {else}
            {set $loop_daylast = $days}
        {/if}
    {else}
         {set $loop_daylast = $loop_dayone}
    {/if}
    {for $loop_dayone|int() to $loop_daylast|int() as $counter}
        {set $day_array = concat($day_array, $counter, ', ')}
        {if eq($counter,$temp_today)}
            {set $eventi = $eventi|append($event)}
        {/if}
    {/for}
    {set $day_events=$day_events|merge(hash('Eventi', array_reverse($eventi) ))}
  {else}
    {if eq($temp_month|int(), $event.data_map.from_time.content.month|int())}
        {set $loop_dayone = $event.data_map.from_time.content.day}
    {else}
        {set $loop_dayone = 1}
    {/if}
    {if $event.data_map.to_time.content.is_valid}
       {if eq($temp_month|int(), $event.data_map.to_time.content.month|int())}
            {set $loop_daylast = $event.data_map.to_time.content.day}
        {else}
            {set $loop_daylast = $days}
        {/if}
    {else}
         {set $loop_daylast = $loop_dayone}
    {/if}
    {for $loop_dayone|int() to $loop_daylast|int() as $counter}
        {set $day_array = concat($day_array, $counter, ', ')}
        {if eq($counter,$temp_today)}
            {set $manifestazioni = $manifestazioni|append($event)}
        {/if}
    {/for}
    {set $day_events=$day_events|merge(hash('Manifestazioni', array_reverse($manifestazioni) ))}
  {/if}
{/foreach}

<div class="border-box">

<div class="content-view-full">
 <div class="class-event-calendar event-calendar-calendarview">

<div class="attribute-header">
    <h1>{$event_node.name|wash()}</h1>
</div>

{* EDITOR TOOLS *}
{include name = editor_tools
         node = $node             
         uri = 'design:parts/openpa/editor_tools.tpl'}

<div class="columns-two">
<div class="col-1">
<div class="col-content">


<div id="ezagenda_calendar_container" class="block">



<table summary="Calendario degli eventi">
<thead>
<tr class="calendar_heading">
    <th class="calendar_heading_prev first_col"><a href={$url_back|ezurl} title=" Previous month ">&#8249;&#8249;</a></th>
    <th class="calendar_heading_date" colspan="5">{$temp_ts|datetime( custom, '%F' )|upfirst()}&nbsp;{$temp_year}</th>
    <th class="calendar_heading_next last_col"><a href={$url_forward|ezurl} title=" Next Month ">&#8250;&#8250;</a></th>
</tr>
<tr class="calendar_heading_days">
    <th class="first_col">{"Mon"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th>{"Tue"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th>{"Wed"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th>{"Thu"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th>{"Fri"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th>{"Sat"|i18n("design/ezwebin/full/event_view_calendar")}</th>
    <th class="last_col">{"Sun"|i18n("design/ezwebin/full/event_view_calendar")}</th>
</tr>
</thead>
<tbody>

{def $counter=1 $col_counter=1 $css_col_class='' $col_end=0}
{while le( $counter, $days )}
    {set $dayofweek     = makedate( $temp_month, $counter, $temp_year )|datetime( custom, '%w' )
         $css_col_class = ''
         $col_end       = or( eq( $dayofweek, 0 ), eq( $counter, $days ) )}
    {if or( eq( $counter, 1 ), eq( $dayofweek, 1 ) )}
        <tr class="days{if eq( $counter, 1 )} first_row{elseif lt( $days|sub( $counter ), 7 )} last_row{/if}">
        {set $css_col_class=' first_col'}
    {elseif and( $col_end, not( and( eq( $counter, $days ), $span2|gt( 0 ), $span2|ne( 7 ) ) ) )}
        {set $css_col_class=' last_col'}
    {/if}
    {if and( $span1|gt( 1 ), eq( $counter, 1 ) )}
        {set $col_counter=1 $css_col_class=''}
        {while ne( $col_counter, $span1 )}
            <td>&nbsp;</td>
            {set $col_counter=inc( $col_counter )}
        {/while}
    {elseif and( eq($span1, 0 ), eq( $counter, 1 ) )}
        {set $col_counter=1 $css_col_class=''}
        {while le( $col_counter, 6 )}
            <td>&nbsp;</td>
            {set $col_counter=inc( $col_counter )}
        {/while}
    {/if}
    <td class="{if eq($counter, $temp_today)}ezagenda_selected{/if} {if and(eq($counter, $curr_today), eq($curr_month, $temp_month))}ezagenda_current{/if}{$css_col_class}">
    {if $day_array|contains(concat(' ', $counter, ',')) }
        <a href={concat( $event_node.url_alias, "/(day)/", $counter, "/(month)/", $temp_month, "/(year)/", $temp_year)|ezurl}>{$counter}</a>
    {else}
        {$counter}
    {/if}
    </td>
    {if and( eq( $counter, $days ), $span2|gt( 0 ), $span2|ne(7))}
        {set $col_counter=1}
        {while le( $col_counter, $span2 )}
            {set $css_col_class=''}
            {if eq( $col_counter, $span2 )}
                {set $css_col_class=' last_col'}
            {/if}
            <td class="{$css_col_class}">&nbsp;</td>
            {set $col_counter=inc( $col_counter )}
        {/while}
    {/if}
    {if $col_end}
        </tr>
    {/if}
    {set $counter=inc( $counter )}
{/while}
</tbody>
</table>

</div>



<div id="ezagenda_calendar_today">
{*<div id="ezagenda_calendar_program">*}

{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js') )}

<script type="text/javascript">
{literal}
$(function() {
        $('.block-lista_tab .ui-tabs-nav li a').each(function(index) {
                $(this).attr( 'href', '#'+$('span', this).attr('class') );
        });
        $("#events-tabs").tabs({ 
                tabTemplate: '<![CDATA[<li><a class="no-js-hide" href="#{href}"><span>#{label}</span></a><a class="no-js-show"></a></li>]]>'
                });
});
{/literal}
</script>

<div class="block-type-lista block-iosono block-lista_tab">
<div class="ui-tabs">

{def $event_types = array('Eventi', 'Manifestazioni')}
                <div class="border-box box-trans-blue box-tabs-header tabs">
                <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                <div class="border-ml"><div class="border-mr"><div class="border-mc">
                <div class="border-content" id="events-tabs">
                        <ul class="ui-tabs-nav">
                            {foreach $event_types as $index => $event_type}
                                <li class="{if $index|eq(0)}ui-state-active{else}ui-state-default{/if}">
                                    <a href="#" title="{$event_type|wash()}"><span class="{$event_type|slugize()}">{$event_type} del {$temp_today}/{$temp_month}</span></a> 
                                </li>
                            {/foreach}
                        </ul>
                </div>
                </div></div></div>
                </div>

		<div class="tabs-panels">
			{foreach $event_types as $index => $event_type}
                        <h3 class="hide no-js-hide">{$event_type}</h3>
                        <div id="{$event_type|slugize()}" class="{if $index|gt(0)}no-js-hide {/if}ui-tabs-hide">

                                <div class="border-box box-violet box-tabs-panel">
                                <div class="border-ml"><div class="border-mr"><div class="border-mc">
                                <div class="border-content">
				{if $day_events[$event_type]|count()|gt(0)}
				{foreach $day_events[$event_type] as $day_event}
    
				    <div class="ezagenda_day_event{if gt($curr_ts , $day_event.object.data_map.to_time.content.timestamp)} ezagenda_event_old{/if}">
				    <h4><a href={$day_event.url_alias|ezurl}>{$day_event.name|wash}</a></h4>
				    <span class="ezagenda_date">

				    {$day_event.object.data_map.from_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
				    {if and($day_event.object.data_map.to_time.has_content,  ne( $day_event.object.data_map.to_time.content.timestamp|datetime(custom,"%j %M"),
				            $day_event.object.data_map.from_time.content.timestamp|datetime(custom,"%j %M") ))}
					       - {$day_event.object.data_map.to_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
				    {/if}
				    </span>
	
					{if $day_event.object.data_map.materia.has_content}
					<span class="ezagenda_keyword">[{attribute_view_gui attribute=$day_event.object.data_map.materia}]</span>
					{/if}
    
                    {if $day_event.object.data_map.abstract.has_content}
                        <div class="attribute-short">{attribute_view_gui attribute=$day_event.object.data_map.abstract}</div>
					{/if}

				    </div>
				{/foreach}
				{else}
					<h3>{$no_events[$event_type]}</h3>
				{/if}
                        	</div>
	                        </div></div></div>
        	                </div>
			</div>
			{/foreach}
		</div>
		</div>
		</div>

	</div>
		<div class="border-box box-violet-gray box-tabs-footer tab-link">
                      <div class="border-ml"><div class="border-mr"><div class="border-mc">
                          <div class="border-content">
                          </div>
                      </div></div></div>
                      <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>


</div> {* col-content *}
</div>
<div class="col-2">
<div class="col-content">

<div id="ezagenda_calendar_program">
{*<div id="ezagenda_calendar_today">*}


<h3>Programma del mese di {$temp_ts|datetime( custom, '%F %Y' )|upfirst()}:</h3> 
{foreach $events as $event}

    <div class="ezagenda_month_event float-break {if gt($curr_ts , $event.object.data_map.to_time.content.timestamp)}ezagenda_event_old{/if}">
    
    <div class="ezagenda_month_label">
        <span class="ezagenda_month_label_date">{$event.object.data_map.from_time.content.timestamp|datetime(custom,"%j")}</span>
        {$event.object.data_map.from_time.content.timestamp|datetime(custom,"%M")|extract_left( 3 )}
    	{if and($event.object.data_map.to_time.has_content,  ne( $event.object.data_map.to_time.content.timestamp|datetime(custom,"%j %M"),
		$event.object.data_map.from_time.content.timestamp|datetime(custom,"%j %M") ))}
        	<span class="ezagenda_month_label_date"> - </span>
	        <span class="ezagenda_month_label_date">{$event.object.data_map.to_time.content.timestamp|datetime(custom,"%j")}</span>
        	{$event.object.data_map.to_time.content.timestamp|datetime(custom,"%M")|extract_left( 3 )}
    	{/if}
    </div>
    <div class="ezagenda_month_info">
    <h4><a href={$event.url_alias|ezurl}>{$event.name|wash}</a></h4>
    {if $event.object.data_map.materia.has_content}
    <span class="ezagenda_keyword">[
    {attribute_view_gui attribute=$event.object.data_map.materia}
    ]</span>
    {/if}
    {if $event.object.data_map.abstract.has_content}
        <div class="attribute-short">{attribute_view_gui attribute=$event.object.data_map.abstract}</div>   
    {/if}
    {if $event.object.data_map.luogo_svolgimento.has_content}
	<div class="attribute-short"><strong>Dove: </strong>{attribute_view_gui attribute=$event.object.data_map.luogo_svolgimento}</div>
    {/if}

    {if $event.object.data_map.periodo_svolgimento.has_content}
	<div class="attribute-short"><strong>Quando: </strong>{attribute_view_gui attribute=$event.object.data_map.periodo_svolgimento}</div>
    {/if}

    </div>

    </div>

{/foreach}

</div>


</div>
</div>
</div>

{undef}
</div>
</div>

</div>
