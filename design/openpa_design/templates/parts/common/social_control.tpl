<div class="attribute-social float-break">
    
    <div class="left">
    
    {def $tipafriend_access=fetch( 'user', 'has_access_to', hash( 'module', 'content',
                                                                      'function', 'tipafriend' ) )}
    {if and( ezmodule( 'content/tipafriend' ), $tipafriend_access )}
        <div class="attribute-tipafriend">
            <p><a href={concat( "/content/tipafriend/", $node.node_id )|ezurl} title="{'Tip a friend'|i18n( 'design/ezwebin/full/article' )}">{'Tip a friend'|i18n( 'design/ezwebin/full/article' )}</a></p>
        </div>
    {/if}
    {*
        <div class="attribute-print">
            <p><a href="javascript:window.print()" title="Stampa la pagina corrente">Versione stampabile</a></p>
        </div>
    *}
    
    {def $rss_export = fetch( 'rss', 'export_by_node', hash( 'node_id', $node.node_id ) )}
    {if $rss_export}
        <div class="attribute-rss">
            <p><a href="{concat( '/rss/feed/', $rss_export.access_url )|ezurl( 'no' )}" title="{$rss_export.title|wash()}">RSS {$rss_export.title|wash()}</a></p>
        </div>
    {/if}
    
    
     <!-- AddThis Button BEGIN -->
    <div class="addthis_toolbox addthis_default_style ">
    <a class="addthis_button_preferred_1"></a>
    <a class="addthis_button_preferred_2"></a>
    <a class="addthis_button_preferred_3"></a>
    <a class="addthis_button_preferred_4"></a>
    <a class="addthis_button_compact"></a>
    <a class="addthis_counter addthis_bubble_style"></a>
    </div>
    <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4f1466e1721a5c5b"></script>
    <!-- AddThis Button END -->
    
    
    </div>
    
    {if is_set( $node.data_map.star_rating )}
        <div class="attribute-star_rating right">
            <strong>{$node.data_map.star_rating.contentclass_attribute_name}:</strong>
            {attribute_view_gui attribute=$node.data_map.star_rating}
        </div>
    {/if}
    

</div>