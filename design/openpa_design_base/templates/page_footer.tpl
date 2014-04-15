    <div id="footer" class="width-layout">
    <div id="footer-design">
{if and( $ui_context|ne( 'edit' ), $ui_context|ne( 'browse' ) )} 
{*        
        <ul class="w3c-conformance">
            <li>
                <a title="Explanation of Level Double-A Conformance" href="http://www.w3.org/WAI/WCAG2AA-Conformance">
                    <img height="32" width="88" alt="Level Double-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 2.0" src={'validators/wcag2AA.png'|ezimage()} longdesc="http://www.w3.org/WAI/WCAG2AA-Conformance" />
                </a>
            </li>
            <li>
                <a title="Valid XHTML 1.1" href="http://validator.w3.org/check?uri=referer">
                    <img src={'validators/valid-xhtml11.png'|ezimage()} alt="Valid XHTML 1.1" height="31" width="88" longdesc="http://validator.w3.org" />
                </a>
            </li>
            <li>
                <a title="CSS Valido!" href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">
                    <img style="border:0;width:88px;height:31px" src={'validators/vcss.gif'|ezimage()} alt="CSS Valido!" longdesc="http://jigsaw.w3.org" />
                </a>
            </li>
            <li>
                <a title="Sito conforme agli standard" href="http://www.totalvalidator.com/validator/Revalidate?revalidate=true">
                    <img src={'validators/valid_n_xhtml_11.gif'|ezimage()} alt="Sito completamente conforme agli standard" />
                </a>
            </li>
            <li>
                <a title="Validatore accessibilit&agrave;" href="http://wave.webaim.org/report?url=http://{ezsys('hostname')}/">
                    <img src={'validators/wavelogo.jpg'|ezimage()} alt="Validatore accessibilit&agrave;" />
                </a>
            </li>
            <li>
                <a title="Validatore WCAG 2.0" href="http://www.tawdis.net/ingles.html?lang=en&amp;url=http://{ezsys('hostname')}#wcag2">
                    <img src={'validators/NombrePortal.jpg'|ezimage()} alt="Validatore WCAG 2.0" />
                </a>
            </li>
        </ul>
*}        
        
            
            {def $footer_notes = fetch( 'openpa', 'footer_notes' )}
            {if $footer_notes}
            <div class="block">{attribute_view_gui attribute=$footer_notes}</div>                
            {/if}
            
            {def $footer_links = fetch( 'openpa', 'footer_links' )}
            {if count( $footer_links )}                                
            <p class="footer-links">
                {foreach $footer_links as $item}
                
                {def $href = $item.url_alias|ezurl(no)}
                {if eq( $ui_context, 'browse' )}
                    {set $href = concat("content/browse/", $item.node_id)|ezurl(no)}
                {elseif $pagedata.is_edit}
                    {set $href = '#'}
                {elseif and( is_set( $item.data_map.location ), $item.data_map.location.has_content )}
                    {set $href = $item.data_map.location.content}                        
                {/if}
                
                <a href="{$href}" title="Leggi {$item.name|wash()}">{$item.name|wash()}</a>
                
                {undef $href}
                
                {delimiter} - {/delimiter}
                
                {/foreach}
            </p>                
            {/if}
            
            <small>
                powered by <a href="http://www.innovazione.comunitrentini.tn.it/Progetti/ComunWEB" title="Progetto ComunWEB - Consorzio dei Comuni Trentini">Consorzio dei Comuni Trentini</a>
                con il supporto di <a href="http://www.opencontent.it" title="OpenContent - Free Software Solutions">OpenContent Scarl</a>
            </small>
        
    </div>
    </div>
{/if}
</div>
</div>
