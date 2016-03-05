{def $handler = class_extra_parameters($node.class_identifier,'table_view')}
{if $handler.enabled}
    <table class="table">
        {foreach $node.object.data_map as $identifier => $attribute}
            {if and( $handler.show|contains( $identifier ), or( $handler.show_empty|contains( $identifier ), $attribute.has_content ) )}
                <tr>
                    {if and( $handler.show_label|contains( $identifier ), $handler.collapse_label|contains( $identifier )|not() )}
                        <th>{$attribute.contentclass_attribute_name}</th>
                    {/if}
                    <td{if $handler.show_label|contains( $identifier )|not()} colspan="2"{/if}>
                        {if and( $handler.show_label|contains( $identifier ), $handler.collapse_label|contains( $identifier ) )}
                            <strong>{$attribute.contentclass_attribute_name}</strong>
                        {/if}
                        {attribute_view_gui attribute=$attribute image_class=small}
                    </td>
                </tr>
            {/if}
        {/foreach}
    </table>
{/if}