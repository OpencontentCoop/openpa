{* Folder - Full view *}
{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

{def $current_user = fetch( 'user', 'current_user' )
     $classes_parent_to_edit=array('file_pdf', 'news')
     $sezioni_per_tutti= openpaini( 'GestioneSezioni', 'sezioni_per_tutti' )
	 $style='col-odd'
}

<div class="border-box">
<div class="global-view-full content-view-full">
    <div class="class-folder">

        <h1>{$node.name|wash}</h1>
	
        {* DATA e ULTIMAMODIFICA *}
        {*include name = last_modified
                 node = $node             
                 uri = 'design:parts/openpa/last_modified.tpl'*}
    
        {* EDITOR TOOLS *}
        {include name = editor_tools
                 node = $node             
                 uri = 'design:parts/openpa/editor_tools.tpl'}
    
        {* ATTRIBUTI : mostra i contenuti del nodo *}
        {include name = attributi_principali
                 uri = 'design:parts/openpa/attributi_principali.tpl'
                 node = $node}

        <div class="attributi-principali float-break col col-notitle">
            {if and( is_set($node.data_map.description), $node.data_map.description.has_content )}
            <div class="col-content-design">
                {attribute_view_gui attribute=$node.data_map.description}
            </div>
            {/if}
        </div>
        

        {include name=Validation uri='design:content/collectedinfo_validation.tpl'
                 class='message-warning'
                 validation=$validation collection_attributes=$collection_attributes}
        <form method="post" action={"content/action"|ezurl}>
        {def $_style='col-odd'}
        {foreach $node.object.contentobject_attributes as $attribute}
            {if $attribute.is_information_collector}
            {if $_style|eq( 'col-even' )}{set $_style = 'col-odd'}{else}{set $_style = 'col-even'}{/if}
            <div class="{$_style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">                
                <div class="col-content"><div class="col-content-design">
                <p><strong>{$attribute.contentclass_attribute_name}</strong></p>
                {attribute_view_gui attribute=$attribute}
                </div></div>
            </div>
            
            {/if}
        {/foreach}
        <div class="content-action">
            <input type="submit" class="defaultbutton" name="ActionCollectInformation" value="{"Send form"|i18n("design/ezwebin/full/feedback_form")}" />
            <input type="hidden" name="ContentNodeID" value="{$node.node_id}" />
            <input type="hidden" name="ContentObjectID" value="{$node.object.id}" />
            <input type="hidden" name="ViewMode" value="full" />
        </div>
            
        </form>
        
        {* TIP A FRIEND *}
        {include name=tipafriend node=$node uri='design:parts/common/tip_a_friend.tpl'}

    </div>
</div>
</div>