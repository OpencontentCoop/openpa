{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js', 'ui-datepicker-it.js' ) )}
{ezcss_require( array('datepicker.css') )}
<script type="text/javascript">
{literal}
$(function() {	
    $( ".calendar_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1
    });    
});
{/literal}
</script>

{def $calendarData = fetch( openpa, calendario_eventi, hash( 'calendar', $node,
                                                             'params', $view_parameters|merge( hash( 'interval', 'P1M', 'view', 'calendar' ) ) ) )}

{debug-log var=$calendarData.fetch_parameters msg='Fecth eventi'}

{def $curr_ts = currentdate()
     $curr_today = $calendarData.parameters.current_day
     $curr_year = $calendarData.parameters.current_year
     $curr_month = $calendarData.parameters.current_month     
     $temp_month = $calendarData.parameters.month
     $temp_year = $calendarData.parameters.year
     $temp_today = $calendarData.parameters.day
     $days = $calendarData.parameters.days_of_month
     $span1 = $calendarData.parameters.start_weekday
     $span2 = sub( 7, $calendarData.parameters.end_weekday )
     $dayofweek = 0
     $counter = 1
     $col_counter = 1
     $css_col_class = ''
     $col_end = 0}

<form class="calendar-tools" method='GET' action={concat('openpa/calendar/', $node.node_id)|ezurl}>
<input type='hidden' name="UrlAlias" value="{$node.url_alias}" />
<input type='hidden' name="View" value="calendar" />
<div class="calendar-tools">            
    <input class="query" placeholder="Cerca testo" type="text" name="Query" value="{$calendarData.parameters.query}" />
    <input class="calendar_picker" placeholder="gg-mm-yyyy" type="text" name="SearchDate" title="Seleziona data" value="{$calendarData.parameters.picker_date}" />
    
    {foreach $calendarData.search_facets as $facetFieldName => $facets}
        {if count($facets)|gt(0)}
        <select name="{$facetFieldName}">
            <option value="">{$facetFieldName}</option>
            {foreach $facets as $styleAndName}                
                <option value="{$styleAndName.value|wash()}"{if $calendarData.parameters[$facetFieldName]|eq($styleAndName.value)} selected="selected"{/if}>{if $styleAndName.indent}&nbsp;&nbsp;&nbsp;{/if}{$styleAndName.name|wash()}</option>
            {/foreach}            
        </select>
        {/if}
    {/foreach}
    <input class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
    <button class="button imagebutton" type="submit" name="TodayButton" title="Azzera la ricerca">
        <img src={'images/icons/close_icon.png'|ezdesign()} title="Azzera la ricerca" />
    </button>
</div>

<table summary="Calendario degli eventi" class="smart_calendar">
<thead>
<tr colspan="7">
    <th class="month_name last_col float-break">
        <p>
            <input type="submit" name="PrevMonthCalendarButton" class="button" value="&laquo;" />
            {$calendarData.parameters.timestamp|datetime( custom, '%F' )|upfirst()}&nbsp;{$temp_year}
            <input type="submit" name="NextMonthCalendarButton" class="button" value="&raquo;" />
            
            {if $calendarData.search_count|eq(1)}
                <small>Trovato 1 evento nel periodo {$calendarData.parameters.search_from_timestamp|l10n( 'shortdate' )} - {$calendarData.parameters.search_to_timestamp|l10n( 'shortdate' )} </small>
            {elseif $calendarData.search_count|gt(1)}
                <small>Trovati {$calendarData.search_count} eventi nel periodo {$calendarData.parameters.search_from_timestamp|l10n( 'shortdate' )} - {$calendarData.parameters.search_to_timestamp|l10n( 'shortdate' )} </small>
            {/if}
            
        </p>
        <div class="view-buttons">
            <input type="submit" name="ViewCalendarButton" class="defaultbutton" value="Calendario" />
            <input type="submit" name="ViewProgramButton" class="button" value="Lista" />
        </div>
    </th>
