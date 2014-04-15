{* 
	STRUTTURE COMUNALI
	 template adatto a chiamate ajax 
*}
{def $available_classes=array('ufficio','servizio')
     $classe = $arguments[0]
     $id = $arguments[1]
     $inverso = $arguments[2]
     $selezionato = $arguments[3]}
{if $id|gt(0)}
{if $available_classes|contains($inverso)}
{if $available_classes|contains($classe)}
{def $objects=fetch( 'content', 'reverse_related_objects', 
hash('object_id', $id, 'attribute_identifier', concat($inverso, '/', $classe)))}
{foreach $objects as $object}
<option value="{$object.id}" {if $selezionato|eq($object.id)} selected="selected" {/if}>{$object.name}</option>
{/foreach}
{/if}
{/if}
{/if}