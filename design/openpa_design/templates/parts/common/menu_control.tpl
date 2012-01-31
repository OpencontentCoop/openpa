{if or( openpaini( 'ExtraMenu', 'NascondiNeiNodi', array( 0 ) )|contains($node.node_id), openpaini( 'ExtraMenu', 'NascondiNelleClassi', array( 0 ) )|contains($node.class_identifier), openpaini( 'ExtraMenu', 'Nascondi', false() ) )}
    {set scope=global persistent_variable=hash('extra_menu', false())}
{/if}
{if or( openpaini( 'SideMenu', 'NascondiNeiNodi', array( 0 ) )|contains($node.node_id), openpaini( 'SideMenu', 'NascondiNelleClassi', array( 0 ) )|contains($node.class_identifier) )}
	{set scope=global persistent_variable=hash('left_menu', false(), 'extra_menu', false())}
{/if}