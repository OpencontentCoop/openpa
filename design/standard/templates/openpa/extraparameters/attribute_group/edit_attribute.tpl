{*
    @var OCClassExtraParametersHandlerInterface $handler
    @var eZContentClass $class
    @var eZContentClassAttribute $attribute
*}
<td>
<div class="checkbox">
    <label>
        <input type="checkbox" name="extra_handler_{$handler.identifier}[class_attribute][{$class.identifier}][{$attribute.identifier}][contacts]" value="1" {if $handler.contacts|contains($attribute.identifier)}checked="checked"{/if} /> Contatti
    </label>
</div>
</td>