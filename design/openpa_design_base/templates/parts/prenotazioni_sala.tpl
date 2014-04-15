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
                  'class_id', array( 'prenotazione_sala' ),
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

{foreach $events as $event}
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
            {set $day_events = $day_events|append($event)}
        {/if}
    {/for}
{/foreach}

<div class="oggetti-correlati">
    <div class="border-header border-box box-trans-blue box-allegati-header">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
                <h2>Calendario prenotazioni sala</h2>
        </div>
        </div></div></div>
    </div>
    <div class="border-body border-box box-violet box-allegati-content">
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">

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
            
            </div> {* col-content *}
            </div>
            <div class="col-2">
            <div class="col-content">
            
                <div id="ezagenda_calendar_program">
                <h3>{$temp_ts|datetime( custom, '%F %Y' )|upfirst()}:</h3> 
                <ul>
                {foreach $events as $event}
                    <li>
                        {def $today = false()}
                        {foreach $day_events as $day_event}
                            {if $day_event.contentobject_id|eq($event.contentobject_id)}
                                {set $today = true()}
                            {/if}
                        {/foreach}
                        
                        {include name=edit node=$event uri='design:parts/openpa/edit_buttons.tpl'}    
                        
                        {if $today}<strong>{/if}
                        {$event.object.data_map.from_time.content.timestamp|datetime(custom,"%j")} {$event.object.data_map.from_time.content.timestamp|datetime(custom,"%F")} 
                        {$event.object.data_map.from_time.content.timestamp|datetime(custom,"%H:%i")} -
                        {$event.object.data_map.to_time.content.timestamp|datetime(custom,"%H:%i")}
                        {if $today}</strong>{/if}
                        
                        {undef $today}
                    </li>    
                {/foreach}
                
                </ul>
                </div>
            
            </div>
            </div>
            </div>
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>
</div>            
