{if is_set($error)}
<div class="warning"><h2>{$error|wash()}</div>
{/if}

<form action={'openpa/fix_class_relation/'|ezurl()} method="post">
	<div class="block">
		<label>Vecchio identificatore di classe</label>
		<input class="box" type="text" name="old_class_identifier" value="{$old_class_identifier}" placeholder="articolo" />
	</div>
	<div class="block">
		<label>Nuovo identificatore di classe</label>
		<input class="box" type="text" name="new_class_identifier" value="{$new_class_identifier}" placeholder="article" />
	</div>
	<button class="defaultbutton btn btn-primary" type="submit" name="check_classes">Controlla</button>

	{if $wrong_relation_classes|count()|gt(0)}
		<hr />
		<h4>Ho trovati questi identificatori</h4>
		<ol>
		{foreach $wrong_relation_classes as $id => $item}
			<li>
				<label>
					<input type="checkbox" name="fix_identifier[]" value="{$id}" checked="checked" />
					{$item.identifier|wash()}: {$item.list|implode(', ')}
				</label>
			</li>
		{/foreach}
		</ol>

		<button class="defaultbutton btn btn-primary" type="submit" name="fix_attributes">Correggi selezionati</button>
	{/if}

</form>