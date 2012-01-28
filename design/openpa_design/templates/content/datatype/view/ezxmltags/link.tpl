{def $current_paramenter = module_params().parameters
     $current_node = false()
	 $areatematica = is_area_tematica()
	 $node_id = 0
	 $bottone = 0}

{if $href}
	{def $node_id_arr = $href|explode('/locale/')}
	{def $bottone_arr = $href|explode('/bottone/')}
	{if $node_id_arr|is_array()}
		{if count($node_id_arr)|gt(1)}
			{set $node_id = $node_id_arr[1]}
		{/if}	
	{/if}
	{if $bottone_arr|is_array()}
		{if count($bottone_arr)|gt(1)}
			{set $bottone = $bottone_arr[1]}
		{/if}
	{/if}
{/if}

{if not($title)}
{set $title=concat('Link a ', $content|strip_tags())}
{/if}

{if and( $areatematica, $node_id|gt(0) )} 
	<a href={concat('/', $current_node.url_alias,'/(reference)/',$node_id)} {if $id} id="{$id}"{/if}{if $title} title="{$title}"{/if} {if $classification} class="{$classification|wash}"{/if}{if and(is_set( $hreflang ), $hreflang)} hreflang="{$hreflang|wash}"{/if}>{$content}</a>
{elseif  $node_id|gt(0)} 
	<a href={concat('/',$current_node.url_alias)|ezurl}{if $id} id="{$id}"{/if}{if $title} title="{$title}"{/if} {if $classification} class="{$classification|wash}"{/if}{if and(is_set( $hreflang ), $hreflang)} hreflang="{$hreflang|wash}"{/if}>{$content}</a>
{elseif $bottone|gt(0)} 
	<div class="hover">
	<a href={$href|ezurl}{if $id} id="{$id}"{/if}{if $title} title="{$title}"{/if} {if $classification} class="{$classification|wash}"{/if}{if and(is_set( $hreflang ), $hreflang)} hreflang="{$hreflang|wash}"{/if}>{$content}</a>
	</div>
{else}
    {if not($href)}
        <a href={$current_node.url_alias|ezurl}{if $title} title="{$title}"{/if} {if $classification} class="{$classification|wash}"{/if}{if and(is_set( $hreflang ), $hreflang)} hreflang="{$hreflang|wash}"{/if}>{$content}</a>
    {else}
        <a href={$href|ezurl}{if $id} id="{$id}"{/if}{if $title} title="{$title}"{/if} {if $classification} class="{$classification|wash}"{/if}{if and(is_set( $hreflang ), $hreflang)} hreflang="{$hreflang|wash}"{/if}>{$content}</a>
    {/if}
{/if}