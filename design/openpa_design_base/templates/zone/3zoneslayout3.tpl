<div id="layout-3-colonne-position" class="zone-layout-{$zone_layout|downcase()}">
<div id="layout-3-colonne" class="float-break">

	<div class="leftcol_3col">

	<!-- ZONE CONTENT: START -->



	{if and( is_set( $zones[0].blocks ), $zones[0].blocks|count() )}
	<div class="border-box">
	<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
	<div class="border-ml"><div class="border-mr"><div class="border-mc">
	<div class="border-content">	
	{foreach $zones[0].blocks as $block}
	{if or( $block.valid_nodes|count(), 
		and( is_set( $block.custom_attributes), $block.custom_attributes|count() ), 
		and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ), ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' )|not ) )}
		<div id="address-{$block.zone_id}-{$block.id}">
		{block_view_gui block=$block}
		</div>
	{else}
		{skip}
	{/if}
		{delimiter}
			<div class="block-separator"></div>
		{/delimiter}
	{/foreach}
	</div>
	</div></div></div>
	<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
	</div>
	{/if}


	<!-- ZONE CONTENT: END -->

	</div>

	<div class="maincol_3col">

	<!-- ZONE CONTENT: START -->


	{if and( is_set( $zones[1].blocks ), $zones[1].blocks|count() )}
	<div class="border-box">
	<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
	<div class="border-ml"><div class="border-mr"><div class="border-mc">
	<div class="border-content">
	{foreach $zones[1].blocks as $block}
	{if or( $block.valid_nodes|count(), 
		and( is_set( $block.custom_attributes), $block.custom_attributes|count() ), 
		and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ), ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' )|not ) )}
		<div id="address-{$block.zone_id}-{$block.id}">
		{block_view_gui block=$block}
		</div>
	{else}
		{skip}
	{/if}
		{delimiter}
			<div class="block-separator"></div>
		{/delimiter}
	{/foreach}
	</div>
	</div></div></div>
	<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
	</div>
	{/if}


	<!-- ZONE CONTENT: END -->

	</div>

	<div class="rightcol_3col">

	<!-- ZONE CONTENT: START -->


	{if and( is_set( $zones[2].blocks ), $zones[2].blocks|count() )}
	<div class="border-box">
	<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
	<div class="border-ml"><div class="border-mr"><div class="border-mc">
	<div class="border-content">
	{foreach $zones[2].blocks as $block}
	{if or( $block.valid_nodes|count(), 
		and( is_set( $block.custom_attributes), $block.custom_attributes|count() ), 
		and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ), ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' )|not ) )}
		<div id="address-{$block.zone_id}-{$block.id}">
		{block_view_gui block=$block}
		</div>
	{else}
		{skip}
	{/if}
		{delimiter}
			<div class="block-separator"></div>
		{/delimiter}
	{/foreach}
	</div>
	</div></div></div>
	<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
	</div>
	{/if}


	<!-- ZONE CONTENT: END -->

	</div>

</div>
</div>