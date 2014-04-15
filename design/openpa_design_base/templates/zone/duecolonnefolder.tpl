<!-- ZONE CONTENT: START -->
<div class="leftcol">
 
		<div class="border-box">
	  		<div class="border-tl">
               <div class="border-tr">
                    <div class="border-tc">
                    </div>
               </div>
         </div>
	  		<div class="border-ml">
               <div class="border-mr">
                    <div class="border-mc">
			 					<div class="border-content">

			 						{if and( is_set( $zones[0].blocks ), $zones[0].blocks|count() )}
										{foreach $zones[0].blocks as $block}
											{if or( $block.valid_nodes|count(), 
    					            		and( is_set( $block.custom_attributes), $block.custom_attributes|count() ), 
    						    				and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ), 
                                             ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' )|not ) )}
    												<div id="address-{$block.zone_id}-{$block.id}">
    													{block_view_gui block=$block}
    												</div>
											{else}
    											{skip}
											{/if}
    					   			 {delimiter}
        				  					<div class="block-separator">
					   					</div>
    					  				 {/delimiter}
										{/foreach}
									{/if}

								</div>
		   				</div>
               </div>
  			</div>

	  	   <div class="border-bl">
				<div class="border-br">
		     		<div class="border-bc">
		     		</div>
				</div>
	  		</div>
		</div>

</div>
<!-- ZONE CONTENT: END -->

<!-- ZONE CONTENT: START -->

<div class="rightcol">

	<div class="border-box">
		<div class="border-tl">
			<div class="border-tr">
				<div class="border-tc">
				</div>
			</div>
		</div>
	
	<div class="border-ml">
		<div class="border-mr">
			<div class="border-mc">

	<div class="border-content">
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

			</div>
		</div>
	</div>

	<div class="border-bl">
		<div class="border-br">
			<div class="border-bc">
			</div>
		</div>
	</div>
	
</div>
<!-- ZONE CONTENT: END -->