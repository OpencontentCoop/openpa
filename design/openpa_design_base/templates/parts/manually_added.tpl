{*
	BLOCCO DI RICERCA AUTOMATICA MORE LIKE THIS
	funziona solo con ezfind

	node_id		id del nodo su cui applicare la ricerca moreLikeThis
	title		titolo del box
	
	SE si vuole filtrare per classe:
	class_filter	identificatore di classe per cui filtrare

*}

{def 	$threshold=15 
	$class=fetch( 'content', 'class', hash( 'class_id', $class_filter ) )
}
{set-block variable=$open}
<div class="border-box">
<div class="border-content">
{/set-block}
{set-block variable=$close}
</div>
</div>
{/set-block}

{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}

<script type="text/javascript">
{literal}
$(function() {
        $("#{/literal}{concat('MoreLikeThis',$title)|slugize()}{literal}").accordion({ autoHeight: false });
});
{/literal}
</script>

{if $class}
	{def $related_content=fetch( 'ezfind', 'moreLikeThis', hash( 'query_type', 'nid',
                                                'query', $node_id,
						'class_id', $class.id,
                                                'limit', 6))}
{else}
	{def $related_content=fetch( 'ezfind', 'moreLikeThis', hash( 'query_type', 'nid',
                                                'query', $node_id,
                                                'limit', 6))}
{/if}

{if $related_content['SearchCount']|gt(0)}
{$open}
   {set $related_content=$related_content['SearchResult']}
   <div class="block-type-lista block-lista_accordion block-morelikethis">
	<h2 class="block-title">{$title|wash()}</h2>
        <div id="{concat('MoreLikeThis',$title)|slugize()}" class="ui-accordion">
                {foreach $related_content as $index => $child}
		{if $child.node_id|ne($node_id)}
                <div id="doc_{$child.name|slugize()}" class="border-box box-gray box-accordion ui-accordion-header {if $index|eq(0)}no-js-ui-state-active{/if}">
                        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
                        <div class="border-ml"><div class="border-mr"><div class="border-mc">
                        <div class="border-content">

                                <div class="attribute-small">
                                        <a href={$child.url_alias|ezurl()}>{concat($child.name)|wash()}</a>
                                </div>

                        </div>
                        </div></div></div>
                        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
                </div>
		<div id="doc_{$child.node_id|slugize()}-detail" class="border-box box-gray box-accordion ui-accordion-content {if $index|eq(0)}ui-accordion-content-active{/if} {if $index|gt(0)}no-js-hide{/if}">
                        <div class="border-ml"><div class="border-mr"><div class="border-mc">
                        	<div class="border-content">
					{node_view_gui content_node=$child view="simplified_line"}
                        	</div>
                        </div></div></div>
                        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>              
                </div>
		{/if}
                {/foreach}

        </div>

   </div>
{$close}
{/if}
