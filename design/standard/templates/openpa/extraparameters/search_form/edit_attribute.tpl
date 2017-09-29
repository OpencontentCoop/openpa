{*
    @var OCClassExtraParametersHandlerInterface $handler
    @var eZContentClass $class
    @var eZContentClassAttribute $attribute
*}
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" {if $attribute.is_searchable|not}disabled="disabled"{/if} name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][show]" value="1" {if and( $attribute.is_searchable, $handler.show|contains($attribute.identifier))}checked="checked"{/if} /> Mostra nel form
    </label>
</div>
</td>
