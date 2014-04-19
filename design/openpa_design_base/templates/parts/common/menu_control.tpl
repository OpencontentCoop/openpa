{* extra info *}
{if or( openpaini( 'ExtraMenu', 'NascondiNeiNodi', array( 0 ) )|contains($node.node_id),
        openpaini( 'ExtraMenu', 'NascondiNelleClassi', array( 0 ) )|contains($node.class_identifier),
        openpaini( 'ExtraMenu', 'Nascondi', false() ) )}
    {ezpagedata_set( 'extra_menu', false() )}

{elseif $node.parent.class_identifier|eq( 'newsletter' )}
    {ezpagedata_set( 'extra_menu', 'extra_info_newsletter' )}

{elseif $node.parent.class_identifier|eq( 'pagina_trasparenza' )}
    {ezpagedata_set( 'extra_menu', false() )}
{/if}
{* left menu *}
{if or( openpaini( 'SideMenu', 'NascondiNeiNodi', array( 0 ) )|contains($node.node_id),
        openpaini( 'SideMenu', 'NascondiNelleClassi', array( 0 ) )|contains($node.class_identifier) )}
	{ezpagedata_set( 'left_menu', false() )}
{/if}