</tr>
<tr>
    <th class="first_col"><h2>{"Mon"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th><h2>{"Tue"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th><h2>{"Wed"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th><h2>{"Thu"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th><h2>{"Fri"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th><h2>{"Sat"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
    <th class="last_col"><h2>{"Sun"|i18n("design/ezwebin/full/event_view_calendar")}</h2></th>
</tr>
</thead>
<tbody>

{while le( $counter, $days )}
    {set $dayofweek = makedate( $temp_month, $counter, $temp_year )|datetime( custom, '%w' )
         $css_col_class = ''
         $col_end = or( eq( $dayofweek, 0 ), eq( $counter, $days ) )}
    {if or( eq( $counter, 1 ), eq( $dayofweek, 1 ) )}
        <tr class="days{if eq( $counter, 1 )} first_row{elseif lt( $days|sub( $counter ), 7 )} last_row{/if}">
        {set $css_col_class=' first_col'}
    {elseif and( $col_end, not( and( eq( $counter, $days ), $span2|gt( 0 ), $span2|ne( 7 ) ) ) )}
        {set $css_col_class=' last_col'}
    {/if}
    {if and( $span1|gt( 1 ), eq( $counter, 1 ) )}
        {set $col_counter=1 $css_col_class=''}
        {while ne( $col_counter, $span1 )}
            <td class="not-in-current-month {$css_col_class}">&nbsp;</td>
            {set $col_counter=inc( $col_counter )}
        {/while}
    {elseif and( eq($span1, 0 ), eq( $counter, 1 ) )}
        {set $col_counter=1 $css_col_class=''}
        {while le( $col_counter, 6 )}
            <td class="not-in-current-month {$css_col_class}">&nbsp;</td>
            {set $col_counter=inc( $col_counter )}
        {/while}
    {/if}    
    
    <td class="{if eq($counter, $temp_today)}ezagenda_selected {/if}{if and(eq($counter, $curr_today), eq($curr_month, $temp_month))}ezagenda_current {/if}{$css_col_class}">
        <h3>{$counter}</h3>
        {def $day_id = concat( $temp_year, '-', $temp_month, '-', $counter )}
        {if is_set( $calendarData.day_by_day[$day_id] )}
            {if $calendarData.day_by_day[$day_id].count|gt(0)}            
                <ul>
                {foreach $calendarData.day_by_day[$day_id].events as $event max 4 sequence array( 'light', 'dark' ) as $style}
                    <li class="{$style}"><a href={$event.main_url_alias|ezurl()} title="{$event.name|wash()}">{$event.name|shorten('19')|wash()}</a></li>
                {/foreach}
                </ul>
                {if $calendarData.day_by_day[$day_id].count|gt(4)}
                    {def $altri = $calendarData.day_by_day[$day_id].count|sub(4)
                         $title = ''}
                    {foreach $calendarData.day_by_day[$day_id].events as $event offset 4}
                        {set $title = concat( $title, $event.name|wash(), ', ' )}
                    {/foreach}
                    <p>
                        <a title="{$title}" href={concat( $node.url_alias, '/(view)/program', $calendarData.day_by_day[$day_id].uri_suffix, '#day-', $calendarData.day_by_day[$day_id].identifier )|ezurl()}>
                        {if $altri|eq(1)}...e un altro evento{else}...e altri {$altri} eventi{/if}
                        </a>
                    </p>
                    {undef $altri $title}
                {/if}                
            {/if}
        {/if}
        {undef $day_id}
    </td>
    
    {if and( eq( $counter, $days ), $span2|gt( 0 ), $span2|ne(7))}
        {set $col_counter = 1}
        {while le( $col_counter, $span2 )}
            {set $css_col_class = ''}
            {if eq( $col_counter, $span2 )}
                {set $css_col_class = concat($css_col_class,' last_col')}
            {/if}
            <td class="not-in-current-month {$css_col_class}">&nbsp;</td>
            {set $col_counter = inc( $col_counter )}
        {/while}
    {/if}
    {if $col_end}
        </tr>
    {/if}
    {set $counter = inc( $counter )}
{/while}

</tbody>
</table>
</form>