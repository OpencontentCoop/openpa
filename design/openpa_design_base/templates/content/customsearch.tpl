{*
	TEMPLATE DI RICERCA	
*}

{def	$search=false()
	$custom_filter=array()}
{if is_set($use_template_search)}
	{set $use_template_search=true()}
{else}
	{def $use_template_search=true()}
{/if}

{if or($search_text|eq('Cerca in tutto il sito'), $search_text|eq('Search'), $search_text|eq('Cerca'))}{set $search_text=''}{/if}


{* cattura le variabili passate via GET *}
{def $servizi_tmp = ezhttp( 'Servizi', 'get' )
	 $not_available_facets = openpaini( 'MotoreRicerca', 'faccette_non_disponibili' )
	 $anni = ezhttp( 'Anni', 'get' )
	 $interna = ezhttp( 'Interna', 'get' )
	 $subfilter_arr = ezhttp('subfilter_arr','get')
	 $argomenti = ezhttp( 'Argomenti', 'get' )
	 $sort = ezhttp( 'Sort', 'get' )
	 $cond = ezhttp( 'cond', 'get' )
	 $order = ezhttp( 'Order', 'get' )
	 $classe = ezhttp( 'SearchContentClassID', 'get' )
	 $filtri_selezionati = ezhttp( 'Filtri', 'get' )
	 $anno_s = ezhttp( 'anno_s', 'get' )
	 $SearchButton = ezhttp( 'SearchButton', 'get' )
	 $sub_tree_passed= ezhttp( 'SubTreeArray', 'get' )
	 $orig_position=fetch(content,node,hash(node_id,$sub_tree_passed[0]))
	 $OriginalNodeID= ezhttp( 'OriginalNode', 'get' )
	 $filtro_id = ''
	 $sort_by = ''
	 $order_by = ''
	 $classes_id=array()
	 $argomenti_tutti = array()
	 $string_filters = ''
	 $servizi = array()
}

{set $servizi=hash(0, $servizi_tmp[0]|explode("-")|implode("'") )}




{switch match=$sort}
	{case match='score'}
		{set $sort_by='score'}
	{/case}
	{case match='published'}
		{set $sort_by='published'}
	{/case}
	{case match='class_name'}
		{set $sort_by='class_name'}
	{/case}
	{case match='name'}
		{set $sort_by='name'}
	{/case}
	{case}
		{set $sort_by='score'}
	{/case}
{/switch}
{switch match=$order}
	{case match='desc'}
		{set $order_by='desc'}
	{/case}
	{case match='asc'}
		{set $order_by='asc'}
	{/case}
	{case}
		{set $order_by='desc'}
	{/case}
{/switch}


{if $search_text|eq('')}
	{set	$sort_by='published'
		$order_by='desc'}
	 
{/if}


{if $filtri_selezionati|count()|gt(0)}
	{foreach $filtri_selezionati as $filtro}
		{set $filtro_id=$filtro|explode(':')}
		{set $string_filters=concat($string_filters,"&Filtri[]=contentclass_id:",$filtro_id[1])}
		{set $classes_id=$classes_id|append($filtro_id[1])}
	{/foreach}
{/if}

<div class="border-box search-page{if $OriginalNodeID|not()} search-full{/if}">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc float-break">

{* se si effettua una ricerca dal box colonna destra per una classe specifica *}
{if $filtri_selezionati|count()|ne(1)}
{if $OriginalNodeID|gt(0)}
{def $node_referrer=fetch(content,node,hash(node_id,$OriginalNodeID))}
	<div id="virtual-path" class="width-layout">
	<p>
		{foreach $node_referrer.path as $path}
			<a href={$path.url_alias|ezurl()}>{$path.name|wash}</a><span class="path-separator">&raquo;</span>
		{/foreach}
		<a href={$node_referrer.url_alias|ezurl()}>{$node_referrer.name}</a>
	</p>
	</div>	
	{include name=searchbox node=$node_referrer
		subfilter_arr=$subfilter_arr
		folder=$node_referrer.name
		search_text=$search_text
		class_filters=$classes_id
		argomenti=$argomenti 
		servizi=$servizi
		search_included=true()
		uri='design:parts/search_class_and_attributes.tpl' }
{/if}
{/if}

