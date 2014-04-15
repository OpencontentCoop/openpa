{* Folder - Line view *}

{def $classi_con_immagine_inline = openpaini( 'GestioneClassi', 'classi_con_immagine_inline' )
     $classi_senza_immagine_inline = openpaini( 'GestioneClassi', 'classi_senza_immagine_inline' )}

{if is_set($show_image)}
	{def $show_icon_image=$show_image}
{else}
	{def $show_icon_image=''}
{/if}

<div class="content-view-line">
    <div class="class-folder">
    
    {if $show_icon_image|ne('nessuna')}
    {if $node.data_map.image.has_content}
        <div class="main-image left">{attribute_view_gui attribute=$node.data_map.image image_class='small'}</div>
    {/if}
    {/if}
	
	<div class="blocco-titolo-oggetto">
		<div class="titolo-blocco-titolo">
		        <h3><a href={$node.url_alias|ezurl}>{$node.name|wash()}</a></h3>
		</div>
        {if and( is_set( $node.data_map.abstract ), $node.data_map.abstract.has_content )}
        <div class="attribute-short">
            {attribute_view_gui attribute=$node.data_map.abstract}
        </div>
        {/if}
	</div>

    </div>
</div>
