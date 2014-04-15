<div class="zone-layout-{$zone_layout|downcase()} norightcol">

<div class="content-columns float-break">
<div class="leftcol-position">
<div class="leftcol">

<!-- ZONE CONTENT: START -->

<div class="zone-content float-break">
{if and( is_set( $zones[0].blocks ), $zones[0].blocks|count() )}
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
{/if}
</div>

<!-- ZONE CONTENT: END -->

<!-- COLUMNS TWO: START -->

<div class="columns-two">
<div class="col-1">

<!-- ZONE CONTENT: START -->

<div class="zone-content float-break">
{if and( is_set( $zones[2].blocks ), $zones[2].blocks|count() )}
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
{/if}
</div>

<!-- ZONE CONTENT: END -->

</div>
<div class="col-2">

<!-- ZONE CONTENT: START -->

<div class="zone-content float-break">
{if and( is_set( $zones[3].blocks ), $zones[3].blocks|count() )}
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
{/if}
</div>

<!-- ZONE CONTENT: END -->

</div>
</div>

<!-- COLUMNS TWO: END -->

</div>
</div>

<div class="maincol-position">
<div class="maincol">

<!-- ZONE CONTENT: START -->

<div class="zone-content float-break">
{if and( is_set( $zones[1].blocks ), $zones[1].blocks|count() )}
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
{/if}
</div>

<!-- ZONE CONTENT: END -->

</div>
</div>

</div>

</div>