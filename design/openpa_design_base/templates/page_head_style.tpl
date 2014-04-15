{if  $custom_keys.contrasto|eq('alto') }

      {ezcss_load( array( 'altocontrasto.css',
						  'debug.css',
						  'websitetoolbar.css'), 'screen' ) }

{else}

	{* DEFINIZIONE DELLO STILE PERSONALIZZATO DELLE AREE TEMATICHE *}
	{def $area_tematica_css = get_area_tematica_style()}

	{* DEFINIZIONE DELLO STILE PERSONALIZZATO DEI FORM DI EDIT DI FRONTEND *}
	{def $style_edit_css = false()}
	{if or( $pagedata.is_edit, eq($ui_context, 'browse') )}
		{set $style_edit_css = 'style_edit.css'}
	{/if}

	{if is_unset( $load_css_file_list )}
		{def $load_css_file_list = true()}
	{/if}
	{if $load_css_file_list}
	  {ezcss_load( array( 'core.css',
						  'debug.css',
						  'pagelayout.css',
						  'content.css',
						  'websitetoolbar.css',
						  ezini( 'StylesheetSettings', 'CSSFileList', 'design.ini' ),
						  $area_tematica_css,
						  $style_edit_css,
                          'custom.css' ), 'screen' ) }
	{else}
	  {ezcss_load( array( 'core.css',
						  'debug.css',
						  'pagelayout.css',
						  'content.css',
						  'websitetoolbar.css',
						  $area_tematica_css,
						  $style_edit_css,
                          'custom.css' ), 'screen' ) }
	{/if}

	<link rel="stylesheet" type="text/css" href={"stylesheets/print.css"|ezdesign} media="print" />
	<!--[if IE 5]>     <style type="text/css"> @import url({"stylesheets/browsers/ie5.css"|ezdesign(no)});    </style> <![endif]-->
	<!--[if lte IE 6]> <style type="text/css"> @import url({"stylesheets/browsers/ie6lte.css"|ezdesign(no)}); </style> <![endif]-->
	<!--[if lte IE 7]> <style type="text/css"> @import url({"stylesheets/browsers/ie7lte.css"|ezdesign(no)}); </style> <![endif]-->
	<!--[if IE]> <style type="text/css"> @import url({"stylesheets/browsers/ie.css"|ezdesign(no)}); </style> <![endif]-->

{/if}

{* DEFINIZIONI INLINE DELLA DIMENSIONE DEI CARATTERI IN BASE AL COOKIE dimensione *}
{if  $custom_keys.dimensione|eq('grande') }
	{literal}
	<style type="text/css">
	<!--
	body { font-size: 1em; }
	-->
	</style>
	{/literal}
{else}
	{literal}
	<style type="text/css">
	<!--
	body { font-size: 0.8em; }
	-->
	</style>
	{/literal}
{/if}

{* DEFINIZIONI INLINE DEL LAYOUT IN BASE AL COOKIE layout *}
{if  $custom_keys.layout|eq('fluido') }
	{literal}
	<style type="text/css">
	<!--
	div.width-layout{width:100%}
	-->
	</style>
	{/literal}
{/if}
