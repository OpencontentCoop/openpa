{def $children                  = $block.valid_nodes
     $nodo                  	= $children[0]
     $classi_senza_data_inline 	= openpaini( 'GestioneClassi', 'classi_senza_data_inline', array())
     $classi_con_data_inline 	= openpaini( 'GestioneClassi', 'classi_con_data_inline', array())
}

{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}

<script type="text/javascript">
{literal}
$(function() {
	$("#{/literal}{$nodo.name|slugize()}-{$block.id}{literal}").accordion({ 
		autoHeight: false,
		event: "mouseover",
		change: function(event, ui) { 
			$('a', ui.newHeader ).addClass('active'); 
			$('a', ui.oldHeader ).removeClass('active');  
		}
	}); 
});
{/literal}
</script>

<div class="block-type-lista block-lista_accordion block-{$block.view}">
	
	{if $block.name}
		<h2 class="block-title">
			{$block.name}
		</h2>
	{/if}
		
	<div id="{$nodo.name|slugize()}-{$block.id}" class="ui-accordion">	
		{foreach $children as $index => $child}
		
		<div id="{$child.name|slugize()}" class="border-box box-gray box-accordion ui-accordion-header {if $index|eq(0)}no-js-ui-state-active{/if}">
			<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">
				
				<h3 class="attribute-small">
					{if $child.class_identifier|eq('link')}
        				<a href={$child.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">{$child.name|wash()}</a>
					{else}
						<a{if $index|eq(0)} class="active"{/if} href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
					{/if}
				</h3>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
		</div>
		
		<div id="{$child.name|slugize()}-detail" class="border-box box-gray box-accordion ui-accordion-content {if $index|eq(0)}ui-accordion-content-active{/if} {if $index|gt(0)}no-js-hide{/if}">
			<div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">						
				
				<div class="attribute-short {if $index|gt(0)}no-js-hide{/if}">
					{if and( is_set( $child.data_map.image ), $child.data_map.image.has_content )}
					<div class="attribute-image">	
						{if $child.class_identifier|eq('link')}
        					{attribute_view_gui attribute=$child.data_map.image  href=$child.data_map.location.content|ezurl() image_class=lista_accordion}
						{else}
							{attribute_view_gui attribute=$child.data_map.image href=$child.url_alias|ezurl() image_class=lista_accordion}
						{/if}
					</div>
					{else}
                        {include node=$child uri='design:parts/common/class_icon.tpl' css_class="image-medium"}           
					{/if}
					
					{if $classi_con_data_inline|contains($child.class_identifier)}
						di {$child.object.published|l10n(date)}
					{/if}
										
					{if and( is_set($child.data_map.oggetto), $child.data_map.oggetto.has_content)}
                        <div class="attribute-object">
                            {attribute_view_gui attribute=$child.data_map.oggetto}
                        </div>

                    {elseif is_set( $child.data_map.testata )}
                        <div class="abstract-line">
                            {if $child.data_map.testata.has_content}
                            <p>
                                Tratto da: 
                                <strong> {attribute_view_gui href=nolink attribute=$child.data_map.testata} </strong>
                                {if $child.data_map.pagina.content|ne(0)}
                                    a pag. {attribute_view_gui attribute=$child.data_map.pagina}
                                    {if $child.data_map.pagina_continuazione.content|ne(0)}
                                        e {attribute_view_gui attribute=$child.data_map.pagina_continuazione}
                                    {/if}
                                {/if}
                                {if $child.data_map.autore.has_content}
                                    (di {attribute_view_gui attribute=$child.data_map.autore})
                                {/if}
                            </p>
                            {/if}
                            
                            {if $child.data_map.argomento_articolo.has_content}
                            <p>
                                Su: 
                                <strong>{attribute_view_gui href=nolink attribute=$child.data_map.argomento_articolo}</strong>
                            </p>
                            {/if}
                        </div>
                    
                    {elseif and( is_set( $child.data_map.abstract ), $child.data_map.abstract.has_content )}
                        {attribute_view_gui attribute=$child.data_map.abstract}
					{elseif and( is_set( $child.data_map.short_description ), $child.data_map.short_description.has_content )}
                            {attribute_view_gui attribute=$child.data_map.short_description}
					{elseif $child|has_abstract()}
						{$child|abstract()|openpa_shorten(450)}						
					{/if}
                    
					{if $child.class_identifier|eq('applicativo')}
						{attribute_view_gui attribute=$child.data_map.location_applicativo}
					{/if}
				</div>
				
			</div>
			</div></div></div>
			<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
		</div>
		
		{/foreach}
		
	</div>
	
</div>