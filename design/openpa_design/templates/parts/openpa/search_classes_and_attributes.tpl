{*
	BLOCCO di ricerca

	node			ID nodo del folder, a cui limitare la ricerca
	class_filters		array di classi a cui limitare la ricerca
	servizi			array di servizi
	anno_s			array di anni
	argomenti		array di argomenti
	subfilter_arr		array dei campi valorizzati e ricercabili
	search_text		testo contenente la ricerca aperta
	folder			nome del contenitore
	search_included		esiste se il template è incluso in search.tpl
	sub_tree		array sottoalbero a cui limitare la ricerca

*}


{def $filterParameter = array()
     $subtreearray = array(2) }

{if is_set($subtree)}
    {set $subtreearray = $sub_tree}
{/if}

{if and( $subtreearray|contains(2), count($subtreearray)|gt(1) )}
    {def $tempSta = array()}
    {foreach $subtreearray as $sta}
        {if and( $sta|ne(2), $sta|ne('') )}
            {set $tempSta = $tempSta|append($sta)}
        {/if}
    {/foreach}
    {set $subtreearray = $tempSta}
{/if}


{if $node.class_identifier|eq('folder')}
	{if $node.data_map.classi_filtro.has_content}
		{def $related_nodes = fetch('content','related_objects', hash('object_id', $node.contentobject_id, 'attribute_identifier', 'folder/subfolders'))}
		{if $related_nodes|count()|gt(0)}
			{set $subtreearray=$related_nodes[0].main_node_id
                 $folder = $related_nodes[0].name|wash()}
		{elseif is_area_tematica()}
			{set $subtreearray=$node.path_array[3]}
		{/if}
	{/if}
{elseif $node.parent.class_identifier|eq('folder')}
	{if $node.parent.data_map.classi_filtro.has_content}
		{def $related_nodes = fetch('content','related_objects', hash('object_id', $node.parent.contentobject_id, 'attribute_identifier', 'folder/subfolders'))}	
		{if $related_nodes|count()|gt(0)}
			{set $subtreearray=$related_nodes[0].main_node_id
                 $folder = $related_nodes[0].name|wash()}
		{elseif is_area_tematica()}
			{set $subtreearray=$node.path_array[3]}
		{/if}
	{else}
		{if is_area_tematica()}
			{set $subtreearray=$node.path_array[3]}
		{/if}
	{/if}
{elseif is_area_tematica()}
	{set $subtreearray=$node.path_array[3]}
{/if}

{if is_array( $subtreearray )|not}
    {set $subtreearray = array( $subtreearray )}
{/if}

