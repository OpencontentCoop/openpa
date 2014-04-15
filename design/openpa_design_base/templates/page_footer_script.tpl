{if $pagedesign.data_map.footer_script.has_content}
<script language="javascript" type="text/javascript">
<!--

    {$pagedesign.data_map.footer_script.content}

-->
</script>
{/if}

{if openpaini( 'Seo', 'GoogleAnalyticsAccountID', false() )}
<script type="text/javascript">
<!--
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '{openpaini( 'Seo', 'GoogleAnalyticsAccountID' )}']);
    _gaq.push(['_trackPageview']);
    
    (function() {ldelim}
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    {rdelim})();
-->
</script>
{/if}
