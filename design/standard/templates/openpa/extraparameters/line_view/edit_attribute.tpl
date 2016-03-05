{*
    @var OCClassExtraParametersHandlerInterface $handler
    @var eZContentClass $class
    @var eZContentClassAttribute $attribute
*}
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show]" value="1" {if $handler.show|contains($attribute.identifier)}checked="checked"{/if} /> Mostra in line
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
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show_link]" value="1" {if $handler.show_link|contains($attribute.identifier)}checked="checked"{/if} /> Mostra link (oggetto correlato)
    </label>
</div>
</td>