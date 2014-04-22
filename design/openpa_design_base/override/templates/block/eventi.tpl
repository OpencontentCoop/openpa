{def $valid_node = $block.valid_nodes[0]}

{if $valid_node|not()}
    {set $valid_node = ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
{/if}

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

{def $calendarDataDay = fetch( openpa, calendario_eventi, hash( 'calendar', $valid_node, 'params', hash( 'interval', 'PT1439M' ) ) )}
{if is_set( $block.custom_attributes )}
    {def $calendarDataOther = fetch( openpa, calendario_eventi, hash( 'calendar', $valid_node, 'params', $block.custom_attributes ) )}
{else}
    {def $calendarDataOther = false()}
{/if}
{debug-log var=$calendarDataDay.fetch_parameters msg='Blocco eventi fetch oggi'}
     
{def $day_events = $calendarDataDay.events
     $day_events_count = $calendarDataDay.search_count
     $prossimi = array()
     $prossimi_count = 0}

{if $calendarDataOther}     
{debug-log var=$calendarDataOther.fetch_parameters msg='Blocco eventi fetch secondo tab'}
{set $prossimi = $calendarDataOther.events
     $prossimi_count = $calendarDataOther.search_count}
{/if}     
     
    {if and( $prossimi_count|eq(0), $day_events_count|eq(0) )}
    
        <div class="warning"><p>Nessun evento in programma</p></div>
    
    {else}
    
    <div  class="ui-tabs">		
	
		<div class="border-box box-trans-blue box-tabs-header tabs">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content" id="zone-id-{$block.id}">
			<ul class="ui-tabs-nav">							 

				{if $day_events_count|ne(0)}
                <li class="ui-state-active eventi-oggi">
					<a href="#oggi"><span class="oggi">Oggi</span></a>
				</li>
                {/if}

                {if $prossimi_count|gt(0)}
				<li class="{if $day_events_count|ne(0)}ui-state-default{else}ui-state-active{/if} eventi-prossimamente">
					<a href={concat($valid_node.url_alias, '/(show)/', $block.custom_attributes.tab_title|slugize)|ezurl()} 
					   title="{$valid_node.name|wash()}"><span class="panel-{$block.custom_attributes.tab_title|slugize}">{$block.custom_attributes.tab_title}</span></a>
				</li>
                {/if}
				
			</ul>
		</div>
		</div></div></div>
		</div>	


		<div class="tabs-panels">
            
            {if $day_events_count|ne(0)}
			<div id="oggi" class="ui-tabs-hide">
				
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					{if $day_events_count}
					<ul class="eventi-cycle">								
						{foreach $day_events as $index => $child}
							<li class="evento-cycle {if $day_events_count|eq($index|inc())}lastli{/if}{if $index|eq(2)} no-js-lastli{/if}{if $index|ge(3)} no-js-hide{/if}">												
							{node_view_gui content_node=$child.node view='event'}
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
            {/if}

			{if $prossimi_count|gt(0)}
			<div id="panel-{$block.custom_attributes.tab_title|slugize}" class="no-js-hide ui-tabs-hide">
				
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">				
				<div class="border-content">					
					{if $prossimi_count}
					<ul class="eventi-cycle">						
						{foreach $prossimi as $index => $child}
							<li class="evento-cycle {if $prossimi_count|eq($index|inc())}lastli{/if}{if $index|eq(2)} no-js-lastli{/if}{if $index|ge(3)} no-js-hide{/if}">	
							{node_view_gui content_node=$child.node view='event'}
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
            {/if}
            
		</div>

	</div>
        
    {/if}
    
</div>