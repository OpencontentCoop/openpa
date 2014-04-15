{*
	TEMPLATE  creazione commenti
	node	nodo di riferimento
*}

<div class="comments">
<div class="border-box box-trans-gray-3 box-comment">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">

	<h2>{"Comments"|i18n("design/ezwebin/full/article")}</h2>	
    {def $comments = fetch_alias( comments, hash( parent_node_id, $node.node_id ) )
         $comments_count = $comments|count()}
    
    {if $comments_count|gt(0)}				
    <div class="content-view-children">
        {foreach $comments as $comment}
            {node_view_gui view='full' content_node=$comment}
        {/foreach}
    </div>
    {/if}
				
    {if fetch( 'content', 'access', hash( 'access', 'create',
                                          'contentobject', $node,
                                          'contentclass_id', 'comment' ) )}
        <form method="post" action={"content/action"|ezurl}>
            <input type="hidden" name="ClassIdentifier" value="comment" />
            <input type="hidden" name="NodeID" value="{$node.object.main_node.node_id}" />
            <input type="hidden" name="ContentLanguageCode" value="{ezini( 'RegionalSettings', 'Locale', 'site.ini')}" />
            <input class="defaultbutton new_comment" type="submit" name="NewButton" value="Lascia un commento" />
        </form>
    {else}
        <a class="defaultbutton new_comment" href="/user/login">Accedi con il tuo utente per lasciare un commento</a>
    {/if}

</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>				
</div>
