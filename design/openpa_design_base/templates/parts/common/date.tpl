{if is_set( $format )|not()}
    {def $format = array( 'custom', '%d %F %Y' )}
{/if}

{if is_set( $format[1] )|not()}
    {set $format = $format|append( false() )}
{/if}

{if is_set( $pre )|not()}
    {def $pre = ''}
{/if}

{if is_set( $post )|not()}
    {def $post = ''}
{/if}


{if openpaini( 'Classi', 'MostraData', array() )|contains( $node.class_identifier )}

    {switch match = $node.class_identifier}
        
        {case in = openpaini( 'Classi', 'NascondiData', array() )}
        {/case}
        
        {case match = 'event'}
        
            {$node.object.data_map.from_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
            {if and($node.object.data_map.to_time.has_content,  ne( $node.object.data_map.to_time.content.timestamp|datetime(custom,"%j %M"),
                $node.object.data_map.from_time.content.timestamp|datetime(custom,"%j %M") ))} - {$node.object.data_map.to_time.content.timestamp|datetime(custom,"%j %F")|shorten( 12 , '')}
            {/if}
        
        {/case}
        
        {case}
            {$pre}{$node.object.published|datetime( $format[0], $format[1] )}{$post}
        {/case}
    
    {/switch}

{/if}