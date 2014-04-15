 <div class="class-{$node.class_identifier} float-break">

    
    {if and( is_set( $node.data_map.short_title ), $node.data_map.short_title.has_content )}
        <div class="float-break">
        <h2>{attribute_view_gui attribute=$node.data_map.short_title}</h2>
        </div>
    {/if}
 
    {if $node.data_map.image.has_content}
        <div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
    {/if}

	<div class="blocco-titolo-oggetto">    
 		<div class="titolo-blocco-titolo">
            <p>
            {if is_set( $node.url_alias )}
                <strong><a href={if $node.class_identifier|eq('area_tematica')}{$node.object.main_node.url_alias|ezurl}{else}{$node.url_alias|ezurl('no')}{/if} title="{$node.name|wash()}">{$node.name|wash()}</a></strong>
            {else}
                <strong>{$node.name|wash()}</strong>
            {/if}
            </p>
			
		</div>
		
        {if is_set($node.data_map.short_description)}
            {if $node.data_map.short_description.has_content}
                <div class="abstract-line">
                    {attribute_view_gui attribute=$node.data_map.short_description}
                </div>
            {/if}				
        {/if}


	</div>
 </div>
