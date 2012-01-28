{ezscript_require( array( 'ezjsc::yui2', 'ezjsc::jquery', 'ezajax_autocomplete.js' ) )}

{def $classes=fetch( 'class', 'list' )
     $class_ids = array()
     $exclude_classes = openpaini( 'Classi', 'SearchEscludiDaiRisultati', array() )}
{foreach $classes as $class}
    {if $exclude_classes|contains( $class.identifier )|not()}
        {set $class_ids = $class_ids|merge( array( $class.id ) )}
    {/if}
{/foreach}          

{def $search=false()
     $search_hash=false()
     $search_extras=false()
     $ini_not_available_facets 	= openpaini( 'Classi', 'SearchEscludiDaFaccette', array() )
     $not_available_facets      = array()
}

{foreach $classes as $class}
    {if $ini_not_available_facets|contains( $class.identifier )}
        {set $not_available_facets = $not_available_facets|append( $class.id )}
    {/if}
{/foreach}   


{if $search_text|eq( 'Search'|i18n('design/ezwebin/pagelayout') )}
{set $search_text=''}
{/if}
     
{if $use_template_search}
    {set $page_limit=10}

    {def $activeFacetParameters = array()}
    {if ezhttp_hasvariable( 'activeFacets', 'get' )}
        {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
    {/if}

    {def $dateFilter=0}
    {if ezhttp_hasvariable( 'dateFilter', 'get' )}
        {set $dateFilter = ezhttp( 'dateFilter', 'get' )}
        {switch match=$dateFilter}
            {case match=1}
                {def $dateFilterLabel="Last day"|i18n("design/standard/content/search")}
            {/case}
            {case match=2}
                {def $dateFilterLabel="Last week"|i18n("design/standard/content/search")}
            {/case}
            {case match=3}
                {def $dateFilterLabel="Last month"|i18n("design/standard/content/search")}
            {/case}
            {case match=4}
                {def $dateFilterLabel="Last three months"|i18n("design/standard/content/search")}
            {/case}
            {case match=5}
                {def $dateFilterLabel="Last year"|i18n("design/standard/content/search")}
            {/case}
        {/switch}
    {/if}
    

{*hash( 'field', 'subattr_argomento___name____s', 'name', 'Argomento', 'limit', 10 ),
hash( 'field', 'subattr_categoria_sindacale___name____s', 'name', 'Categoria', 'limit', 10 ),
hash( 'field', 'subattr_ente_sindacale___name____s', 'name', 'Ente', 'limit', 10 ),
hash( 'field', 'subattr_unione_sindacale___name____s', 'name', 'Ust', 'limit', 10 )*}

    {*def $filterParameters = fetch( 'ezfind', 'filterParameters' )*}
    {def $filterParameters = getFilterParameters()
         $defaultSearchFacets = array( hash( 'field', 'class', 'name', 'Tipologia di contenuto', 'limit', 20 ) )}
    {* def $facetParameters=$defaultSearchFacets|array_merge_recursive( $activeFacetParameters ) *}

    {set $search_hash=hash( 'query', $search_text,
                              'offset', $view_parameters.offset,
                              'class_id', $class_ids,
                              'limit', $page_limit,
                              'sort_by', hash( 'score', 'desc' ),
                              'facet', $defaultSearchFacets,
                              'filter', $filterParameters,
                              'publish_date', $dateFilter,
                              'spell_check', array( true() )
                             )
         
         $search=fetch( ezfind, search, $search_hash )
         $search_result=$search['SearchResult']
         $search_count=$search['SearchCount']
         $search_extras=$search['SearchExtras']
         $stop_word_array=$search['StopWordArray']
         $search_data=$search
    }

    {if $search_extras.hasError}
    {debug-log var=$search_extras msg='Server Error'}
    {/if}

{/if}

{def $baseURI=concat( '/content/search?SearchText=', $search_text )}

{def $uriSuffix = ''}
{foreach $activeFacetParameters as $facetField => $facetValue}
    {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
{/foreach}

{*foreach $filterParameters as $name => $value}
    {set $uriSuffix = concat( $uriSuffix, '&filter[]=', $name, ':', $value )}
{/foreach*}

{set $uriSuffix = concat( $uriSuffix, $filterParameters|getFilterUrlSuffix() )}

{if gt( $dateFilter, 0 )}
    {set $uriSuffix = concat( $uriSuffix, '&dateFilter=', $dateFilter )}
{/if}

<div class="border-box">
<div class="border-content">
<div class="content-search">

<form action={"/content/search/"|ezurl} method="get">

    <div class="attribute-header">
        <h1>Cerca nel sito</h1>        
    </div>
    
    <div id="ezautocomplete" class="text-center block">
        <input class="halfbox" type="text" size="20" name="SearchText" id="Search" value="{$search_text|wash}" />
        <input class="button" name="SearchButton" type="submit" value="{'Search'|i18n('design/ezwebin/content/search')}" />
        <a href="{'content/advancedsearch'|ezurl(no)}" title="Link alla maschera di ricerca avanzata">Ricerca avanzata</a>
        <div id="ezautocompletecontainer"></div>
    </div>

    {if $search_extras.spellcheck_collation}
         {def $spell_url=concat('/content/search/',$search_text|count_chars()|gt(0)|choose('',concat('?SearchText=',$search_extras.spellcheck_collation|urlencode)))|ezurl}
         <div class="block"><p class="text-center"><em>Forse cercavi</em> <b>{concat("<a href=",$spell_url,">")}{$search_extras.spellcheck_collation}</a></b> ?</p></div>
    {/if}

    {switch name=Sw match=$search_count}
        {case match=0}
        
        <div class="block warning text-center">
            <h2>{'No results were found when searching for "%1".'|i18n("design/ezwebin/content/search",,array($search_text|wash))}</h2>
            {if $search_extras.hasError}
                <p>Il server non &egrave; attivo</p>
            {else}
                <p><a href="{'content/advancedsearch'|ezurl(no)}" title="Link alla maschera di ricerca avanzata">Prova la ricerca avanzata</a></p>
            {/if}
        </div>
        
        {/case}
        {case}
        
        <div id="search_controls">
            <h2 class="blocktitle">Filtra i risultati della ricerca</h2>
            <div class="block-content">
                {*def $activeFacetsCount=0*}
                <ul id="active-facets-list" class="menu">
                {foreach $defaultSearchFacets as $key => $defaultFacet}
                    {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )}
                        {foreach $search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                            {if eq( $activeFacetParameters[concat( $defaultFacet['field'], ':', $defaultFacet['name'] )], $facetName )}
                                {*def $activeFacetsCount=sum( $key, 1 )*}
                                {def $suffix=$uriSuffix|explode( concat( '&filter[]=', $search_extras.facet_fields.$key.queryLimit[$key2]|addQuoteOnFilter ) )|implode( '' )|explode( concat( '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName ) )|implode( '' )}
                                <li class="float-break">
                                    <a class="close-facet" href={concat( $baseURI, $suffix )|ezurl}><span>[x]</span></a>
                                    <span class="text-facet"><strong>{$defaultFacet['name']}</strong>: {$facetName}</span>
                                </li>
                            {/if}
                        {/foreach}
                    {/if}
                {/foreach}
        
                {* handle date filter here, manually for now. Should be a facet later on *}
                {if gt( $dateFilter, 0 )}
                    <li class="float-break">
                       {*set $activeFacetsCount=$activeFacetsCount|inc*}
                       {def $suffix=$uriSuffix|explode( concat( '&dateFilter=', $dateFilter ) )|implode( '' )}
                        <a class="close-facet" href={concat( $baseURI, $suffix )|ezurl}><span>[x]</span></a>
                        <span class="text-facet"><strong>Data di pubblicazione</strong>: {$dateFilterLabel}</span>
                    </li>
                {/if}
        
                {*if ge( $activeFacetsCount, 2 )}
                    <li>
                        <a class="close-facet" href={$baseURI|ezurl}>[x]</a>
                        <span class="text-facet"><em>Rimuovi tutti i filtri</em></span>
                    </li>
                {/if*}
                </ul>
                <ul id="facet-list" class="menu">
                {foreach $defaultSearchFacets as $key => $defaultFacet}
                    {if array_keys( $activeFacetParameters )|contains( concat( $defaultFacet['field'], ':', $defaultFacet['name']  ) )|not}
                        {if $search_extras.facet_fields.$key.nameList|count()}
                        <li>
                            <span><strong>{$defaultFacet['name']}</strong></span>
                            <ul>
                              {foreach $search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                                  {if ne( $key2, '' )}
                                  {if $not_available_facets|contains( $key2 )|not()}
                                  <li>
                                      <a href={concat( $baseURI, '&filter[]=', $search_extras.facet_fields.$key.queryLimit[$key2]|addQuoteOnFilter, '&activeFacets[', $defaultFacet['field'], ':', $defaultFacet['name'], ']=', $facetName, $uriSuffix )|ezurl}>
                                      {$facetName}</a> ({$search_extras.facet_fields.$key.countList[$key2]})
                                  </li>
                                  {/if}
                                  {/if}
                              {/foreach}
                            </ul>
                        </li>
                        {/if}   
                    {/if}
                {/foreach}
        
                {* date filtering here. Using a simple filter for now. Should use the date facets later on *}
                {if eq( $dateFilter, 0 )}
                    <li>
                        <span {*style="background-color: #F2F1ED"*}><strong>Data di pubblicazione</strong></span>
                        <ul>
                          <li>
                              <a href={concat( $baseURI, '&dateFilter=1', $uriSuffix )|ezurl}>{"Last day"|i18n("design/standard/content/search")}</a>
                          </li>
                          <li>
                              <a href={concat( $baseURI, '&dateFilter=2', $uriSuffix )|ezurl}>{"Last week"|i18n("design/standard/content/search")}</a>
                          </li>
                          <li>
                              <a href={concat( $baseURI, '&dateFilter=3', $uriSuffix )|ezurl}>{"Last month"|i18n("design/standard/content/search")}</a>
                          </li>
                          <li>
                              <a href={concat( $baseURI, '&dateFilter=4', $uriSuffix )|ezurl}>{"Last three months"|i18n("design/standard/content/search")}</a>
                          </li>
                          <li>
                              <a href={concat( $baseURI, '&dateFilter=5', $uriSuffix )|ezurl}>{"Last year"|i18n("design/standard/content/search")}</a>
                          </li>
                        </ul>
                    </li>
                 {/if}
                </ul>
            </div>
        </div>

        <div id="search_results">
            <h2 class="blocktitle">{'Search for "%1" returned %2 matches'|i18n("design/ezwebin/content/search",,array($search_text|wash,$search_count))}</h2>             
            {include name=Navigator
                     uri='design:navigator/google.tpl'
                     page_uri='/content/search'
                     page_uri_suffix=concat('?SearchText=',$search_text|urlencode,$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix )
                     item_count=$search_count
                     view_parameters=$view_parameters
                     item_limit=$page_limit}
            
            <div class="content-view-children">
            
            {foreach $search_result as $index => $result sequence array( 'light', 'dark' ) as $style}
               {node_view_gui view=ezfind_line style=$style content_node=$result}
            {/foreach}
            </div>
            
            {include name=Navigator
                     uri='design:navigator/google.tpl'
                     page_uri='/content/search'
                     page_uri_suffix=concat('?SearchText=',$search_text|urlencode,$search_timestamp|gt(0)|choose('',concat('&SearchTimestamp=',$search_timestamp)), $uriSuffix )
                     item_count=$search_count
                     view_parameters=$view_parameters
                     item_limit=$page_limit}
        </div>
        {/case}
{/switch}
</form>

</div>

{*<p class="small"><em>{'Search took: %1 msecs, using '|i18n('ezfind',,array($search_extras.responseHeader.QTime|wash))}{$search_extras.engine}</em></p>
$search|attribute(show,2)*}

<script language="JavaScript" type="text/javascript">
<!--{literal}
    // toggle block
    function ezfToggleBlock( id )
    {
        var value = (document.getElementById(id).style.display == 'none') ? 'block' : 'none';
        ezfSetBlock( id, value );
        ezfSetCookie( id, value );
    }

    function ezfSetBlock( id, value )
    {
        var el = document.getElementById(id);
        if ( el != null )
        {
            el.style.display = value;
        }
    }

    function ezfTrim( str )
    {
        return str.replace(/^\s+|\s+$/g, '') ;
    }

    function ezfGetCookie( name )
    {
        var cookieName = 'eZFind_' + name;
        var cookie = document.cookie;

        var cookieList = cookie.split( ";" );

        for( var idx in cookieList )
        {
            cookie = cookieList[idx].split( "=" );

            if ( ezfTrim( cookie[0] ) == cookieName )
            {
                return( cookie[1] );
            }
        }

        return 'none';
    }

    function ezfSetCookie( name, value )
    {
        var cookieName = 'eZFind_' + name;
        var expires = new Date();

        expires.setTime( expires.getTime() + (365 * 24 * 60 * 60 * 1000));

        document.cookie = cookieName + "=" + value + "; expires=" + expires + ";";
    }
{/literal}--></script>

<script language="JavaScript" type="text/javascript">
jQuery('#ezautocompletecontainer').css('width', jQuery('input#Search').width());
var ezAutoHeader = eZAJAXAutoComplete();
ezAutoHeader.init({ldelim}

    url: "{'ezjscore/call/ezfind::autocomplete'|ezurl('no')}",
    inputid: 'Search',
    containerid: 'ezautocompletecontainer',
    minquerylength: {ezini( 'AutoCompleteSettings', 'MinQueryLength', 'ezfind.ini' )},
    resultlimit: {ezini( 'AutoCompleteSettings', 'Limit', 'ezfind.ini' )}

{rdelim});

<!--{literal}
ezfSetBlock( 'ezfFacets', ezfGetCookie( 'ezfFacets' ) );
ezfSetBlock( 'ezfHelp', ezfGetCookie( 'ezfHelp' ) );
{/literal}--></script>

</div>
</div>