{if $node.object.can_edit}
    <form method="post" action={"content/action"|ezurl} style="display: inline">
        <input type="hidden" name="HasMainAssignment" value="1" />
        <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
        <input type="hidden" name="NodeID" value="{$node.node_id}" />
        <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
        <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
        <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
        <input type="image" src={"websitetoolbar/ezwt-icon-edit.png"|ezimage}
               name="EditButton" title="{'Edit'|i18n( 'design/ezwebin/parts/website_toolbar')}" />            
        {if $node.object.can_remove}
        <input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage}
               name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}" />            
        {/if}
    </form>
{/if}