{ezpagedata_set( 'left_menu', false() )}
{ezpagedata_set( 'extra_menu', false() )}
{ezpagedata_set( 'show_path', false() )}

<div class="border-box">
<div class="global-view-full content-view-full">
    <div class="class-folder">

        <h1>{$node.name|wash()}</h1>
    
        {include name=Validation uri='design:content/collectedinfo_validation.tpl'
                 class='message-warning'
                 validation=$validation
                 collection_attributes=$collection_attributes}
                         
        <input class="defaultbutton" action="action" type="button" value="Indietro" onclick="history.go(-1);" />
        
    </div>
</div>
</div>
