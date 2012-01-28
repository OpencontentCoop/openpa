    <div id="main-position">
      <div id="main" class="float-break">
		<div class="overflow-fix">
			{$module_result.content}
			{if $current_node_id|eq(2)}
				{include uri='design:parts/banner_carousel.tpl'}
			{/if}
		</div>
	  </div>
    </div>