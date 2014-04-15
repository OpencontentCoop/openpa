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

{def $currentInterval = 'P1M'
     $calendarData = fetch( openpa, calendario_eventi, hash( 'calendar', $node,
                                                             'params', hash( 'interval', $currentInterval, 'view', 'program' )|merge( $view_parameters ) ) )}
{debug-log var=$calendarData.fetch_parameters msg='Fecth eventi'}

<form class="calendar-tools" method='GET' action={concat('openpa/calendar/', $node.node_id)|ezurl}>
<input type='hidden' name="UrlAlias" value="{$node.url_alias}" />
<input type='hidden' name="View" value="program" />
<input type='hidden' name="CurrentInterval" value="{$calendarData.parameters.interval}" />
<div class="calendar-tools">    
    <input class="query" placeholder="Cerca testo" type="text" name="Query" value="{$calendarData.parameters.query}" />
    <input class="calendar_picker" placeholder="gg-mm-yyyy" type="text" name="SearchDate" title="Seleziona data" value="{$calendarData.parameters.picker_date}" />
    
    {foreach $calendarData.search_facets as $facetFieldName => $facets}
        <select name="{$facetFieldName}">
            <option value="">{$facetFieldName}</option>
            {foreach $facets as $styleAndName}                
                <option value="{$styleAndName.value|wash()}"{if $calendarData.parameters[$facetFieldName]|eq($styleAndName.value)} selected="selected"{/if}>{if $styleAndName.indent}&nbsp;&nbsp;&nbsp;{/if}{$styleAndName.name|wash()}</option>
            {/foreach}            
        </select>
    {/foreach}
    <input class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
    <button class="button imagebutton" type="submit" name="TodayButton" title="Azzera la ricerca">
        <img src={'images/icons/close_icon.png'|ezdesign()} title="Azzera la ricerca" />
    </button>
</div>

<table summary="Calendario degli eventi" class="smart_calendar" style="width: 100%">
<thead>
<tr colspan="7">
    <th class="month_name last_col float-break">
        <p>
            <input type="submit" name="PrevMonthCalendarButton" class="button" value="&laquo;" />
            {$calendarData.parameters.timestamp|datetime( custom, '%F' )|upfirst()}&nbsp;{$calendarData.parameters.year}
            <input type="submit" name="NextMonthCalendarButton" class="button" value="&raquo;" />
            
            {if $calendarData.search_count|eq(1)}
                <small>Trovato 1 evento nel periodo {$calendarData.parameters.search_from_timestamp|l10n( 'shortdate' )} - {$calendarData.parameters.search_to_timestamp|l10n( 'shortdate' )} </small>
            {elseif $calendarData.search_count|gt(1)}
                <small>Trovati {$calendarData.search_count} eventi nel periodo {$calendarData.parameters.search_from_timestamp|l10n( 'shortdate' )} - {$calendarData.parameters.search_to_timestamp|l10n( 'shortdate' )} </small>
            {/if}
            
        </p>
        <div class="view-buttons">
            <input type="submit" name="ViewCalendarButton" class="button" value="Calendario" />
            <input type="submit" name="ViewProgramButton" class="defaultbutton" value="Lista" />
        </div>
    </th>
</tr>
</thead>
</table>


{foreach $calendarData.day_by_day as $calendarDay}    
    {if $calendarDay.count|gt(0)}
        
        <div class="calendar-day-program float-break" id="day-{$calendarDay.identifier}">
            
            <h2>
                {if $calendarDay.is_today}Oggi - {$calendarDay.start|l10n( 'date' )}
                {elseif $calendarDay.is_tomorrow}Domani - {$calendarDay.start|l10n( 'date' )}
                {elseif and( $calendarDay.is_in_week, $calendarDay.is_in_month )}{*$calendarDay.start|datetime( 'custom', '%l' )*}{$calendarDay.start|l10n( 'date' )}
                {else}{$calendarDay.start|l10n( 'date' )}
                {/if}
            </h2>
    
            <div class="block">
            {foreach $calendarDay.events as $event sequence array( 'left', 'right' ) as $style}
            <div class="calendar-event {$style}">
                {include name="calendar-item" uri="design:calendar/program_item.tpl" event=$event}
            </div>
            {/foreach}
            </div>
        
        </div>
    {/if}
{/foreach}

<div class="calendar-more">    
    <input type="submit" name="AddIntervalButton" class="defaultbutton" value="Mostra altri eventi" />
</div>

</form>