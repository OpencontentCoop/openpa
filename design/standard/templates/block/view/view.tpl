{def $source_node = false()
     $valid_nodes = array()
     $shorten = 300
     $style = false()}
{if and( is_set( $block.custom_attributes ),  is_set( $block.custom_attributes.node_id ) )}
    {def $source = $block.custom_attributes.node_id
         $limit = cond( is_set( $block.custom_attributes.limite ), $block.custom_attributes.limite, 10)
         $filter = cond( is_set( $block.custom_attributes.classi ),
                                                        hash(
                                                            'class_filter_type', 'include',
                                                            'class_filter_array', $block.custom_attributes.classi|explode(',')
                                                        ),
                                                        hash() )
        $sort_by = hash( 'sort_by', array('published', false()) )}
    {if $source}
        
        {set $source_node = fetch( 'content', 'node', hash( 'node_id', $source ) )}
        
        {if $source_node.class_identifier|eq( 'event_calendar' )}
            {set $sort_by = hash( 'sort_by', array( 'attribute', true(), 'event/from_time') )}
        {else}
            {set $sort_by = hash( 'sort_by', $source_node.sort_array)}
        {/if}
        
        {set $valid_nodes = fetch( 'content', 'list', hash( 'parent_node_id', $source, 
                                                            'limit', $limit
                                                         )|merge( $filter, $sort_by ) )
             $style = has_main_style( $source )}
    {/if}
{else}
    {set $valid_nodes = $block.valid_nodes}
{/if}

<div class="content-view-block block-type-lista block-default">

	{if $block.name}
		<h2 class="block-title"><span class="{$style}">{$block.name}</a></h2>
    {else}
        <h2 class="block-title">Altre informazioni</h2>
	{/if}
	    
	<ul>
        {foreach $valid_nodes as $node}
        <li><h3 class="attribute-small">{$node.class_identifier|class_icon( small, $node.class_name )} <a href={$node.object.main_node.url_alias|ezurl()} title="Vai a {$node.name|wash()}">{$node.name|wash()}</a></h3></li>
        {/foreach}
    </ul>

{if $source_node}
    <div class="block-link">
        <a href={$source_node.url_alias|ezurl()} title="Vai a {$source_node.name|wash()}">Vai a <span>{$source_node.name|wash()}</span></a>
    </div>
{/if}

</div>
{undef}