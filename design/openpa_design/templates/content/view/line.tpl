{default node_name=$object.main_node.name node_url=$object.main_node.url_alias}
{section show=$node_url}<a href={$node_url|ezurl}>{/section}{$node_name|wash}{section show=$node_url}</a>{/section}
{/default}