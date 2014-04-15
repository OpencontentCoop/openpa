	{default attribute_parameters=array()}
	{section show=$object.main_node_id|null|not}
	    <a href={$incorporated_link|ezurl}>{$object.name|wash}</a>
	{section-else}
	    {$object.name|wash}
	{/section}
	{/default}
