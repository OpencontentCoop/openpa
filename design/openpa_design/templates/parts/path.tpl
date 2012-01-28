  <!-- Path content: START -->
  <h2 class="hide">Ti trovi in:</h2>
  <p>

  {if and( is_set($module_result.content_info), $module_result.content_info.class_identifier|eq('user') )}
	   
       {def $localnode=fetch(content, node, hash(node_id,92236))
            $nodo_utente=fetch(content, node, hash(node_id,$module_result.content_info.node_id))
            $paths=$localnode.path}

	{foreach $paths as $path} 
		<a href={$path.url_alias|ezurl}>
			{if $path.node_id|eq("2")}
				Home
			{else}
				{$path.name|wash}
			{/if}
  		</a>
  	   <span class="path-separator">&raquo;</span>
	{/foreach}
	<a href={$localnode.url_alias|ezurl}>{$localnode.name|wash}</a>
	<span class="path-separator">&raquo;</span>
	<span class="path-text"> {$nodo_utente.name|wash} </span>
	
  {else}

  	{foreach $pagedata.path_array as $path}
  		{if $path.url}
			<a href={cond( is_set( $path.url_alias ), $path.url_alias, $path.url )|ezurl}>
				{if $path.node_id|eq("2")}
					Home
				{else}
					{$path.text|wash}
				{/if}
			</a>
			 <span class="path-separator">&raquo;</span>
		  {else} 
			<span class="path-text"> {$path.text|wash} </span>	
		  {/if}
	{/foreach}

  {/if}

  </p>
  <!-- Path content: END -->
