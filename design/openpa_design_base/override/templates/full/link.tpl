{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="global-view-full content-view-full">
<div class="class-link">

    <h1>{attribute_view_gui attribute=$node.data_map.name}</h1>
	<div class="last-modified">Ultima modifica: {$node.object.modified|l10n(date)}</div>

	{* EDITOR TOOLS *}
	{include name = editor_tools
             node = $node             
             uri = 'design:parts/openpa/editor_tools.tpl'}

	{* ATTRIBUTI : mostra i contenuti del nodo *}
    {include name = attributi_principali
             uri = 'design:parts/openpa/attributi_principali.tpl'
             node = $node}

    {if ne( $node.data_map.location.content, '' )}
        <div class="attribute-link">
        	<p>
				Collegamento diretto su: 
                <a href="{$node.data_map.location.content}" target="_blank">
                    {if ne( $node.data_map.location.data_text, '' )}
                        {$node.data_map.location.data_text}
                    {else}
                    {$node.data_map.location.content}
                {/if}
                </a>
            </p>
        </div>
    {/if}


    <div class="attributi-base">
        {def $style='col-odd'}
        {if $node.data_map.descrizione.has_content}
            <div class="{$style} col col-notitle float-break attribute-{$attribute.contentclass_attribute_identifier}">
                <div class="col-content"><div class="col-content-design">
                    {attribute_view_gui attribute=$node.data_map.descrizione}
                </div></div>
            </div>
        {/if}
    </div>

</div>
</div>
</div>
