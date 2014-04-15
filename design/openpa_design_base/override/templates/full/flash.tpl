{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="border-content">

  <div class="global-view-full content-view-full">
   <div class="class-{$node.object.class_identifier}">

 	<h1>{$node.name|wash()}</h1>
	{* DATA e ULTIMAMODIFICA *}
	{include name = last_modified
             node = $node             
             uri = 'design:parts/openpa/last_modified.tpl'}
	
	{* EDITOR TOOLS *}
	{include name = editor_tools
             node = $node             
             uri = 'design:parts/openpa/editor_tools.tpl'}

	{* ATTRIBUTI : mostra i contenuti del nodo *}
    {include name = attributi_principali
             uri = 'design:parts/openpa/attributi_principali.tpl'
             node = $node}

        <div class="content-media">
        {def $attribute=$node.data_map.file}
            <object codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0"
                    {section show=$attribute.content.width|gt( 0 )}width="{$attribute.content.width}"{/section} {section show=$attribute.content.height|gt( 0 )}height="{$attribute.content.height}"{/section} id="objectid{$node.object.id}">
    
            <param name="movie" value={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename|explode('.')|implode('_'))|ezurl} />
            <param name="quality" value="{$attribute.content.quality}" />
            <param name="play" value="{section show=$attribute.content.is_autoplay}true{/section}" />
            <param name="loop" value="{section show=$attribute.content.is_loop}true{/section}" />
            <embed src={concat("content/download/",$attribute.contentobject_id,"/",$attribute.content.contentobject_attribute_id,"/",$attribute.content.original_filename|explode('.')|implode('_'))|ezurl}
                   type="application/x-shockwave-flash"
                   quality="{$attribute.content.quality}" pluginspage="{$attribute.content.pluginspage}"
                   {section show=$attribute.content.width|gt( 0 )}width="{$attribute.content.width}"{/section} {section show=$attribute.content.height|gt( 0 )}height="{$attribute.content.height}"{/section} play="{section show=$attribute.content.is_autoplay}true{/section}"
                   loop="{section show=$attribute.content.is_loop}true{/section}" name="objectid{$node.object.id}">
            </embed>
            </object>
        </div>

	</div>
  </div>
</div>
</div>
