    <div id="main-position">
      <div id="main" class="float-break">
		<div class="overflow-fix">
			{$module_result.content}
			{if or( $current_node_id|eq(ezini('NodeSettings','RootNode', 'content.ini')),
                    concat( 'content/view/full/', $current_node_id )|eq(ezini('SiteSettings','IndexPage')) )}
				{include uri='design:parts/banner_carousel.tpl'}
			{/if}
		</div>
	  </div>
    </div>