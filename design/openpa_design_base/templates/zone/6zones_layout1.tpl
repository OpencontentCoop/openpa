<div class="zone-layout-{$zone_layout|downcase()} norightcol">

<div class="top-zone-position">
<div class="top-zone">
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
</div>

<div class="columns-three">
<div class="col-1-2">
<div class="col-1">

<div class="central_left-zone-position">
<div class="central_left-zone">
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
</div>

</div>
<div class="col-2">

<div class="centra_middle-zone-position">
<div class="centra_middle-zone">
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
</div>
<div class="col-3">

<div class="central_right-zone-position">
<div class="central_right-zone">
<!-- ZONE CONTENT: START -->
{if and( is_set( $zones[3].blocks ), $zones[3].blocks|count() )}

<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
{foreach $zones[3].blocks as $block}
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
</div>


<div class="content-columns float-break">
<div class="bottom_left-zone-position leftcol-position">
<div class="bottom_left-zone leftcol">

<!-- ZONE CONTENT: START -->



{if and( is_set( $zones[4].blocks ), $zones[4].blocks|count() )}
<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
{foreach $zones[4].blocks as $block}
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

<div class="bottom_right-zone-position maincol-position">
<div class="bottom_right-zone maincol">

<!-- ZONE CONTENT: START -->

{if and( is_set( $zones[5].blocks ), $zones[5].blocks|count() )}
<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
{foreach $zones[5].blocks as $block}
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

</div>