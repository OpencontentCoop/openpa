<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it">
<head>
{set $current_user = fetch( 'user', 'current_user' )}
{def $user_hash = concat( $current_user.role_id_list|implode( ',' ), ',', $current_user.limited_assignment_value_list|implode( ',' ) )}

{if is_set( $extra_cache_key )|not}
    {def $extra_cache_key = ''}
{/if}

{def $browser          = checkbrowser('checkbrowser')
     $cookies          = check_and_set_cookies()
     $pagedata         = ezpagedata()
     $pagestyle        = $pagedata.css_classes
     $locales          = fetch( 'content', 'translation_list' )
     $pagedesign       = $pagedata.template_look
     $current_node_id  = $pagedata.node_id
     $main_style       = get_main_style()
     $custom_keys      = hash( 'browser', $browser, 'is_area_tematica', is_area_tematica() )|merge( $cookies )
}

{set scope=global custom_keys=$custom_keys}

{include uri='design:page_head.tpl'}
{include uri='design:page_head_style.tpl'}
{include uri='design:page_head_script.tpl'}

<style type="text/css" media="all">
/* <![CDATA[ */
#debug
{ldelim}
    display:none;
{rdelim}
/* ]]> */
</style>

</head>
<body class="no-js">
<script type="text/javascript">{literal}
//<![CDATA[
(function(){var c = document.body.className;
c = c.replace(/no-js/, 'js');
document.body.className = c;
})();
//]]>{/literal}
</script>

{include uri='design:page_browser_alert.tpl'}


{cache-block keys=array( $module_result.uri, $basket_is_empty, $current_user.contentobject_id, $extra_cache_key )}

<div id="page" class="{$pagestyle} {$main_style}">

    {if and( is_set( $pagedata.persistent_variable.extra_template_list ), $pagedata.persistent_variable.extra_template_list|count() )}
        {foreach $pagedata.persistent_variable.extra_template_list as $extra_template}
            {include uri=concat('design:extra/', $extra_template)}
        {/foreach}
    {/if}

    {include uri='design:page_header.tpl'}
  

{/cache-block}    



{cache-block keys=array( $module_result.uri, $basket_is_empty, $current_user.contentobject_id, $extra_cache_key )}
    <div id="columns-position" class="width-layout{if $pagedata.class_identifier|eq('frontpage')} frontpage{/if}">
    <div id="columns" class="float-break">
{/cache-block}

    {include uri='design:page_mainarea.tpl'}

{cache-block keys=array( $module_result.uri, $basket_is_empty, $current_user.contentobject_id, $extra_cache_key, $cookie_dimensione, $cookie_contrasto )}
    </div>
    </div>

{cache-block ignore_content_expiry}
    {include uri='design:page_footer.tpl'}
{/cache-block}

</div>

{include uri='design:page_footer_script.tpl'}

{/cache-block}

</body>
</html>
