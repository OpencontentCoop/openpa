{def $valid_node = $block.valid_nodes[0]}


<div class="block-type-lista block-lista_accordion block-{$block.view}">
	
	{if $block.name}
		<h2 class="block-title">
			{*<a href={$nodo.url_alias|ezurl()} title="Vai a {$block.name|wash()}">*}
			{$block.name}
			{*</a>*}
		</h2>
	{/if}
		
	<div id="{$nodo.name|slugize()}-{$block.id}" class="ui-accordion">	
		
		<div id="{$valid_node.name|slugize()}" class="border-box box-gray box-accordion>
			<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">
				
				<h3 class="attribute-small">
					{if $valid_node.class_identifier|eq('link')}
        					<a href={$valid_node.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">{$valid_node.name|wash()}</a>
					{else}
						<a href={$valid_node.url_alias|ezurl()}>{$valid_node.name|wash()}</a>
					{/if}
				</h3>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>
		
		<div id="{$valid_node.name|slugize()}-detail" class="border-box box-gray box-accordion>
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">						
				
				<div class="attribute-short">
					{if $valid_node.data_map.image.has_content}
					<div class="attribute-image">	
						{if $valid_node.class_identifier|eq('link')}
        						{attribute_view_gui attribute=$valid_node.data_map.image 
								href=$valid_node.data_map.location.content|ezurl() image_class=lista_accordion}
						{else}
							{attribute_view_gui attribute=$valid_node.data_map.image 
								href=$valid_node.url_alias|ezurl() image_class=lista_accordion}
						{/if}
					</div>
					{else}
					  <img class="image-medium" src={concat('icons/crystal/64x64/mimetypes/',$valid_node.class_identifier,'.png')|ezimage()} alt="{$valid_node.class_identifier}" title="{$valid_node.class_identifier}" />
					{/if}
					
									
					{if is_set($valid_node.data_map.abstract)}
						{if $valid_node.data_map.abstract.has_content}						
							{attribute_view_gui attribute=$valid_node.data_map.abstract}
						{/if}
					{elseif is_set($valid_node.data_map.oggetto)}
						{if $valid_node.data_map.oggetto.has_content}
							<div class="attribute-object">
								{attribute_view_gui attribute=$valid_node.data_map.oggetto}
							</div>
						{/if}
					{elseif is_set($node.data_map.testata)}
					   <div class="abstract-line">
					   {if $node.data_map.testata.has_content}
						<p>Tratto da: 
						<strong> {attribute_view_gui href=nolink attribute=$node.data_map.testata} </strong>
					   	   {if $node.data_map.pagina.content|ne(0)}a pag. {attribute_view_gui attribute=$node.data_map.pagina}
					        	{if $node.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui attribute=$node.data_map.pagina_continuazione}
							{/if}
					   	   {/if}
						   {if $node.data_map.autore.has_content}
			 				(di {attribute_view_gui attribute=$node.data_map.autore})
					    	   {/if}
						</p>
					    {/if}    
					    {if $node.data_map.argomento_articolo.has_content}
			 			<p>Su: 
						 <strong>
						 {attribute_view_gui href=nolink attribute=$node.data_map.argomento_articolo}
						 </strong>
						</p>
					    {/if}
					    </div>
					{else}
						<div class="attribute-node">
							{node_view_gui content_node=$valid_node view='line'}
						</div>
					{/if}
					{if $valid_node.class_identifier|eq('applicativo')}
						{attribute_view_gui attribute=$valid_node.data_map.location_applicativo}
					{/if}
				</div>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
		</div>
		
	</div>
	
</div>
	