{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">
{literal}
$(function() {
	$(".block-search-advanced-link p").click(function () {
		$(this).toggleClass('open');
		$(this).next().slideToggle("slow");		
    });
});
{/literal}
</script>


{set-block variable=$open}
<div class="border-box block-search">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
{/set-block}

{set-block variable=$close}
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
</div>
{/set-block}


<form id="search-form-box" action="{'content/advancedsearch'|ezurl('no')}" method="get">
	<fieldset>
        {if and( is_array($subtreearray), count($subtreearray)|eq(1), $subtreearray|contains(2) )}
		<legend class="block-title"><span>Cerca</span></legend>
        {else}
		<legend class="block-title"><span>Cerca in {$folder}</span></legend>
        {/if}

	{$open}
	{if is_array($subtreearray)}
		{foreach $subtreearray as $sta}
			<input name="SubTreeArray[]" type="hidden" value="{$sta}" />
		{/foreach}
	{else}
		<input name="SubTreeArray[]" type="hidden" value="{$subtreearray}" />
	{/if}
	<label for="search-string">Ricerca libera</label>
    <input id="search-string" type="text" name="SearchText" value="" />

{if $node.class_identifier|eq('folder')}
	{set $class_filters = $node.data_map.classi_filtro.content|explode(',')}
{/if}

{if count( $class_filters )}
    <div class="block-search-advanced-container square-box-soft-gray-2">
    <div class="block-search-advanced-link">
    
    <p {if $node.class_identifier|eq('folder')}class="open"{/if}>Ricerca avanzata</p>
    
    {def $class  = false()
         $attributi_da_escludere_dalla_ricerca  = openpaini( 'Attributi', 'EscludiDaRicerca', array())
         $anni  = openpaini( 'MotoreDiRicerca', 'RicercaAvanzataSelezionaAnni', array() )
         $facets = array()
         $facets_for_class = array()}
    
    <div class="block-search-advanced {if $node.class_identifier|ne('folder')}hide{/if}">
        {foreach $class_filters as $class_filter}
        {if $class_filter|ne('')}
        
            {set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter|trim() ) )
                 $facets = array()}
            
            <fieldset>
                <legend>{$class.name}</legend>
            
            {foreach $class.data_map as $attribute}
            {if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
                {switch match=$attribute.data_type_string}
                    
                    {case in=array('ezstring','eztext')}
                    {set $filterParameter = getFilterParameter( concat( 'attr_', $attribute.identifier, '_t' ) )}
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <input id="{$attribute.identifier}" type="text" name="filter[{concat( 'attr_', $attribute.identifier, '_t' )}]" value="{if is_set($filterParameter[0])}{$filterParameter[0]}{/if}" />
                    {/case}
                    
                    {case in=array('ezobjectrelationlist')}
                        {set $facets = $facets|append( hash( 'field', concat( 'submeta_', $attribute.identifier, '___main_node_id_si' ), 'name', $attribute.name, 'limit', 10 ) )}
                    {/case}
                    
                    {case}
                    {/case}
                    
                    {case in=array('ezinteger')}
                        {if $attribute.identifier|eq('annoxxx')}
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                            <select id="{$attribute.identifier}" name="anno_s[]">
                                    <option value="">Qualsiasi anno</option>
                                    {foreach $anni as $anno}
                                    <option value="{$anno}">{$anno}</option>
                                    {/foreach}
                            </select>
                        {else}
                            {set $filterParameter = getFilterParameter( concat( 'attr_', $attribute.identifier, '_si' ) )}
                            <label for="{$attribute.identifier}">{$attribute.name}</label>
                            <input id="{$attribute.identifier}" size="5" type="text" name="filter[{concat( 'attr_', $attribute.identifier, '_si' )}]" value="{if is_set($filterParameter[0])}{$filterParameter[0]}{/if}" />
                        {/if}
                    {/case}
                    
                {/switch}
            {/if}
            {/foreach}
            
            </fieldset>
            
            <input name="filter[]" value="contentclass_id:{$class.id}" type="hidden" />
            {set $facets_for_class = $facets_for_class|append( hash( $class.name, $facets ) )}
        
        {/if}
        {/foreach}
            
        <input name="OriginalNode" value="{$node.node_id}" type="hidden" />
        {if is_array($subtreearray)}
            {set $subtreearray = $subtreearray|unique()} 
            {foreach $subtreearray as $sta}
                <input name="SubTreeArray[]" type="hidden" value="{$sta}" />
            {/foreach}
        {else}
        <input name="SubTreeArray[]" type="hidden" value="{$subtreearray}" />
        {/if}
    </div>
    
    </div>
    </div>

    {if count( $facets_for_class )}
    
        {def $filters_parameters = false()
             $query = false()
             $filters_hash = false()
             $filters_search = false()
             $filters_search_extras = false()
             $nameList = false()
             $baseURI = false()
             $uriSuffix = false()
             $faccette = false()}
             
        {foreach $class_filters as $id => $class_filter}
        {if $class_filter|ne('')}
        
            {set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter|trim() ) )}
            
            {if $facets_for_class[$id][$class.name]|count()}
            
                {set $filters_parameters = getFilterParameters()
                     $query = cond( ezhttp( 'SearchText','get','hasVariable' ), ezhttp( 'SearchText', 'get' ), '' )
                     $filters_hash = hash( 'query', $query,
                                              'class_id', array( $class.id ),
                                              'limit', 1,
                                              'subtree_array', $subtreearray,
                                              'sort_by', hash( 'score', 'desc' ),
                                              'facet', $facets_for_class[$id][$class.name],
                                              'filter', $filters_parameters
                                             )
                     $filters_search = fetch( ezfind, search, $filters_hash )
                     $filters_search_extras = $filters_search['SearchExtras']
                     $nameList = array()
                     $baseURI=concat( '/content/advancedsearch?', 'OriginalNode=', $node.node_id, '&SubTreeArray[]=', $subtreearray|implode( '&SubTreeArray[]=' ) )
                     $uriSuffix = $filters_parameters|getFilterUrlSuffix()
                     $faccette = false()}
        
                {if $class}
                    {set $uriSuffix = concat( $uriSuffix, '&filter[contentclass_id]=', $class.id )}
                {/if}
        
        {set-block variable=$faccette}      
                
                {foreach $facets_for_class[$id][$class.name] as $key => $facet}
                    {if $filters_search_extras.facet_fields.$key.nameList|count()}
                        
                        <strong>{$facet['name']}</strong>
                                        
                        <ul>
                        {foreach $filters_search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                            {if ne( $key2, '' )}
                                <li>
                                    <a href={concat( $baseURI, $uriSuffix, '&filter[]=', $filters_search_extras.facet_fields.$key.queryLimit[$key2], '&activeFacets[', $facet['field'], ':', $facet['name'], ']=', $facetName )|ezurl()}>
                                    {if fetch( 'content', 'node', hash( 'node_id', $facetName ))}{fetch( 'content', 'node', hash( 'node_id', $facetName )).name|wash()}{else}{$facetName}{/if} ({$filters_search_extras.facet_fields.$key.countList[$key2]})
                                    </a>
                                </li>
                            {/if}
                        {/foreach}
                        </ul>
                    {/if}
                {/foreach}
        {/set-block}
                {if ne($faccette|trim(), '')}
                    <div class="block-search-advanced-container square-box-soft-gray-2">
                    <div class="block-search-advanced-link">
                    <p class="close">Filtra in {$class.name}</p>
                    <div class="block hide">
                        {$faccette}
                    </div>
                    </div>
                    </div>
                {/if}   
      
            {/if}  
        {/if}  
        {/foreach}
    
    {/if}  

{/if}

	<input id="search-button-button" class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
	{$close}
	</fieldset>
</form>
