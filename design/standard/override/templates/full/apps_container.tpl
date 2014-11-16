{ezpagedata_set( 'extra_menu', false() )}
{ezpagedata_set( 'hide_valuation', true() )}

<div class="border-box">
<div class="border-content">

 <div class="global-view-full content-view-full">
  <div class="class-{$node.object.class_identifier}">

	<h1>{apps_root().name|wash()}</h1>
	
	<ul>
	{foreach apps_root().children as $item}
	  <li class="button">	
		<a href={$item.url_alias|ezurl()} title="{$item.name|wash()}">{$item.name|wash()}</a>	  
	  </li>
	{/foreach}
	</ul>

    </div>
</div>

</div>
</div>