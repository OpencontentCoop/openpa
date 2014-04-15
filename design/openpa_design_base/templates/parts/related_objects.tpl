{if is_set( $view )|not()}
    {def $view = 'simplified_line'}
{/if}


{* la clausola array( 'class_name', true() ) in sort_by genera un errore Postgres nella query di eZContentObject#2950 *}
{def $has_content = false()
     $correlati = array()
     $related = fetch( 'content', 'related_objects', hash( 'object_id', $node.object.id,
                                                           'all_relations', array( 'common' ),
                                                           'load_data_map',  false(),
                                                           'sort_by', array( array( 'name', true() ) ) ))}
                                                           
{foreach $related as $rel}
    {if and( $oggetti_correlati|contains( $rel.class_identifier ), $rel.can_read )}
        {set $correlati = $correlati|append( $rel )
             $has_content = true()}        
    {/if}
{/foreach}

{if $has_content}
<div class="oggetti-correlati">
    <div class="border-header border-box box-trans-blue box-allegati-header">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
            <h2>{$title}</h2>
        </div>
        </div></div></div>
    </div>
    <div class="border-body border-box box-violet box-allegati-content">
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
            {foreach $correlati as $object sequence array( 'col-even', 'col-odd' ) as $style}
                <div class="{$style} col col-notitle float-break">                    
                    <div class="col-content"><div class="col-content-design">								                    
                        {node_view_gui content_node=$object.main_node view=$view show_image='nessuna'}                    
                    </div></div>
				</div>	
            {/foreach}
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
    </div>
</div>
{/if}