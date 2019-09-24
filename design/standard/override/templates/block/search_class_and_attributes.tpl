{def $foldersClasses = array( 'folder', 'pagina_sito' )}

{if and( is_set($block.custom_attributes.node_id), $block.custom_attributes.node_id|gt(0) )}
    {def $node_id = $block.custom_attributes.node_id}
    {def $subtreearray = array( $block.custom_attributes.node_id )}
{else}
    {def $node_id = ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
    {def $subtreearray = array( ezini( 'NodeSettings', 'RootNode', 'content.ini' ) )}
{/if}

{if $block.custom_attributes.class|ne('')}    
    {def $class_filter = $block.custom_attributes.class|explode(',')}
{/if}

{def $limit=10}

{def $node = fetch(content,node,hash(node_id,$node_id))
     $attributi_da_escludere_dalla_ricerca = openpaini( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca' )
     $anni = openpaini( 'MotoreDiRicerca', 'RicercaAvanzataSelezionaAnni', array())}


{set-block variable=$open}

{/set-block}

{set-block variable=$close}

{/set-block}

{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js', 'ui-datepicker-it.js' ) )}
{ezcss_require( array( 'datepicker.css' ) )}
<script type="text/javascript">
{literal}
$(function() {
    $( ".from_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1

    });
    $( ".to_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1
    });
});
{/literal}
</script>
<div class="widget {$block.view}">
<div class="widget_title">
    <h3>{$block.name|wash()}</h3>
</div>
<div class="widget_content">
    <form action="{'content/search'|ezurl('no')}" method="get">
			
        <input placeholder="Ricerca libera" class="form-control" id="search-string" type="text" name="SearchText" value="" />

        <button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#AdvancedPanel">
            Ricerca avanzata
        </button>
        
        {if is_set( $class_filter[0] )}
        <div id="AdvancedPanel" class="collapse">
            
            {def $class = fetch( 'content', 'class', hash( 'class_id', $class_filter[0] ) )}
            
            {def $sorters = array()
                 $filter_string = ''}
            
            {foreach $class.data_map as $attribute}
            {if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
                {switch match=$attribute.data_type_string}
                    {case in=array('ezstring','eztext')}
                           {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', solr_field( $attribute.identifier, 'string' ) ) )}
                    {/case}
                    {case in=array('ezdate', 'ezdatetime')}
                        {set $filter_string = solr_field( $attribute.identifier, 'date' )}
                        {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', $filter_string ) )}
                    {/case}
                    {case in=array('ezinteger')}
                        {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', solr_field( $attribute.identifier, 'sint' ) ) )}
                    {/case}
                    {case}
                    {/case}
                {/switch}
            {/if}
            {/foreach}
        
            <div class="form-group">
                <label for="Sort">Ordina per</label>
                <select class="form-control" id="Sort" name="Sort">
                    <option value=""> - Seleziona</option>
                    <option value="published">Data di pubblicazione</option>
                    <option value="score">Rilevanza</option>                
                    {foreach $sorters as $sorter}
                        {if and( $sorter.name|ne( 'Nome' ), $sorter.name|ne( 'Rilevanza' ), $sorter.name|ne( 'Tipologia di contenuto' ), $sorter.name|ne( 'Data di pubblicazione' ) )}
                            <option value="{$sorter.value|wash()}">{$sorter.name|wash()}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>

            <div class="form-group">
                <label for="Order">Ordinamento</label>
                <select class="form-control" name="Order" id="Order">
                    <option value="desc">Discendente</option>
                    <option value="asc">Ascendente</option>
                    {foreach $sorters as $sorter}
                        {if and( $sorter.name|ne( 'Nome' ), $sorter.name|ne( 'Rilevanza' ), $sorter.name|ne( 'Tipologia di contenuto' ), $sorter.name|ne( 'Data di pubblicazione' ) )}
                            <option value="{$sorter.value|wash()}">{$sorter.name|wash()}</option>
                        {/if}
                    {/foreach}
                </select>
            </div>

            {def $facets = array()
                 $filterParameter = false()}                        
                
            {foreach $class.data_map as $attribute}
            {if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
                {switch match=$attribute.data_type_string}
                    
                    {case in=array('ezstring','eztext')}
                    {set $filterParameter = getFilterParameter( solr_field( $attribute.identifier, 'text' ) )}
                    {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', solr_field( $attribute.identifier, 'text' ) ) )}
                        <div class="form-group">
                            <label for="{$attribute.identifier}">{$attribute.name}</label>
                            <input class="form-control" id="{$attribute.identifier}" type="text" name="filter[{solr_field( $attribute.identifier, 'text' )|wash()}]" value="{if is_set($filterParameter[0])}{$filterParameter[0]|wash()}{/if}" />
                        </div>
                    {/case}
                    
                    {case in=array('ezobjectrelationlist')}
                        {set $facets = $facets|append( hash( 'field', solr_meta_subfield($attribute.identifier,'main_node_id'), 'name', $attribute.name, 'limit', 10 ) )}
                    {/case}
                    
                    {case in=array('ezdate', 'ezdatetime')}
                        {set $filter_string = solr_field( $attribute.identifier, 'date' )}
                        {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', $filter_string ) )}
                        {if $attribute.identifier|eq('data_archiviazione')|not()}
                            <div class="form-group">
                                <span class="help-block"><strong>{$attribute.name}:</strong></span>
                                <label for="from">Dalla data: <small class="no-js-show"> (GG-MM-AAAA)</small>
                                <input type="text" class="from_picker form-control" name="from_attributes[{$filter_string|wash()}]" title="Dalla data" value="" /></label>
                                <label for="to">Alla data: <small class="no-js-show"> (GG-MM-AAAA)</small>
                                <input class="to_picker form-control" type="text" name="to_attributes[{$filter_string|wash()}]" title="Alla data" value="" /></label>
                            </div>
                        {/if}                    
                    {/case}
                    
                    {case}
                    {/case}
                    
                    {case in=array('ezinteger')}
                        <div class="form-group">
                        {if $attribute.identifier|eq('annoxxx')}
                            <label for="{$attribute.identifier}">{$attribute.name}</label>
                            <select class="form-control" id="{$attribute.identifier}" name="anno_s[]">
                                    <option value="">Qualsiasi anno</option>
                                    {foreach $anni as $anno}
                                    <option {if $anno|eq($anno_s[0])} class="marked" selected="selected"{/if} value="{$anno|wash()}">{$anno|wash()}</option>
                                    {/foreach}
                            </select>
                        {else}
                            {set $filterParameter = getFilterParameter( solr_field( $attribute.identifier, 'sint' ) )}
                            <label for="{$attribute.identifier}">{$attribute.name}</label>
                            <input class="form-control" id="{$attribute.identifier}" size="5" type="text" name="filter[{solr_field( $attribute.identifier, 'sint' )}]" value="{if is_set($filterParameter[0])}{$filterParameter[0]|wash()}{/if}" />
                        {/if}
                        </div>
                        {set $sorters = $sorters|append( hash( 'name', $attribute.name, 'value', solr_field( $attribute.identifier, 'sint' ) ) )}
                    {/case}                
                {/switch}
            {/if}
            {/foreach}
            
            <div class="form-group">
                <span class="help-block"><strong>Data di pubblicazione:</strong></span>
                <label for="from">Dalla data: <small class="no-js-show"> (GG-MM-AAAA)</small>
                <input type="text" class="from_picker form-control" name="from" title="Dalla data" value="" /></label>
                <label for="to">Alla data: <small class="no-js-show"> (GG-MM-AAAA)</small>
                <input class="to_picker form-control" type="text" name="to" title="Alla data" value="" /></label>
            </div>
            
            <input name="filter[]" value="contentclass_id:{$class.id}" type="hidden" />
            <input name="OriginalNode" value="{$node.node_id}" type="hidden" />
            {if is_array($subtreearray)}
                {set $subtreearray = $subtreearray|unique()} 
                {foreach $subtreearray as $sta}
                    <input name="SubTreeArray[]" type="hidden" value="{$sta|wash()}" />
                {/foreach}
            {else}
            <input name="SubTreeArray[]" type="hidden" value="{$subtreearray|wash()}" />
            {/if}
            
            {if count($facets)}
                {def $filters_parameters = getFilterParameters()
                     $cleanFilterParameters = array()
                     $tempFilter = false()}
                
                {def $query = cond( ezhttp( 'SearchText','get','hasVariable' ), ezhttp( 'SearchText', 'get' )|wash(), '' )}
                {if count( $subtreearray )|eq(0)}
                    {set $subtreearray = array( ezini( 'NodeSettings', 'RootNode', 'content.ini' ) )}
                {/if}
                {def $filters_hash = hash( 'query', $query,
                                              'class_id', array( $class.id ),
                                              'limit', 1,
                                              'subtree_array', $subtreearray,
                                              'sort_by', hash( 'score', 'desc' ),
                                              'facet', $facets,
                                              'filter', $filters_parameters
                                             )
                     $filters_search = fetch( ezfind, search, $filters_hash )
                     $filters_search_extras = $filters_search['SearchExtras']
                }
                
                {def $nameList = array()}
                
                {def $baseURI=concat( '/content/advancedsearch?', 'OriginalNode=', $node.node_id, '&SubTreeArray[]=', $subtreearray|implode( '&SubTreeArray[]=' ) )}
                {def $uriSuffix = $filters_parameters|getFilterUrlSuffix()}
        
                {if $class}
                    {set $uriSuffix = concat( $uriSuffix, '&filter[contentclass_id]=', $class.id )}
                {/if}
        
                {def $activeFacetParameters = array()}
                {if ezhttp_hasvariable( 'activeFacets', 'get' )}
                    {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
                {/if}
                {foreach $activeFacetParameters as $facetField => $facetValue}
                    {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
                {/foreach}                
        
                {foreach $facets as $key => $facet}            
                    {if $filters_search_extras.facet_fields.$key.nameList|count()}
                        <span class="help-block"><strong>{$facet['name']}</strong></span>
                        
                        {if count($filters_search_extras.facet_fields.$key.nameList)|gt(5)}
                            <select class="form-control" name="filter[]">
                                <option value=""> - Seleziona</option>
                            {foreach $filters_search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                                {if ne( $key2, '' )}
                                    {def $filterName = $filters_search_extras.facet_fields.$key.queryLimit[$key2]|explode(':')
                                         $filterValue = getFilterParameter( $filterName[0] )}
                                    <option {if $filterValue|contains( $facetName )} selected="selected" {/if} value='{$filters_search_extras.facet_fields.$key.queryLimit[$key2]}'>{if fetch( 'content', 'node', hash( 'node_id', $facetName ))}{fetch( 'content', 'node', hash( 'node_id', $facetName )).name|wash()}{else}{$facetName}{/if} ({$filters_search_extras.facet_fields.$key.countList[$key2]})</option>
                                    {undef $filterName $filterValue}
                                {/if}
                            {/foreach}
                            </select>
                        {else}
                            {foreach $filters_search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                                {if ne( $key2, '' )}
                                    {def $filterName = $filters_search_extras.facet_fields.$key.queryLimit[$key2]|explode(':')
                                         $filterValue = getFilterParameter( $filterName[0] )}                            
                                    <div class="radio">
                                    <label>
                                        <input {if $filterValue|contains( $facetName )} checked="checked" {/if} class="inline" type="checkbox" name="filter[]" value='{$filters_search_extras.facet_fields.$key.queryLimit[$key2]}' /> {if fetch( 'content', 'node', hash( 'node_id', $facetName ))}{fetch( 'content', 'node', hash( 'node_id', $facetName )).name|wash()}{else}{$facetName}{/if} ({$filters_search_extras.facet_fields.$key.countList[$key2]})
                                    </label>
                                    </div>
                                    {undef $filterName $filterValue}
                                {/if}
                            {/foreach}
                        {/if}
                    {else}
                        {def $filterValue = getFilterParameter( $facet.field )} 
                        {if count( $filterValue )|gt(0)}
                        <span class="help-block"><strong>{$facet['name']}</strong></span>
                            <div class="radio">
                            <label>
                                <input checked="checked" class="inline" type="checkbox" name="filter[]" value='{concat( $facet.field, ':', $filterValue[0] )}' /> {if fetch( 'content', 'node', hash( 'node_id', $filterValue[0] ))}{fetch( 'content', 'node', hash( 'node_id', $filterValue[0] )).name|wash()}{else}{$filterValue[0]}{/if}
                            </label>
                            </div>
                        {/if}
                        {undef $filterValue}
                    {/if}
                    
                {/foreach}
                
            {/if}
            
        </div>
        {/if}
        
        <div class="form-group m_top_5 clearfix">
            <input id="search-button-button" class="btn btn-primary pull-right" type="submit" name="SearchButton" value="Cerca" />
        </div>
    
</form>

</div>
</div>