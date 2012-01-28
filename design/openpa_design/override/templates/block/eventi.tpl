{*
	TEMPLATE BLOCCO CALENDARIO
	prevede una visualizzazione a tab secondo 2 logiche differenti:
	1) oggi
	2) ultimi inseriti
*}

{def $valid_node = $block.valid_nodes[0]}


{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js', 'jcarousel.js' ) )}

<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('.block-lista_tab .ui-tabs-nav li a').each(function(index) {
		$(this).attr( 'href', '#'+$('span', this).attr('class') );
	});
	$("#zone-id-{/literal}{$block.id}{literal}").tabs({ 
		tabTemplate: '<![CDATA[<li><a class="no-js-hide" href="#{href}"><span>#{label}</span></a></li>]]>'
	});
	
		$(".eventi-cycle").jcarousel({
			vertical: true
		});

});
{/literal}
</script>

<div class="block-type-lista block-{$block.view} block-lista_tab">

	{if $block.name}
		<h2 class="block-title">{$block.name}</h2>
	{else}
		<h2 class="block-title">{$valid_node.name|wash()}</h2>
	{/if}


	<div  class="ui-tabs">		
	
		<div class="border-box box-trans-blue box-tabs-header tabs">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content" id="zone-id-{$block.id}">
			<ul class="ui-tabs-nav">							 

				<li class="ui-state-active eventi-oggi">
					<a href="#oggi"><span class="oggi">Oggi</span></a>
				</li>

				<li class="ui-state-default eventi-prossimamente">
					<a href={concat($valid_node.url_alias, '/(show)/prossimamente/')|ezurl()} 
					   title="{$valid_node.name|wash()}"><span class="prossimamente">Prossimamente</span></a>
				</li>			
				
			</ul>
		</div>
		</div></div></div>
		</div>		
	

{def

    $event_node    = $valid_node
    $event_node_id = $valid_node.node_id

    $curr_ts = currentdate()
    $curr_today = $curr_ts|datetime( custom, '%j')
    $curr_year = $curr_ts|datetime( custom, '%Y')
    $curr_month = $curr_ts|datetime( custom, '%n')

    $temp_ts = currentdate()

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
}

{if ne($temp_month, 12)}
    {set $last_ts = makedate( $temp_month|sum( 1 ), 1, $temp_year )}
{else}
    {set $last_ts = makedate( 1, 1, $temp_year|sum(1) )}
{/if}

{def $events=fetch( 'content', 'list', hash(
                                'parent_node_id', $event_node_id,
                                'sort_by', array( 'attribute', false(), 'event/from_time'),
                                'class_filter_type',  'include',
                                'class_filter_array', array( 'event' ),
                                'main_node_only', true(),
                                'attribute_filter',
                                array( 'or',
                                                array( 'event/from_time', 'between', array( sum($first_ts,1), sub($last_ts,1)  )),
                                                array( 'event/to_time', 'between', array( sum($first_ts,1), sub($last_ts,1) )),
                                                array(  'and', array( 'event/from_time', '<', '$first_ts'), array( 'event/to_time', '>', '$last_ts') )
                                )
            ))}


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
            {set $day_events = $day_events|append( $event )}
        {/if}
    {/for}
{/foreach}

		<div class="tabs-panels">
			<div id="oggi" class="ui-tabs-hide">
				
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					{def $day_events_count = $day_events|count()}
					{if $day_events_count}
					<ul class="eventi-cycle">								
						{foreach $day_events as $index => $child}
							<li class="evento-cycle {if $day_events_count|eq($index|inc())}lastli{/if}{if $index|eq(2)} no-js-lastli{/if}{if $index|ge(3)} no-js-hide{/if}">												
							{node_view_gui content_node=$child view='event'}
							</li>
						{/foreach}
					</ul>
					{/if}
				</div>
				</div></div></div>
				</div>
				
				<div class="border-box box-violet-gray box-tabs-footer tab-link">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">				
					<a class="calendar arrows" href={$valid_node.url_alias|ezurl()} title="{$valid_node.name|wash()}"><span class="arrows-blue-r">Vedi tutto il calendario</span></a>			
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>
				
			</div>

			{def $prossimi=fetch( 'content', 'list',
                                                hash( 'parent_node_id', $valid_node.node_id,
                                                        'sort_by', array('published', false()),
                                                        'limit', 5
                                                     )
                                            )}
			<div id="prossimamente" class="no-js-hide ui-tabs-hide">
				
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">				
				<div class="border-content">
					{def $prossimi_count = $prossimi|count()}
					{if $prossimi_count}
					<ul class="eventi-cycle">						
						{foreach $prossimi as $index => $child}
							<li class="evento-cycle {if $prossimi_count|eq($index|inc())}lastli{/if}{if $index|eq(2)} no-js-lastli{/if}{if $index|ge(3)} no-js-hide{/if}">	
							{node_view_gui content_node=$child view='event'}
							</li>
						{/foreach}
					</ul>
					{/if}
				</div>
				</div></div></div>
				</div>
				
				<div class="border-box box-violet-gray box-tabs-footer tab-link">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">					
					<a class="calendar arrows" href={$valid_node.url_alias|ezurl()} title="{$valid_node.name|wash()}"><span class="arrows-blue-r">Vedi tutto il calendario</span></a>		
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>
				
			</div>
		</div>

	</div>
</div>