{*?template charset=utf-8?*}
{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}
{def $link = openpaini( 'LinkSpeciali', 'ATeLaParolaDistrictID', '6542690' )}
{def $uri = openpaini( 'LinkSpeciali', 'DimmiTuttoUri', 'http://217.26.90.200/sms_portals/Default.aspx?portal=' )}

<iframe src="{concat( $uri, $link )}" width="100%" height="540px" frameborder="0" scrolling="no">
  Impossibile leggere la pagina come iframe... Si prega di connettersi alla pagina esterna http://www.sensorcivico.it/startup?districtId={$link}
</iframe>

