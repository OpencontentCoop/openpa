{def $tmp = $attribute.data_text
     $link = $tmp|explode( '?access_token' )|implode( 'embed/?access_token' )}
     
<iframe style="border:0px;padding:0px;margin:0px;" width="100%" height="343" src="{$link}" frameborder="0" scrolling="no" allowfullscreen></iframe>