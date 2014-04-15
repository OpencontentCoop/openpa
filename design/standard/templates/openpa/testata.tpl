<div class="block">
<h3>{$header.name|wash()} <a href={concat("content/edit/",$header.contentobject_id,"/f/ita-IT")|ezurl}><img class="button" src={"edit.gif"|ezimage} width="16" height="16" alt="Edit" /></a></h3>
<label>anteprima rimpicciolita</label>
{attribute_view_gui attribute=$header.data_map.image image_class=large}
<label>codice applicato</label>
<code>{fetch( 'openpa', 'header_banner_background_style' )}</code>
</div>

<div class="block">
<h3>{$logo.name|wash()} <a href={concat("content/edit/",$logo.contentobject_id,"/f/ita-IT")|ezurl}><img class="button" src={"edit.gif"|ezimage} width="16" height="16" alt="Edit" /></a></h3>
<label>anteprima rimpicciolita</label>
<div style="background:#ccc">{attribute_view_gui attribute=$logo.data_map.image image_class=medium}</div>
<label>codice applicato</label>
<code>{fetch( 'openpa', 'header_logo_background_style' )}</code>
</div>