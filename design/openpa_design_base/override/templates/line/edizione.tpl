{* Edizione - Line view *}

<div class="content-view-line">
    <div class="class-file_pdf">

	<h2>
	{if $node.object.can_edit}
		<a href="{$node.url_alias|ezurl('no')}" title="{$node.name|wash()}">{$node.name|wash()}</a>
	{else}
		{$node.name|wash()}
	{/if}
	</h2>
	
		{if $node.data_map.to_time.has_content}
			{if eq( $node.data_map.to_time.content.timestamp|datetime(custom,"%j %M"), 
		   		$node.data_map.from_time.content.timestamp|datetime(custom,"%j %M") )}
				<div class="attribute-edizione-data">
					{$node.data_map.from_time.content.timestamp|datetime(custom,"%l %j %F")}; orario:  {$node.data_map.from_time.content.timestamp|datetime(custom,"%G:%i")} - {$node.data_map.to_time.content.timestamp|datetime(custom,"%G:%i")}
				</div>
			{else}
				<div class="attribute-edizionelunga-data">
					Da {$node.data_map.from_time.content.timestamp|datetime(custom,"%l %j %F")} a {$node.data_map.to_time.content.timestamp|datetime(custom,"%l %j %F")}
				</div>
			{/if}
		{else}
			<div class="attribute-edizione-data">
				{$node.data_map.from_time.content.timestamp|datetime(custom,"%l %j %F")}
			</div>
		{/if}
			

	{def $children=fetch('content', 'list', 
				   hash('parent_node_id', $node.node_id, 'sort_by', 'name',
                             		'class_filter_type', 'include', 'class_filter_array', array('edizione')) )}
 	{if $children|count()|gt(0)}
		<ul>
			{foreach $children as $figlio}
				<li>
					{$figlio.data_map.from_time.content.timestamp|datetime(custom,"%l %j %F")}; orario:  {$figlio.data_map.from_time.content.timestamp|datetime(custom,"%G:%i")} - {$figlio.data_map.to_time.content.timestamp|datetime(custom,"%G:%i")}
				</li>
                     	{/foreach}
		</ul>
	{/if}

    </div>
</div>
