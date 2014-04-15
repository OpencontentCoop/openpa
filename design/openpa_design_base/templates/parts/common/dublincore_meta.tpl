{if is_set($node.data_map.dublincore_meta)}
 {if $node.data_map.dublincore_meta.has_content}
    {ezpagedata_set("dublincore_meta", $node.data_map.dublincore_meta.content)}
 {/if}
{/if}