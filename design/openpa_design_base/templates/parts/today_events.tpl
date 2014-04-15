{def $calendarDataDay = fetch( openpa, calendario_eventi, hash( 'calendar', $node.parent, 'params', hash( 'interval', 'PT1439M',
                                                                                                          'filter', array( concat( '-meta_id_si:', $node.contentobject_id ) ) ) ) )}
{def $day_events = $calendarDataDay.events
     $day_events_count = $calendarDataDay.search_count}
{if $day_events_count|ne(0)}

<div class="block-type-lista block-eventi block-lista_tab">
<div  class="ui-tabs">		
	
		<div class="border-box box-trans-blue box-tabs-header tabs">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content">
			<ul class="ui-tabs-nav">							 
                <li class="ui-state-active eventi-oggi">
					<a href="#oggi"><span class="oggi">Oggi</span></a>
				</li>
			</ul>
		</div>
		</div></div></div>
		</div>	


		<div class="tabs-panels">
            
			<div id="oggi">
				
				<div class="border-box box-violet box-tabs-panel">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">
					<ul class="eventi-cycle">								
						{foreach $day_events as $event}        
                            {node_view_gui content_node=$event.node view='event'}
                            {delimiter}<hr />{/delimiter}
                        {/foreach}
					</ul>
				</div>
				</div></div></div>
				</div>
				
				<div class="border-box box-violet-gray box-tabs-footer tab-link">
				<div class="border-ml"><div class="border-mr"><div class="border-mc">
				<div class="border-content">				
					<a class="calendar arrows" href={$node.parent.url_alias|ezurl()} title="{$node.parent.name|wash()}"><span class="arrows-blue-r">Vedi tutto il calendario</span></a>			
				</div>
				</div></div></div>
				<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
				</div>
				
			</div>
		</div>
	</div>    
</div>
{/if}   