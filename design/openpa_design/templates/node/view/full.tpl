{include name=menu_control node=$node uri='design:parts/common/menu_control.tpl'}

<div class="border-box">
<div class="border-content">

 <div class="global-view-full content-view-full">
  <div class="class-{$node.object.class_identifier}">

    <h1>{$node.name|wash()}</h1>
    
    {if is_set( $node.data_map.image )}
    {if $node.data_map.image.has_content}
        
        <div class="attribute-image text-center">
            <p>{attribute_view_gui attribute=$node.data_map.image image_class=imagelarge}</p>
        </div>

        {if is_set( $node.data_map.caption )}
        {if $node.data_map.caption.has_content}
        <div class="attribute-caption text-center">
            {attribute_view_gui attribute=$node.data_map.caption}
        </div>
        {/if}
        {/if}
        
    {/if}        
    {/if}
    
    <div class="content-view-attributes">
    {def $label = true()}
    {foreach $node.object.contentobject_attributes as $attribute sequence array( 'col-even', 'col-odd' ) as $style}
        {if openpaini( 'Attributi', 'FullAttributiDaEscludere', array() )|merge( openpaini( 'Attributi', concat( 'FullAttributiDaEscludere_', $node.class_identifier), array() ) )|contains($attribute.contentclass_attribute_identifier)|not()}
            {if $attribute.has_content}

                {if openpaini( 'Attributi', 'FullAttributiSenzaLabel', array() )|merge( openpaini( 'Attributi', concat( 'FullAttributiDaEscludere_', $node.class_identifier), array() ) )|contains($attribute.contentclass_attribute_identifier)}
                    {set $label = false()}
                {else}
                    {set $label = true()}
                {/if}
                
                
                <div class="{$style} col float-break attribute-{$attribute.contentclass_attribute_identifier}{if $label|not()} col-notitle{/if}">
                    {if $label}
                    <div class="col-title"><span class="label">{$attribute.contentclass_attribute_name}</span></div>
                    {/if}
                    <div class="col-content"><div class="col-content-design">
                    {attribute_view_gui attribute=$attribute}
                    </div></div>
                </div>
                
            {/if}
        {/if}
    {/foreach}
    </div>

    {def $page_limit = 10
         $classes = array()
         $children_count = ''}
    
    {if openpaini( 'Classi', 'FullRiquadroModuli', false() )}
    {set $classes = openpaini( 'Classi', 'FullRiquadroModuli', false() )
         $children_count=fetch_alias( 'children_count', hash( 'parent_node_id', $node.node_id,
                                                              'class_filter_type', 'include',
                                                              'class_filter_array', $classes ) )}

    
    {if $children_count}
    <div class="content-view-children-modulistica">    
        <div class="border-header border-box box-violet-gray box-allegati-header">
            <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
            <div class="border-content">
                <h2>Moduli e Allegati</h2>
            </div>
            </div></div></div>
        </div>
        <div class="border-body border-box box-violet box-allegati-content">
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
            <div class="border-content">  
    
            {foreach fetch_alias( 'children', hash( 'parent_node_id', $node.node_id,
                                                    'offset', $view_parameters.offset,
                                                    'sort_by', array( 'name', true() ),
                                                    'class_filter_type', 'include',
                                                    'class_filter_array', $classes
                                                    ) ) as $child sequence array( 'col-even', 'col-odd' ) as $style}
                
                <div class="{$style} col float-break col-notitle">
                    <div class="col-content"><div class="col-content-design">
                        <h3>{$child.name|wash()}</h3>
                        {attribute_view_gui attribute=$child.data_map.file}
                    </div></div>
                </div>
            {/foreach}
            
            </div>
            </div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
        </div>	
    </div>
    {/if}
    {/if}

    
    {set $page_limit = 20
         $classes = openpaini( 'Classi', 'FullFigliDaEscludereDefault', array() )|merge( openpaini( 'Classi', 'FullRiquadroModuli', array() ) )
         $children_count = ''}

    {set $children_count=fetch_alias( 'children_count', hash( 'parent_node_id', $node.node_id,
                                                              'class_filter_type', 'exclude',
                                                              'class_filter_array', $classes ) )}

    
    {if $children_count}
    <div class="content-view-children">
        {foreach fetch_alias( 'children', hash( 'parent_node_id', $node.node_id,
                                                'offset', $view_parameters.offset,
                                                'sort_by', $node.sort_array,
                                                'class_filter_type', 'exclude',
                                                'class_filter_array', $classes,
                                                'limit', $page_limit ) ) as $child sequence array( 'col-even', 'col-odd' ) as $style}
            {node_view_gui style=$style view='line' content_node=$child}
        {/foreach}
    </div>
    

    {include name=navigator
             uri='design:navigator/google.tpl'
             page_uri=$node.url_alias
             item_count=$children_count
             view_parameters=$view_parameters
             item_limit=$page_limit}
    {/if}
    
    {include name=menu_control node=$node uri='design:parts/common/social_control.tpl'}

    </div>
</div>

</div>
</div>