{* se si parte dal motore di ricerca globale e si filtra per una sola classe *}

{if $filtri_selezionati|count()|eq(1)}
	{def $node_referrer=fetch(content,node,hash(node_id,2))}
	<div id="virtual-path" class="width-layout">
	<p>
		{foreach $node_referrer.path as $path}
			<a href={$path.url_alias|ezurl()}>{$path.name|wash}</a><span class="path-separator">&raquo;</span>
		{/foreach}
		<a href={$node_referrer.url_alias|ezurl()}>{$node_referrer.name}</a>
	</p>
	</div>
{def $contentClass=fetch( 'content', 'class', hash( 'class_id', $classes_id[0] ) )}
	{include name=searchbox node=$node_referrer
		subfilter_arr=$subfilter_arr
		folder=concat('tutto il sito per documenti di tipo ', $contentClass.name)
		search_text=$search_text
		class_filters=$classes_id
		argomenti=$argomenti 
		servizi=$servizi
		search_included=true()
		uri='design:parts/search_class_and_attributes.tpl' }
{/if}


{* inizializzazione variabili *}
{def 	$available_classes=array(36,37,40,38,73,72,75,96,121,109,103,4)
	$contentClass=fetch( 'content', 'class', hash( 'class_id', $contentClass_id ) )
	$facetParameters  = fetch( ezfind, facetParameters ) $filterParameters = fetch( ezfind, filterParameters )
	$date=$anno_s[0]
	$filter=array()
}

{if and($servizi[0]|ne(""), $argomenti[0]|ne(""))}
	{def $classi_con_servizi_e_argomenti = wrap_user_func('getClassAttributes', array(array('servizio', 'argomento')) )}
	{set $custom_filter=array('or')}
	{foreach $classi_con_servizi_e_argomenti as $ccs}
		{set $filter=array('and',
				concat( $ccs['identifier'] ,'/servizio:', $servizi[0]),
				concat( $ccs['identifier'] ,'/argomento:', $argomenti[0])
			)
			$custom_filter=$custom_filter|append($filter)}
	{/foreach}
{else}
{* filtro per servizi *}
	{if $servizi[0]|ne("")}
	{def $classi_con_servizi = wrap_user_func('getClassAttributes', array(array('servizio')) )}
	{set $custom_filter=array('or')}
	
	{foreach $classi_con_servizi as $ccs}
		{set $filter=array(concat( $ccs['identifier'] ,'/servizio:', $servizi[0]))
			$custom_filter=$custom_filter|append($filter)}
	{/foreach}
	{undef $classi_con_servizi}
	{/if}

{* filtro per argomenti *}
	{if $argomenti[0]|ne("")}
	{def $classi_con_argomenti = wrap_user_func('getClassAttributes', array(array('argomento')) )}
	{set $custom_filter=array('or')}
	{foreach $classi_con_argomenti as $cca}
		{def $filter=array(concat( $cca['identifier'] ,'/argomento:', $argomenti[0]))}
		{set $custom_filter=$custom_filter|append($filter)}
	{/foreach}
	{undef $classi_con_argomenti}
	{/if}
{/if}


{if $filtri_selezionati|gt(0)}
	{def $custom_subfilters=array() 
		 $subfilter_string=''}
	{if $subfilter_arr|count()|gt(0)}
		{foreach $subfilter_arr as $field => $value}
			{if $value}
				{set $custom_subfilters=$custom_subfilters|append(array('and', concat($field, ':', $value)))}
				{set $subfilter_string=concat($subfilter_string,'&subfilter_arr[',$field,']=',$value)}
			{/if}
		{/foreach}
		{if $custom_filter|count()}
			{set $custom_filter=array($custom_filter, $custom_subfilters)}
		{else}
			{set $custom_filter=array($custom_subfilters)}
		{/if}
	{/if}
{/if}


