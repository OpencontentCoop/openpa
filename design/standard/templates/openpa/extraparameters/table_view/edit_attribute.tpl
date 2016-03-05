{*
    @var OCClassExtraParametersHandlerInterface $handler
    @var eZContentClass $class
    @var eZContentClassAttribute $attribute
*}
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show]" value="1" {if $handler.show|contains($attribute.identifier)}checked="checked"{/if} /> Mostra in visualizzazione tabellare
    </label>
</div>
</td>
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show_label]" value="1" {if $handler.show_label|contains($attribute.identifier)}checked="checked"{/if} /> Mostra etichetta
    </label>
</div>
</td>
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show_empty]" value="1" {if $handler.show_empty|contains($attribute.identifier)}checked="checked"{/if} /> Mostra anche se non popolato
    </label>
</div>
</td>
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][collapse_label]" value="1" {if $handler.collapse_label|contains($attribute.identifier)}checked="checked"{/if} /> Collassa etichetta
    </label>
</div>
</td>
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show_link]" value="0" {if $handler.show_link|contains($attribute.identifier)}checked="checked"{/if} /> Mostra link (oggetto correlato)
    </label>
</div>
</td>
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][highlight]" value="1" {if $handler.highlight|contains($attribute.identifier)}checked="checked"{/if} /> Evidenzia
    </label>
</div>
</td>