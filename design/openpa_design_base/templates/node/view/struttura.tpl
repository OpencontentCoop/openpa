{* 
	STRUTTURE COMUNALI
	 template adatto a chiamate ajax 
*}
{def $available_classes=array('ufficio','servizio') $id=$view_parameters.object_id}

{if $id|gt(0)}
	{if $available_classes|contains($view_parameters.inverso)}
		{if $available_classes|contains($view_parameters.classe)}
			{def $objects=fetch( 'content', 'reverse_related_objects', 
					hash('object_id', $id, 'attribute_identifier', concat($view_parameters.inverso, '/', $view_parameters.classe)))}
			{foreach $objects as $object}
			<option value="{$object.id}" {if $view_parameters.selezionato|eq($object.id)} selected="selected" {/if}>{$object.name}</option>
			{/foreach}
		{/if}
	{/if}
{/if}
