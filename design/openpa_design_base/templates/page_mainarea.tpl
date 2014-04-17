    <div id="main-position">
      <div id="main" class="float-break">
		<div class="overflow-fix">
			{$module_result.content}
			{if and( is_set( $module_result.node_id ),
                     or( $module_result.node_id|eq(ezini('NodeSettings','RootNode', 'content.ini')),
                         concat( 'content/view/full/', $module_result.node_id )|eq(ezini('SiteSettings','IndexPage')) ) )}
				{cache-block keys=array( $module_result.uri, $user_hash, $extra_cache_key )}
                    {include uri='design:parts/banner_carousel.tpl'}
                {/cache-block}
			{/if}
		</div>
	  </div>
    </div>