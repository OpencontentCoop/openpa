{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="border-content">

 <div class="global-view-full content-view-full">
  <div class="class-{$node.object.class_identifier}">

        <div class="attribute-header">
            <h1>{$node.name|wash()}</h1>
        </div>

        <div class="attribute-short">
        {attribute_view_gui attribute=$node.data_map.description}
        </div>

        <form method="post" action={"content/action"|ezurl}>
        <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
        <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
        <input type="hidden" name="ViewMode" value="full" />

        <div class="content-question">
        {attribute_view_gui attribute=$node.data_map.question}
        </div>

        {if is_unset( $versionview_mode )}
        <input class="button" type="submit" name="ActionCollectInformation" value="{"Vote"|i18n("design/ezwebin/full/poll")}" />
        {/if}

        </form>

        <div class="content-results">
            <div class="attribute-link">
                <p><a href={concat( "/content/collectedinfo/", $node.node_id, "/" )|ezurl}>{"Result"|i18n("design/ezwebin/full/poll")}</a></p>
            </div>
        </div>

    {include name=menu_control node=$node uri='design:parts/common/social_control.tpl'}

    </div>
</div>

</div>
</div>