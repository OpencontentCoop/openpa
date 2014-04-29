{if openpaini( 'GestioneClassi', 'MostraIcone', 'enabled' )|eq('enabled')}
{def $_icon = 'empty'}
{if is_set( $node )}
    {set $_icon = $node.class_identifier}
{elseif is_set( $class_identifier )}
    {set $_icon = $class_identifier}
{/if}

{def $default_image_path = 'icons/crystal/64x64/mimetypes/empty.png'|ezimage(no)
     $class_path = concat( 'icons/crystal/64x64/mimetypes/', $_icon, '.png' )|ezimage(no,true())}
{if file_exists( concat( ezsys( sitedir ),  $class_path ) )}
    {set $default_image_path = concat( 'icons/crystal/64x64/mimetypes/', $_icon, '.png' )|ezimage(no)}
{/if}
<img class="{if is_set($css_class)}{$css_class}{/if}" {if is_set($height)}height={$height}{/if} {if is_set($width)}width={$width}{/if} src="{$default_image_path}" alt="{$_icon}" title="{if is_set($node)}{$node.object.class_name}{else}{$_icon}{/if}" />
{undef $_icon}
{/if}