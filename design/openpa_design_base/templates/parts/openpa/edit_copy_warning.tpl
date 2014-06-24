{if ezhttp_hasvariable( 'from', 'get' )}	  
  {def $original_object = fetch( content, object, hash( object_id, ezhttp( 'from', 'get' ) ) )}
  {if $original_object}
	{def $original_node = $original_object.main_node}
	<div class="warning">
	  <strong>Attenzione:</strong> L'oggetto che si sta editando &egrave; stato creato copiando l'oggetto <a target="_blank" href={$original_node.url_alias|ezurl}>{$original_node.name|wash}</a>
	</div>
	{set $_redirect = concat( 'content/view/full/', $object.main_node_id )}
	{undef $original_object $original_node}
  {/if}
{/if}