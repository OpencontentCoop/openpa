{if $openpa.content_gallery.has_images}
  {if $openpa.content_gallery.has_single_images}
    <h2><i class="fa fa-camera"></i> {$openpa.content_gallery.title}</h2>
    {include uri='design:atoms/gallery.tpl' items=$openpa.content_gallery.images title=false()}
  {/if}
  
  {if $openpa.content_gallery.has_galleries}
    {foreach $openpa.content_gallery.galleries as $gallery}
      <h2><i class="fa fa-camera"></i> {$gallery.name|wash()}</h2>
      {include uri='design:atoms/gallery.tpl' items=fetch( content, list, hash( 'parent_node_id', $gallery.node_id, 'class_filter_type', 'include', 'class_filter_array', array( 'image' ), limit, 3)) title=false()}
      <small><a href="{$gallery.url_alias|ezurl(no)}" title="Visualizza tutta la galleria">Visualizza tutta la galleria</a></small>
    {/foreach}
  {/if}
{/if}