{if $date|gt(0)}
	{def $to_date=$date|sum(1)}
	{if $custom_filter|count()|gt(0)}
	{def $custom_filter=array( 'and', $custom_filter, array(concat('published:[', $date, '-01-01T00:59:59.999Z/YEAR TO ', $to_date, '-01-01T00:59:59.999Z/YEAR]')))}
	{else}
	{def $custom_filter=array( array(concat('published:[', $date, '-01-01T00:59:59.999Z/YEAR TO ', $to_date, '-01-01T00:59:59.999Z/YEAR]')))}
	{/if}
{/if}

{* controlla SE il filtro e' vuoto per prevenire errori nalla costruzione della query x ezfind *}
{if $custom_filter|eq(array(array()))}
	{set $custom_filter=array()}
{/if}
{if $custom_filter[1]|eq(array())}
	{set $custom_filter=$custom_filter[0]}
{/if}

{def $memo_search_text=$search_text}
{if $cond|eq('AND')}
	{def $tmp_array=$search_text|explode(' ') $tmp_string='' }
	{foreach $tmp_array as $k => $ts}
		{if $k|eq(0)}
			{set $tmp_string=$ts}
		{else}
			{set $tmp_string=concat($tmp_string, ' AND ', $ts)}
		{/if}
	{/foreach}
	{set $search_text=$tmp_string}
{/if}


{if $use_template_search}
    {set $page_limit=50}
	{if $classes_id|count()|gt(0)}
    		{set $search=fetch( ezfind,search,
                        hash( 'query', $search_text,
                              'offset', $view_parameters.offset,
			      'subtree_array', $search_subtree_array,
                              'limit', $page_limit,
			      'class_id', $classes_id,
                              'sort_by', hash( $sort_by, $order_by ),
                              'facet', $facetParameters,
                              'filter', $custom_filter ))}
	{else}
    		{set $search=fetch( ezfind,search,
                        hash( 'query', $search_text,
                              'offset', $view_parameters.offset,
			      'subtree_array', $search_subtree_array,
                              'limit', $page_limit,
			      'class_id', '[* TO *]',
                              'sort_by', hash( $sort_by, $order_by ),
                              'facet', $facetParameters,
                              'filter', $custom_filter ))}
	
	{/if}
	{set $search_text=$memo_search_text}
    {set $search_count=$search['SearchCount']}
    {set $search_count=$search['SearchCount']}
    {set $search_result=$search['SearchResult']}
    {def $search_extras=$search['SearchExtras']}
    {def $facetField=$search_extras.facet_fields.0.field}
    {def $facetField='class'}
    {set $stop_word_array=$search['StopWordArray']}
    {set $search_data=$search}
{/if}
{def $baseURI=concat( '/content/advancedsearch?SearchText=', $search_text )}


{* Build the URI suffix, used throughout all URL generations in this page *}
{def $uriSuffix = ''}
{foreach $facetParameters as $item}
	{foreach $item as $name => $value}
	    {set $uriSuffix = concat( $uriSuffix, '&facet_', $name, '=', $value )}
	{/foreach}
{/foreach}

{foreach $filterParameters as $name => $value}
    {set $uriSuffix = concat( $uriSuffix, '&filter[]=', $name, ':', $value )}
{/foreach}

{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">
{literal}
//<![CDATA[
$(function() {

    function ezfTrim( str ) {
        return str.replace(/^\s+|\s+$/g, '') ;
    }

    function ezfGetCookie( name ) {
		var cookieName = 'eZFind_' + name;
		var cookie = document.cookie;
		var cookieList = cookie.split( ";" );
		for( var idx in cookieList ) {
			cookie = cookieList[idx].split( "=" );
			if ( ezfTrim( cookie[0] ) == cookieName ){
				return( cookie[1] );
			}
		}
		return 'block';
    }

    function ezfSetCookie( name, value ){
		var cookieName = 'eZFind_' + name;
		var expires = new Date();
		expires.setTime( expires.getTime() + (365 * 24 * 60 * 60 * 1000));
		document.cookie = cookieName + "=" + value + "; expires=" + expires + ";";
    }

	$.fn.ezfToggleBlock = function(options) {
		return $(this).each(function() {
			var name = $(this).attr('id');			
			$('#'+name+'Panel').css('display', ezfGetCookie( name ));
			if (ezfGetCookie( name ) == 'none') {
				$(this).removeClass('open');
			}
			$(this).bind('click', function () {
				name = $(this).attr('id');	
				$('#'+name+'Panel').slideToggle("slow", function() {
					$('#'+name).toggleClass('open');
					var id = $(this).prev().attr('id');
					ezfSetCookie( name, $(this).css('display') )
				});
			});
		});
	};
	$(".eztoggle").ezfToggleBlock();
	
	$(".ezjs_toggleCheckboxes").bind('click', function () {
		$("input[name='Filtri[]']").each( function() {
			if ($(this).is(':checked'))
				$(this).attr( 'checked', false );
			else
				$(this).attr( 'checked', true );
		});
		return false;
	});
	
	function jtoggleCheckboxes( formname, checkboxname ){
		with( formname ){
			for( var i = 0, l = elements.length; i < l; i++ ){
				if( elements[i].type === 'checkbox' && elements[i].name == checkboxname && elements[i].disabled == false ){
					if( elements[i].checked == true ){
						elements[i].checked = false;
					}else{
						elements[i].checked = true;
					}
				}
			}
		}
	}
});
//]]>
{/literal}
</script>

{if $filtri_selezionati|count()|ne(1)}
{if $OriginalNodeID|gt(0)|not()}
<form action={"/content/advancedsearch/"|ezurl} id="ezfindsearch" method="get">
	<fieldset>
		<legend class="block-title">{"Search"|i18n("design/ezwebin/content/search")}</legend>

		<div class="border-box box-gray block-search">
		<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content">

			<div class="content-search">

				<input type="hidden" name="facet_field" value="class" />

				<p>
					<label for="Search">Ricerca libera</label>
					<input class="halfbox" type="text" size="20" name="SearchText" id="Search" value="{$search_text|wash}" />
					<input class="defaultbutton" name="SearchButton" type="submit" value="{'Search'|i18n('design/ezwebin/content/search')}" />
				</p>

				<div class="block-search-advanced-container square-box-soft-gray-2">
					<div class="block-search-advanced-link">
						<p class="eztoggle open" id="AdvancedSearch">Ricerca avanzata</p>

						<div class="block-search-advanced" id="AdvancedSearchPanel">					
							<label for="PhraseSearchText">{"Search the exact phrase"|i18n("design/standard/content/search")}</label>
							<input class="box" type="text" size="40" name="PhraseSearchText" id="PhraseSearchText" value="{$phrase_search_text|wash}" />

							<div class="columns-three">
							<div class="col-1-2">
							<div class="col-1">
							<div class="col-content">

{def
	$node_id_servizi_attivi = openpaini( 'Servizi', 'attivi' )
        $node_id_servizi_non_attivi = openpaini( 'Servizi', 'non_attivi' )
        $node_id_argomenti = openpaini( 'Argomenti', 'argomenti' )
        $node_servizi_attivi = fetch(content,node,hash(node_id,$node_id_servizi_attivi))
        $node_servizi_non_attivi = fetch(content,node,hash(node_id,$node_id_servizi_non_attivi))
        $node_argomenti = fetch(content,node,hash(node_id,$node_id_argomenti))
        $servizi_attivi=fetch(content, list, hash(parent_node_id, $node_servizi_attivi.node_id, 'sort_by', array('name', true()),
                                        'class_filter_type',  'include', 'class_filter_array', array( 'servizio')))
        $servizi_non_attivi=fetch(content, list, hash(parent_node_id, $node_servizi_non_attivi.node_id, 'sort_by', array('name', true()),
                                        'class_filter_type',  'include', 'class_filter_array', array( 'servizio')))
        $margomenti=fetch(content, list, hash(parent_node_id, $node_argomenti.node_id, 'sort_by', array('name', true()),
                               'class_filter_type',  'include', 'class_filter_array', array( 'macroargomento')))
}

					<div class="subfilter">
						<label for="Servizi">Inerente al Servizio - {$servizio.name}</label>
						<select {if $servizi[0]}class="marked"{/if} id="Servizi" name="Servizi[]">
						<option value="">Qualsiasi servizio</option>
						<optgroup  label="{$node_servizi_attivi.name|wash}">
                                                {foreach $servizi_attivi as $k => $servizio}
                                                        <option {if concat('"',$servizio.name|wash,'"')|eq($servizi[0])} class="marked" selected="selected" {/if} value='"{$servizio.name|wash|explode("'")|implode("-")}"'>{$servizio.name|wash}</option>
                                                {/foreach}
                                                </optgroup>
                                                <optgroup  label="Servizi non attivi">
                                                {foreach $servizi_non_attivi as $k => $servizio}
                                                        <option {if concat('"',$servizio.name|wash,'"')|eq($servizi[0])} class="marked" selected="selected" {/if} value='"{$servizio.name|wash|explode("'")|implode("-")}"'>{$servizio.name|wash}</option>
                                                {/foreach}
                                                </optgroup>
						</select>
					</div>

								{*  BLOCCO FILTRO SEZIONE oggetto - INIZIO  *} 
								<div class="subfilter">
									<div class="element">
										{def $sub_tree=array( 161, 1026, 53909, 53937, 53943, 156904, 54603)}
										<label for="SubTreeArray">Sezione del sito in cui cercare:</label>
										<select {if $sub_tree_passed[0]}class="marked"{/if} id="SubTreeArray" name="SubTreeArray[]">
											<option value="-1">In tutte le sezioni{*"Any section"|i18n("design/standard/content/search")*}</option>
										{if $sub_tree|contains($search_sub_tree[0])|not()}
											<option selected="selected" value="{$search_sub_tree[0]}">
												Solo in: "{$orig_position.name}"
											</option>
										{/if}
										{foreach $sub_tree as $k => $subtree}
											{def $subtree_node=fetch(content,node,hash(node_id, $subtree))}
											<option {cond( $subtree|eq($search_sub_tree[0] ), ' class="marked" selected="selected"', '' )} value="{$subtree}">
												Solo in "{$subtree_node.name|wash}"
											</option>
										{/foreach}
										</select> 
									</div>
								</div>
								{* FINE BLOCCO FILTRO SEZIONE *}

							</div>
							</div>
							<div class="col-2">
							<div class="col-content">
								<div class="subfilter">
									<label for="Argomenti">Argomento</label>
									<select {if $argomenti[0]}class="marked"{/if} id="Argomenti" name="Argomenti[]">
										<option value="">Qualsiasi argomento</option>
										{def $margomenti=fetch(content, list, hash(parent_node_id, 809, 'sort_by', array('name', true()),
										'class_filter_type',  'include', 'class_filter_array', array( 'macroargomento')))}
										{foreach $margomenti as $k => $margomento}
										<optgroup  label="{$margomento.name|wash}">
											{set $argomenti_tutti=fetch(content, list, hash(parent_node_id, $margomento.node_id,
											'sort_by', array('name', true()),
											'class_filter_type',  'include', 'class_filter_array', array( 'argomento' )))}
											{foreach $argomenti_tutti as $k => $argomento}
												<option {if concat('"', $argomento.name, '"')|eq($argomenti[0])} class="marked" selected="selected"{/if} value='"{$argomento.name}"'>{$argomento.name|wash}</option>
											{/foreach}
										</optgroup>
									{/foreach}
									</select>
								</div>

								<div class="subfilter">
									<label for="Sort">Ordina per</label>
									<select {if $Sort}class="marked"{/if} id="Sort" name="Sort">
										<option {if $sort|eq('score')} class="marked" selected="selected"{/if} value="score">Rilevanza</option>
										<option {if $sort|eq('published')} class="marked" selected="selected"{/if} value="published">Data di pubblicazione</option>
										<option {if $sort|eq('class_name')} class="marked" selected="selected"{/if} value="class_name">Tipologia di contenuto</option>
										<!-- <option {if $sort|eq('name')} class="marked" selected="selected"{/if} value="name">Nome</option> -->
									</select>
									<label for="Order">Ordinamento</label>
									<select {if $order}class="marked"{/if} name="Order" id="Order">										
										<option {if $order|eq('desc')} class="marked" selected="selected"{/if} value="desc">Discendente</option>
										<option {if $order|eq('asc')} class="marked" selected="selected"{/if} value="asc">Ascendente</option>
									</select>
								</div>	

							</div>
							</div>
							</div>
							<div class="col-3">
							<div class="col-content">

								<div class="subfilter">
									<label for="anno_s">Anno</label>
									{def $anni = array('2010','2009','2008','2007','2006','2005','2004','2003','2002','2001')}
									<select {if $anno_s[0]}class="marked"{/if} id="anno_s" name="anno_s[]">
										<option value="">Qualsiasi anno</option>
										{foreach $anni as $anno}
										<option {if $anno|eq($anno_s[0])} class="marked" selected="selected"{/if} value="{$anno}">{$anno}</option>
										{/foreach}
									</select>
								</div>
									
								<span class="label">Usa condizioni logiche:</span>
								<label for="radio_and">AND</label>
								<input type="radio" id="radio_and" name="cond" title="AND" value="AND" {if $cond|eq('AND')}checked="checked"{/if} />
								<label for="radio_or"> OR</label>
								<input id="radio_or" type="radio" name="cond" title="OR" value="OR" {if $cond|ne('AND')}checked="checked"{/if} />

							</div>
							</div>
							</div>
							<input class="defaultbutton" name="SearchButton" type="submit" value="{'Search'|i18n('design/ezwebin/content/search')}" />
						</div>						
					</div>		
				</div>

				{if and( $SearchButton, $search_extras.facet_fields.0.nameList|count()|gt(0) )}
				<div class="block-search-advanced-container square-box-soft-gray-2">
					<div class="block-search-advanced-link">
						<p class="eztoggle open" id="FilterSearch">
						{if $filtri_selezionati|count()|gt(0)}
						Stai filtrando per:
						{def $stai_filtrando_per="checked='checked'"}
						{else}
						Restringi la ricerca solo a:
						{/if}
						</p>
					

						<div id="FilterSearchPanel">
						<p class="ezjs_toggleCheckboxes no-js-hide" title="Inverti la selezione" >Attiva o disattiva i filtri</p>			
						<div class="filter-container float-break">
						{def $faccette=$search_extras.facet_fields.0.nameList}
						{set $faccette=$faccette|asort()}
						{foreach $faccette as $facetID => $name}
							{if $not_available_facets|contains($facetID)|not()}
							   <label>
								<input value="{$search_extras.facet_fields.0.queryLimit[$facetID]|wash}" 
								       title="{$name|wash}" name="Filtri[]" type="checkbox" {$stai_filtrando_per} />
								{$name|wash} ({$search_extras.facet_fields.0.countList[$facetID]})
							   </label>
							   {set $index = $index|sum(1)}	
							{/if}
						{/foreach}
						</div>
						<input class="defaultbutton" name="SearchButton" type="submit" value="{'Search'|i18n('design/ezwebin/content/search')}" />
					</div>						
					</div>
				</div>
				{/if}

			</div>
		</div>
		</div></div></div>
		<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
		</div>
	</fieldset>
</form>	
{/if}
{/if}


{* FORSE CERCAVI... *}
{if $search_extras.spellcheck_collation}
	{def $spell_url=concat('content/advancedsearch/',$search_text|count_chars()|gt(0)|choose('',concat('?SearchText=',$search_extras.spellcheck_collation|urlencode)))|ezurl}
	<p>Forse intendevi cercare per <b>{concat("<a href='",$spell_url,"'>")}{$search_extras.spellcheck_collation}</a></b> ?</p>
{/if}

{* PAROLE ESCLUSE *}
{if $stop_word_array}
    <p>
    {"The following words were excluded from the search"|i18n("design/base")}:
    {foreach $stop_word_array as $stopWord}
        {$stopWord.word|wash}
        {delimiter}, {/delimiter}
    {/foreach}
    </p>
{/if}


{if $SearchButton}
	{switch name=Sw match=$search_count}
		{case match=0}
			<div class="warning">
				<h2>{'No results were found when searching for "%1".'|i18n("design/ezwebin/content/search",,array($search_text|wash))}</h2>
				{if $search_extras.hasError}{$search_extras.error|wash}{/if}
				<p>{'Search tips'|i18n('design/ezwebin/content/search')}</p>
				<ul>
					<li>{'Check spelling of keywords.'|i18n('design/ezwebin/content/search')}</li>
					<li>{'Try changing some keywords (eg, "car" instead of "cars").'|i18n('design/ezwebin/content/search')}</li>
					<li>{'Try searching with less specific keywords.'|i18n('design/ezwebin/content/search')}</li>
					<li>{'Reduce number of keywords to get more results.'|i18n('design/ezwebin/content/search')}</li>
				</ul>
			</div>
		{/case}
		{case}
			<div class="message-feedback">
			{if $search_text|eq('')}
				<h2>{'Search for "%1" returned %2 matches'|i18n("design/ezwebin/content/search",,array($search_text|wash,$search_count))}</h2>
			{else}
				<h2>La ricerca ha prodotto {$search_count} risultati</h2>
			{/if}
			</div>
		{/case}
	{/switch}

	{if $search_result|count()}
	<table class="list" width="100%" cellspacing="0" cellpadding="0" border="0" summary='{'Search for "%1" returned %2 matches'|i18n("design/ezwebin/content/search",,array($search_text|wash,$search_count))}'>
		<thead>
			<tr>
			<th class="width-1">Oggetto</th>
			<th>Risultato della ricerca</th>
			<th class="width-1">Servizio</th>
			<th>Argomento</th>
			<th>Data di pubblicazione</th>
			</tr>
		</thead>
		<tbody>
		{foreach $search_result as $result sequence array(bglight,bgdark) as $bgColor}
		   {node_view_gui view=ezfind_line sequence=$bgColor content_node=$result}
		{/foreach}
		</tbody>
	</table>

	{include name=Navigator
			 uri='design:navigator/google.tpl'
			 page_uri='content/advancedsearch'
			 page_uri_suffix=concat( '?SearchText=',$search_text|urlencode,'&PhraseSearchText=',$phrase_search_text|urlencode,$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix,"&facet_field=class&SearchButton=Cerca&Servizi[]=",$servizi[0],"&Argomenti[]=",$argomenti[0],"&anno_s[]=",$anno_s[0],"&SubTreeArray[]=",$sub_tree_passed[0],$string_filters,$subfilter_string,"&Sort=",$sort_by,"&Order=",$order_by,"&cond=",$cond )
			 item_count=$search_count
			 view_parameters=$view_parameters
			 item_limit=$page_limit}

	{/if}		 
		 
{/if} {* chiudi SearchButton*}

</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>
