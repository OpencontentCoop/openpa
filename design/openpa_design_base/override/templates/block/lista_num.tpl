{def $customs=$block.custom_attributes $errors=false()
     $sort_array=array() 
     $classes=array()
     $classi_con_data_inline = openpaini( 'GestioneClassi', 'classi_con_data_inline', array())
     $classi_senza_data_inline = openpaini( 'GestioneClassi', 'classi_senza_data_inline', array())
     $classi_blocco_particolari = openpaini( 'GestioneClassi', 'classi_blocco_particolari', array())
     $classi_senza_correlazioni_inline = openpaini( 'GestioneClassi', 'classi_senza_correlazioni_inline', array())
     $attributes_to_show=array('organo_competente', 'circoscrizione')
     $attributes_with_title=array('servizio','argomento','ricevimento')
     $curr_ts = currentdate()	
     $ruolo=false()     
}

{if $customs.limite|gt(0)}
    {def $limit=$customs.limite}
{else}
    {def $limit=10}
{/if}

{if $customs.livello_profondita|eq('')}
    {def $depth=10}
{else}
    {def $depth=$customs.livello_profondita}
{/if}

{def $custom_node = fetch( 'content', 'node', hash( 'node_id', $customs.node_id ))}

{if $custom_node}
    
    {switch match=$customs.ordinamento}
        {case match=''}
            {set $sort_array=$custom_node.sort_array}
        {/case}
        {case match='priorita'}
            {set $sort_array=array('priority', true())}
        {/case}
        {case match='pubblicato'}
            {set $sort_array=array('published', false())}
        {/case}
        {case match='modificato'}
            {set $sort_array=array('modified', false())}
        {/case}
        {case match='nome'}
            {set $sort_array=array('name', true())}
        {/case}
        {case}{/case}
    {/switch}
    
    {* se la sorgente è virtualizzata restituisce i risultati della virtualizzazione *}
    {if and( is_set( $custom_node.data_map.classi_filtro ), $custom_node.data_map.classi_filtro.has_content )}        
        {set $classes = $custom_node.data_map.classi_filtro.content|explode(',')}
        {def $virtual_classes = array()
             $virtual_subtree = array()}
        {foreach $classes as $class}
            {set $virtual_classes = $virtual_classes|append( $class|trim() )}
        {/foreach}
        {if $custom_node.data_map.subfolders.has_content}
            {foreach $custom_node.data_map.subfolders.content.relation_list as $relation}
                {set $virtual_subtree = $virtual_subtree|append( $relation.node_id )}
            {/foreach}
        {else}
            {set $virtual_subtree = array( ezini( 'NodeSettings', 'RootNode', 'content.ini' ) )}
        {/if}
        
        {switch match=$customs.ordinamento}
        {case match='priorita'}
            {set $sort_array=hash('priority', 'asc')}
        {/case}
        {case match='pubblicato'}
            {set $sort_array=hash('published', 'desc' )}
        {/case}
        {case match='modificato'}
            {set $sort_array=hash('modified', 'desc')}
        {/case}
        {case match='nome'}
            {set $sort_array=hash('name', 'asc')}
        {/case}            
        {case}
            {set $sort_array=hash('published', 'desc' )}
        {/case}
        {/switch}
        
        {def $search_hash = hash( 'subtree_array', $virtual_subtree,                                  
                                  'limit', $limit,
                                  'class_id', $virtual_classes,
                                  'sort_by', $sort_array
                                  )
             $search = fetch( ezfind, search, $search_hash )             
             $children_count = $search['SearchCount']}
    {/if}
    
    {if and( is_set( $children_count ), $children_count|gt(0) )}
        {def $children = $search['SearchResult']}
    {elseif $customs.escludi_classi|ne('')}
        {set $classes=$customs.escludi_classi|explode(',')}
        {set $classes = merge($classes, openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow' )) }
        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                        'class_filter_type', 'exclude',
                                                        'class_filter_array', $classes,
                                                        'depth', $depth,
                                                        'limit', $limit,
                                                        'sort_by', $sort_array) )}
    {elseif $customs.includi_classi|ne('')}
        {if $customs.includi_classi|eq('news')}
            {set $classes=$customs.includi_classi|explode(',')}
            {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                            'attribute_filter', array( 'and',
                                                                array( 'news/data_inizio_pubblicazione_news', '<=', $curr_ts ),
                                                                array( 'news/data_fine_pubblicazione_news', '>=', $curr_ts  ) ),
                                                            'class_filter_type', 'include',
                                                            'class_filter_array', $classes,
                                                            'depth', $depth,
                                                            'limit', $limit,
                                                            'sort_by', $sort_array) )}
             
        {else} 
            {set $classes=$customs.includi_classi|explode(',')}
            {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                            'class_filter_type', 'include',
                                                            'class_filter_array', $classes,
                                                            'depth', $depth,
                                                            'limit', $limit,
                                                            'sort_by', $sort_array) )}
        {/if}
    {else}
        {set $classes = openpaini( 'GestioneClassi', 'classi_da_escludere_dai_blocchi_ezflow' )}
        {def $children=fetch( 'content', 'tree', hash( 'parent_node_id', $customs.node_id,
                                                        'class_filter_type', 'exclude',
                                                        'class_filter_array', $classes,
                                                        'depth', $depth,
                                                        'limit', $limit,
                                                        'sort_by', $sort_array) )}
    {/if}
    
    
    {ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}
    
    <script type="text/javascript">
    {literal}
    $(function() {
        $("#{/literal}x{$block.id}{literal}").tabs().tabs("rotate", 4000);
        var rotation = true;
        $(".rotation-control").bind('click', function() {
            if (rotation){
                $(this).parent().tabs("rotate", 0);
                rotation = false;
                $(this).removeClass('started');
                $(this).addClass('stopped');
            }else{
                $(this).parent().tabs("rotate", 4000);
                rotation = true;
                $(this).removeClass('stopped');
                $(this).addClass('started');
            }
        });
    });
    {/literal}
    </script>
    
    <div class="block-type-lista block-{$block.view}">
    
        {if $block.name}
            <h2 class="block-title">
                <a href={$custom_node.url_alias|ezurl()} title="Vai a {$block.name|wash()}">{$block.name}</a>
            </h2>
        {/if}
    
        <div class="border-box box-gray box-numeri">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
        
            <div id="x{$block.id}" class="ui-tabs">	
                        
                <div class="num-tabs-panels">
                {foreach $children as $index => $child}
                    <div id="el{$block.id}-{$child.name|slugize()}-{$child.node_id}" class="{if $index|ge(3)}no-js-hide {/if}ui-tabs-hide">
    
                    {if $child.class_identifier|eq('news')} 
                        <div class="attribute-header">
                            <h3>
                               <a{if $index|eq(0)} class="active"{/if} href={$child.parent.url_alias|ezurl()} title="{$child.parent.name|wash()}">{$child.parent.name|wash()}</a>
                            </h3>
                        </div>
                        
                        <div class="attribute-small">	
                        {if $classi_con_data_inline|contains($child.class_identifier)}
                            di {$child.object.published|l10n(date)}
                        {/if}
                        </div>
                        
                        {if and( is_set( $child.parent.data_map.image), $child.parent.data_map.image.has_content )}
                            <div class="attribute-image no-js-hide">
                                {attribute_view_gui attribute=$child.parent.data_map.image image_class=lista_num}
                            </div>
                        {else}
                            {include node=$child.parent uri='design:parts/common/class_icon.tpl' css_class="image-medium"}
                        {/if}
                        
                        <div class="no-js-hide">
                        {if and( is_set($child.data_map.testo_news), $child.data_map.testo_news.has_content )}
                            {attribute_view_gui attribute=$child.data_map.testo_news}
                        {/if}
                        </div>
    
                        {* mostro gli altri attributi *}
                        {foreach $child.parent.data_map as $attribute}
                    
                            {if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
                                {if $attribute.has_content}
                                 <div class="no-js-hide">{attribute_view_gui attribute=$attribute}</div>
                                {/if}
        
                            {elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
                                {if $attribute.has_content}
                                    {if $classi_senza_correlazioni_inline|contains($child.class_identifier)|not}
                                        <div class="no-js-hide">
                                        <strong>{$attribute.contentclass_attribute_name}: </strong>
                                            {attribute_view_gui href=nolink attribute=$attribute}
                                        </div>
                                    {/if}
                                {/if}
                    
                            {/if}
                            
                        {/foreach}
                       
                    {else}
                        
                        <div class="attribute-header">
                            <h3>
                            {if $child.class_identifier|eq('link')}
                                    <a href={$child.data_map.location.content|ezurl()} title="Apri il link in una pagina esterna (si lascerà il sito)">{$child.name|wash()}</a>
                            {else}
                                <a{if $index|eq(0)} class="active"{/if} href={$child.url_alias|ezurl()}>{$child.name|wash()}</a>
                            {/if}
                            </h3>
                        </div>
    
                        <div class="attribute-small">
                        {if $classi_con_data_inline|contains($child.class_identifier)}
                            di {$child.object.published|l10n(date)}
                        {/if}
                        </div>
    
                        {if and(is_set($child.data_map.image), $child.data_map.image.has_content)}
                            <div class="attribute-image no-js-hide">
                                {attribute_view_gui attribute=$child.data_map.image image_class=lista_num}
                            </div>
                        {else}
                            {include node=$child uri='design:parts/common/class_icon.tpl' css_class="image-medium"} 					   
                        {/if}
                        
                        <div class="no-js-hide">
                            {if $child.class_identifier|eq('politico')}
                                {set $ruolo=false()}
                                {if $child.data_map.ruolo.has_content}
                                    {set $ruolo = $child.data_map.ruolo}
                                {/if}
                                {if $ruolo}
                                    {attribute_view_gui attribute=$child.data_map.ruolo}
                                {else}
                                    {if $child.data_map.ruolo2.has_content}
                                        {attribute_view_gui attribute=$child.data_map.ruolo2}
                                    {elseif $child.data_map.abstract.has_content}}			
                                        {attribute_view_gui attribute=$child.data_map.abstract}
                                    {/if}
                                {/if}
                            
                            {elseif is_set($child.data_map.abstract)}
                                {if $child.data_map.abstract.has_content}
                                    {attribute_view_gui attribute=$child.data_map.abstract}
                                {/if}	
                            
                            {elseif is_set($child.data_map.oggetto)}
                                {if $child.data_map.oggetto.has_content}
                                    <div class="attribute-object">
                                        {attribute_view_gui attribute=$child.data_map.oggetto}
                                    </div>
                                {/if}
                            
                            {elseif is_set($child.data_map.testata)}
                               <div class="abstract-line">
                               {if $child.data_map.testata.has_content}
                                <p>Tratto da: 
                                <strong> {attribute_view_gui href=nolink attribute=$child.data_map.testata} </strong>
                                   {if $child.data_map.pagina.content|ne(0)}a pag. {attribute_view_gui attribute=$child.data_map.pagina}
                                        {if $child.data_map.pagina_continuazione.content|ne(0)} e {attribute_view_gui attribute=$child.data_map.pagina_continuazione}
                                    {/if}
                                   {/if}
                                   {if $child.data_map.autore.has_content}
                                    (di {attribute_view_gui attribute=$child.data_map.autore})
                                       {/if}
                                </p>
                                {/if}    
                                {if $child.data_map.argomento_articolo.has_content}
                                <p>Su: 
                                 <strong>
                                 {attribute_view_gui href=nolink attribute=$child.data_map.argomento_articolo}
                                 </strong>
                                </p>
                                {/if}
                                </div>
        
                            {elseif and( is_set( $child.data_map.abstract ), $child.data_map.abstract.has_content )}
                                {attribute_view_gui attribute=$child.data_map.abstract}
                            {elseif $child|has_abstract()}
                                <p>{$child|abstract()|openpa_shorten(200)}</p>
                                
                            {/if}
                            
                            {if $child.class_identifier|eq('applicativo')}
                                {attribute_view_gui attribute=$child.data_map.location_applicativo}
                            {/if}	
                        </div>					
                    
                        {* mostro gli altri attributi *}
                        {foreach $child.data_map as $attribute}
                    
                        {if $attributes_to_show|contains($attribute.contentclass_attribute_identifier)}
                            {if $attribute.has_content}
                             <div class="no-js-hide">{attribute_view_gui attribute=$attribute}</div>
                            {/if}
    
                        {elseif $attributes_with_title|contains($attribute.contentclass_attribute_identifier)}
                            {if $attribute.has_content}
                                {if $classi_senza_correlazioni_inline|contains($child.class_identifier)|not}
                                    <div class="no-js-hide">
                                    <strong>{$attribute.contentclass_attribute_name}: </strong>
                                    {attribute_view_gui href=nolink attribute=$attribute}
                                    </div>
                                {/if}
                            {/if}
                        {/if}
                        
                        {/foreach}
                    
                    {/if}
    
                    </div>
                    {delimiter}{if $index|lt(3)}<hr class="no-js-show clear" />{/if}{/delimiter}
                {/foreach}
                </div>
                
                <div class="rotation-control started no-js-hide"></div>
                <ul class="num-tabs no-js-hide float-break">						 
                {foreach $children as $index => $child}
                    <li><a href="#el{$block.id}-{$child.name|slugize()}-{$child.node_id}">{$index|inc()}</a></li>
                {/foreach}
                </ul>			
                
                <div class="no-js-show"><a href={$custom_node.url_alias|ezurl()} title="{$custom_node.name|wash()}">Vai a {$custom_node.name|wash()}<span class="arrow-blue-r"></span></a></div>
                
            </div>
            
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
        </div>	
    
    </div>
{else}
    <div class="warning">Errore: non &grave; selezionato un nodo sorgente.</div>
{/if}