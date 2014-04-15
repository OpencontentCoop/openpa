{*
	
	TOOLBAR DELL'EDITOR

	current_user	oggetto utente collegato
	node		nodo di partenza
	object		oggetto di partenza
	servizio	nodo del servizio di appartenenza dell'oggetto da editare
	servizio_utente	nodo del servizio a cui appartiene l'utente
	has_servizio	booleano

*}

{def 	$create_policyNews=fetch( 'content', 'access', 
			hash( 'access', 'create', 'contentobject', $node, 'contentclass_id', 'news' ) )
	$create_policyFile=fetch( 'content', 'access', 
			hash( 'access', 'create', 'contentobject', $node, 'contentclass_id', 'file_pdf' ) ) }
<div class="website_toolbar">
<div class="tl"><div class="tr"><div class="tc"></div></div></div>
<div class="mc"><div class="ml"><div class="mr float-break">

        <form method="post" action={"content/action"|ezurl} class="left">
	        <input type="hidden" name="HasMainAssignment" value="1" />
		<input type="hidden" name="ContentObjectID" value="{$object.id}" />
		<input type="hidden" name="NodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentLanguageCode" value="ita-IT" />
		<input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
		{if $object.can_edit}
		        {if $has_servizio|eq('none')}
				<input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage} name="EditButton" title="Modifica: {$object.content_class.name|wash()}" />
		        {else}
                		{if $servizio_utente|eq($servizio)}
				<input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage} name="EditButton" title="Modifica: {$object.content_class.name|wash()}" />
				{/if}
		        {/if}
		{/if}

		{if $object.can_remove}
		        {if $has_servizio|eq('none')}
			<input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage} name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}: {$object.content_class.name|wash()}" />
		        {else}
		        	{if $servizio_utente|eq($servizio)}
				<input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage} name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}: {$object.content_class.name|wash()}" />
				{/if}
	        	{/if}
		{/if}
        </form> 


{if and($create_policyNews,$servizio_utente|eq($servizio))}
        <form method="post" action={"content/action"|ezurl} class="left">
	        <input type="hidden" name="ContentLanguageCode" value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}" />
        	<input type="image" src={"websitetoolbar/news.png"|ezimage} name="NewButton" title="Crea qui una news per questo oggetto" />
	        <input type="hidden" name="ClassID" value="116" />
		<input type="hidden" name="HasMainAssignment" value="1" />
		<input type="hidden" name="ContentObjectID" value="{$object.id}" />
		<input type="hidden" name="NodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
        </form>
{/if}
{undef $create_policyNews}

{if and($create_policyFile, $servizio_utente|eq($servizio))}
        <form method="post" action={"content/action"|ezurl} class="left">
  	      <input type="hidden" name="ContentLanguageCode" value="{ezini( 'RegionalSettings', 'ContentObjectLocale', 'site.ini')}" />
        	<input type="image" src={"websitetoolbar/file_pdf.png"|ezimage} name="NewButton" title="Crea qui un file allegato per questo oggetto" />
        	<input type="hidden" name="ClassID" value="102" />
		<input type="hidden" name="HasMainAssignment" value="1" />
		<input type="hidden" name="ContentObjectID" value="{$object.id}" />
		<input type="hidden" name="NodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
		<input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
        </form>
{/if}
{undef $create_policyFile}

</div></div></div>
<div class="bl"><div class="br"><div class="bc"></div></div></div>
</div>
