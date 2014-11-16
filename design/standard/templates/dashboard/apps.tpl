{def $root = apps_root()}
{if $root}
<h2>{$root.name|wash()}</h2>

<ul>
{foreach $root.children as $item}
  <li class="button">	
	<a href={$item.url_alias|ezurl()} title="{$item.name|wash()}">{$item.name|wash()}</a>	  
  </li>
{/foreach}
</ul>
{/if}