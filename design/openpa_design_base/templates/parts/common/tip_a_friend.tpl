{def $tipafriend_access=fetch( 'user', 'has_access_to', hash( 'module', 'content', 'function', 'tipafriend' ) )}
{if and( ezmodule( 'content/tipafriend' ), $tipafriend_access )}
    <div class="attribute-tipafriend">
    <a href={concat( "/content/tipafriend/", $node.node_id )|ezurl} title="{'Tip a friend'|i18n( 'design/standard/content/tipafriend' )}">{'Tip a friend'|i18n( 'design/standard/content/tipafriend' )}</a>
    </div>
{/if}