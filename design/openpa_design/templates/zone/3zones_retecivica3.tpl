<div class="zone-layout-{$zone_layout|downcase()}">

<div class="content-columns float-break">
    <div class="leftcol-position">
        <div class="leftcol">
        
        {if and( is_set( $zones[0].blocks ), $zones[0].blocks|count() )}
        <div class="zone-content float-break">
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
        {/if}
        
        </div>
    
        <div class="maincol">
        
        {if and( is_set( $zones[1].blocks ), $zones[1].blocks|count() )}
        <div class="zone-content float-break">
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
        {/if}
        
        </div>
    </div>

</div>

<div class="rightcol-position">
<div class="rightcol">

{if and( is_set( $zones[2].blocks ), $zones[2].blocks|count() )}
<div class="zone-content float-break">
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
{/if}

</div>
</div>

</div>
