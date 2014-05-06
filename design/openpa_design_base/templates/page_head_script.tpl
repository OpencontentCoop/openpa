{* Load JavaScript dependencys + JavaScriptList *}
{ezscript_load( ezini( 'JavaScriptSettings', 'JavaScriptList', 'design.ini' ) )}

<!--[if IE]>
{ezscript_load( array( 'jquery.placeholder.js' ) )}
{literal}<script type="text/javascript">$(function(){$('input, textarea').placeholder();});</script>{/literal}
<![endif]-->
