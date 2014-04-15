{* set scope=global persistent_variable=hash('extra_menu', false()) *}

<div class="border-box">
<div class="border-mc float-break">

  <div class="global-view-full content-view-full">
   <div class="dashboard">

<div class="block">
{def $right_blocks = array()}

<div class="left">
{foreach $blocks as $block sequence array( 'left', 'right' ) as $position}
  
  {if $position|eq('left')}  
  <div class="dashboard-item">
    {if $block.template}
        {include uri=concat( 'design:', $block.template )}
    {else}
        {include uri=concat( 'design:dashboard/', $block.identifier, '.tpl' )}
    {/if}
  </div>
  {else}
	{append-block variable=$right_blocks}
    <div class="dashboard-item">
	    {if $block.template}
	        {include uri=concat( 'design:', $block.template )}
	    {else}
	        {include uri=concat( 'design:dashboard/', $block.identifier, '.tpl' )}
	    {/if}
	</div>
	{/append-block}
  {/if}
{/foreach}
</div>

<div class="right">
    {$right_blocks|implode('')}
</div>

</div>

	</div>
  </div>
</div>
</div>