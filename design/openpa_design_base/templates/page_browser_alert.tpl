{def $is_depracated = false()
     $deprecated = openpaini( 'Accessibilita', 'BrowserDeprecato', array() )
     $deprecated_browsers = hash()
     $tmp = array()}

{foreach $deprecated as $d}
    {set $tmp = $d|explode( '|' )}
    {set $deprecated_browsers = $deprecated_browsers|merge( hash( $tmp[0], array( $tmp[1], $tmp[2] ) ) )}
{/foreach}

{foreach $deprecated_browsers as $brower_type => $compare}
    {if eq( $browser['browser_working'], $brower_type )}
        {switch match=$compare[0]}
            
            {case match='lt'}
                {if $browser['browser_number']|lt( $compare[1] )}
                    {set $is_depracated = true()}
                    {break}
                {/if}
            {/case}
    
            {case match='eq'}
                {if $browser['browser_number']|eq( $compare[1] )}
                    {set $is_depracated = true()}
                    {break}
                {/if}
            {/case}
    
            {case match='gt'}
                {if $browser['browser_number']|gt( $compare[1] )}
                    {set $is_depracated = true()}
                    {break}
                {/if}
            {/case}
    
            {case}
            {/case}
            
        {/switch}
    {/if}

{/foreach}


{if $is_depracated}
  <div style='border: 1px solid #F7941D; background: #FEEFDA; text-align: center; clear: both; height: 75px; position: relative;'>
    <div style='position: absolute; right: 3px; top: 3px; font-family: courier new; font-weight: bold;'><a href='#' onclick='javascript:this.parentNode.parentNode.style.display="none"; return false;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-cornerx.jpg' style='border: none;' alt='Close this notice'/></a></div>
    <div style='width: 640px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;'>
      <div style='width: 75px; float: left;'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-warning.jpg' alt='Warning!'/></div>
      <div style='width: 275px; float: left; font-family: Arial, sans-serif;'>
        <div style='font-size: 14px; font-weight: bold; margin-top: 12px;'>Stai usando un browser obsoleto</div>
        <div style='font-size: 12px; margin-top: 4px; line-height: 12px;'>Per una migliore e pi&ugrave; sicura navigazione, scegli un browser di ultima generazione.</div>
      </div>
      <div style='width: 75px; float: left;'><a href='http://www.mozilla-europe.org/it/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-firefox.jpg' style='border: none;' alt='Get Firefox 3.5'/></a></div>
      <div style='width: 75px; float: left;'><a href='http://www.microsoft.com/downloads/details.aspx?FamilyID=341c2ad5-8c3d-4347-8c03-08cdecd8852b&DisplayLang=it' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-ie8.jpg' style='border: none;' alt='Get Internet Explorer 8'/></a></div>
      <div style='width: 73px; float: left;'><a href='http://www.apple.com/it/safari/download/' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-safari.jpg' style='border: none;' alt='Get Safari 4'/></a></div>
      <div style='float: left;'><a href='http://www.google.com/chrome?hl=it' target='_blank'><img src='http://www.ie6nomore.com/files/theme/ie6nomore-chrome.jpg' style='border: none;' alt='Get Google Chrome'/></a></div>
    </div>
  </div>
{/if}

{undef $deprecated $deprecated_browsers $tmp $is_depracated}