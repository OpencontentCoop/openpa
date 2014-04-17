{* DO NOT EDIT THIS FILE! Use an override template instead. *}
{*?template charset=utf8?*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$site.http_equiv.Content-language|wash}" lang="{$site.http_equiv.Content-language|wash}">

<head>
{section name=JavaScript loop=ezini( 'JavaScriptSettings', 'JavaScriptList', 'design.ini' ) }
    <script language="JavaScript" type="text/javascript" src={concat( 'javascript/',$:item )|ezdesign}></script>
{/section}
	<link rel="stylesheet" type="text/css" href={"stylesheets/print.css"|ezdesign} />

{include uri="design:page_head.tpl" enable_print=false()}
</head>

<body>
<h1><a href={"/"|ezurl} title="{ezini('SiteSettings','SiteName')}">{ezini('SiteSettings','SiteName')}</a></h1>
{* Main area START *}

{include uri="design:page_mainarea.tpl"}

{* Main area END *}


<!--DEBUG_REPORT-->

</body>
</html>