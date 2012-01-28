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

{if is_set($search_included)|not()}
    {def $search_included = false()}
{/if}

{if is_set($search_text)|not()}
    {def $search_text = ''}
{/if}

{if is_set($custom_keys)|not()}
    {def $custom_keys = hash( 'is_area_tematica', false() )}
{else}
    {if is_set( $custom_keys[is_area_tematica] )|not()}
        {set $custom_keys = $custom_keys|merge( hash( 'is_area_tematica', false() ) )}
    {/if}
{/if}

{def $filterParameter = array()
     $SubTreeArray = cond( ezhttp( 'SubTreeArray','get','hasVariable' ), ezhttp( 'SubTreeArray', 'get' ), array() )}

{def $subtreearray = array(2) }

{if is_set($subtree)}
    {set $subtreearray = $sub_tree}
{/if}

{if $SubTreeArray}
    {if and( is_array( $SubTreeArray )|not(), $SubTreeArray|ne('') )}
    {set $SubTreeArray = array( $SubTreeArray )}
    {else}
    {def $tempSta = array()}
    {foreach $SubTreeArray as $sta}
        {if and( $sta|ne(2), $sta|ne('') )}
            {set $tempSta = $tempSta|append($sta)}
        {/if}
    {/foreach}
    {set $subtreearray = $tempSta}
    {/if}
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

{def $activeFacetParameters = array()}
{if ezhttp_hasvariable( 'activeFacets', 'get' )}
    {set $activeFacetParameters = ezhttp( 'activeFacets', 'get' )}
{/if}
{*
{if $node.class_identifier|eq('folder')}
	{if is_set( $node.data_map.classi_filtro )}
        {if $node.data_map.classi_filtro.has_content}
            {def $related_nodes = fetch('content','related_objects', hash('object_id', $node.contentobject_id, 'attribute_identifier', 'folder/subfolders'))}
            {if $related_nodes|count()|gt(0)}
                {set $subtreearray=$related_nodes[0].main_node_id}
            {elseif $custom_keys[is_area_tematica]}
                {set $subtreearray=$node.path_array[3]}
            {/if}
        {/if}
    {/if}
{elseif $node.parent.class_identifier|eq('folder')}
	{if is_set( $node.parent.data_map.classi_filtro )}
        {if $node.parent.data_map.classi_filtro.has_content}
            {def $related_nodes = fetch('content','related_objects', hash('object_id', $node.parent.contentobject_id, 'attribute_identifier', 'folder/subfolders'))}	
            {if $related_nodes|count()|gt(0)}
                {set $subtreearray=$related_nodes[0].main_node_id}
            {elseif $custom_keys[is_area_tematica]}
                {set $subtreearray=$node.path_array[3]}
            {/if}
        {/if}
	{else}
		{if $custom_keys[is_area_tematica]}
			{set $subtreearray=$node.path_array[3]}
		{/if}
	{/if}
{elseif $custom_keys[is_area_tematica]}
	{set $subtreearray=$node.path_array[3]}
{/if}
*}


{def $class = ''
	 $attributi_da_escludere_dalla_ricerca  = openpaini( 'Attributi', 'EscludiDaRicerca', array())
	 $anni = openpaini( 'MotoreDiRicerca', 'RicercaAvanzataSelezionaAnni', array() )}

{foreach $class_filters as $class_filter}
    {set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter ) )}
{/foreach}

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

<div class="content-navigator float-break">
	<div class="content-navigator-previous">
		<div class="content-navigator-arrow"></div>
		<a href="/content/advancedsearch/?SearchText=&SearchButton=Cerca">Ricerca generale</a>
	</div>
</div>

<form id="search-form-box" action="{'content/advancedsearch'|ezurl('no')}" method="get">
	<fieldset>
	{$open}
    {if is_array($subtreearray)}
		{set $subtreearray = $subtreearray|unique()} 
        {foreach $subtreearray as $sta}
			<input name="SubTreeArray[]" type="hidden" value="{$sta}" />
		{/foreach}
	{/if}
	<label for="search-string">Ricerca libera</label>
	<input {if $search_included} id="Search" size="20" class="halfbox" {else} id="search-string"{/if} type="text" name="SearchText" value="{$search_text}" />


{if $class_filters[0]|ne('')}
    <div class="block-search-advanced-container square-box-soft-gray-2">
    <div class="block-search-advanced-link">
    
    <p class="{if $activeFacetParameters|count()}close{else}open{/if}">Ricerca avanzata</p>
    {* data classi TODO *}
    
    {def $facets = array()}
    <div class="block-search-advanced{if $activeFacetParameters|count()} hide{/if}">
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
                   {*
                    {if $attribute.identifier|eq('')}
                    {/if}
                    
                    {if $attribute.identifier|eq('servizio')}
                    {set $filterParameter = getFilterParameter( 'submeta_servizio___main_node_id_si' )}
                    <label for="{$attribute.identifier}">{$attribute.name}</label>
                    <select name="filter[submeta_servizio___main_node_id_si]" id="{$attribute.identifier}">
                        <option value="">Qualsiasi servizio</option>
                            {if $node_servizi_attivi}
                            <optgroup  label="{$node_servizi_attivi.name|wash}">
                            {foreach $servizi_attivi as $k => $servizio}
                                <option {if $filterParameter|contains( $servizio.node_id )} class="marked" selected="selected" {/if} value='{$servizio.node_id}'>{$servizio.name|wash}</option>
                            {/foreach}
                            </optgroup>
                            {/if}
                            {if $node_servizi_non_attivi}
                            <optgroup  label="Servizi non attivi">
                            {foreach $servizi_non_attivi as $k => $servizio}
                                <option {if $filterParameter|contains( $servizio.node_id )} class="marked" selected="selected" {/if} value='{$servizio.node_id}'>{$servizio.name|wash}</option>
                            {/foreach}
                            </optgroup>
                            {/if}
                    </select>
                    {/if}
                    
                    {if $attribute.identifier|eq('argomento')}
                    {if $node_argomenti}
                    {set $filterParameter = getFilterParameter( 'submeta_argomento___main_node_id_si-name_s' )}
                    <label for="{$attribute.identifier}">{$attribute.name}</label>
                    <select id="{$attribute.identifier}" name="filter[submeta_argomento___main_node_id_si-name_s']">
                        <option value="">Qualsiasi argomento</option>
                        {def $argomenti_tutti=array()}
                        {foreach $margomenti as $k => $margomento}
                        <optgroup  label="{$margomento.name|wash}">
                            {set $argomenti_tutti = fetch( content, list, hash(  parent_node_id, $margomento.node_id,
                                                                                'sort_by', array('name', true()),
                                                                                'class_filter_type',  'include',
                                                                                'class_filter_array', array( 'argomento' )
                                                                            ))}
                            {foreach $argomenti_tutti as $k => $argomento}
                                <option {if $filterParameter|contains( $argomento.node_id )}  class="marked" selected="selected" {/if} value='{$argomento.node_id}'>{$argomento.name|wash}</option>
                            {/foreach}
                        </optgroup>
                        {/foreach}
                        {undef $argomenti_tutti}
                    </select>
                    {/if}
                    {/if}
                    *}
                {/case}
                
                {case}
                {/case}
                
                {case in=array('ezinteger')}
                    {if $attribute.identifier|eq('annoxxx')}
                    <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <select id="{$attribute.identifier}" name="anno_s[]">
                                <option value="">Qualsiasi anno</option>
                                {foreach $anni as $anno}
                                <option {if $anno|eq($anno_s[0])} class="marked" selected="selected"{/if} value="{$anno}">{$anno}</option>
                                {/foreach}
                        </select>
                    {else}
                        {set $filterParameter = getFilterParameter( concat( 'attr_', $attribute.identifier, '_si' ) )}
                        <label for="{$attribute.identifier}">{$attribute.name}</label>
                        <input id="{$attribute.identifier}" size="5" type="text" name="filter[{concat( 'attr_', $attribute.identifier, '_si' )}]" value="{if is_set($filterParameter[0])}{$filterParameter[0]}{/if}" />
                    {/if}
                {/case}
                
                {*case in=array('ezdatetime', 'ezdate')}
                    <label for="{$attribute.identifier}">{$attribute.name}</label>
                    <input id="{$attribute.identifier}" size="5" type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
                {/case*}
                
                {*case in=array('ezgmaplocation')}
                    {if $attribute.identifier|eq('gps')}
                    
                    {def $latitude = cond( ezhttp( 'latitude','get','hasVariable' ), ezhttp( 'latitude', 'get' ) )
                         $longitude = cond( ezhttp( 'longitude','get','hasVariable' ), ezhttp( 'longitude', 'get' ) )
                         $address = cond( ezhttp( 'address','get','hasVariable' ), ezhttp( 'address', 'get' ) )}
                    
                    <div class="no-js-hide">
                    
                        <label for="{$attribute.identifier}">Ordina per {$attribute.name}</label>
    
                        {run-once}
                        <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={ezini('SiteSettings',concat('GMapsKey_', ezsys( 'hostname' )|explode('.')|implode('_') ) )}&amp;sensor=true" type="text/javascript"></script>
                        <script type="text/javascript">
                        {literal}
                        function eZGmapLocation_MapControl( attributeId, latLongAttributeBase )
                        {
                            var mapid = 'ezgml-map-' + attributeId, latid  = 'ezcoa-' + latLongAttributeBase + '_latitude', longid = 'ezcoa-' + latLongAttributeBase + '_longitude';
                            var geocoder = null, addressid = 'ezgml-address-' + attributeId;
                        
                            var showAddress = function()
                            {
                                var address = document.getElementById( addressid ).value;
                                if ( geocoder )
                                {
                                    geocoder.getLatLng( address, function( point )
                                    {
                                        if ( !point )
                                        {
                                            alert( address + " non trovato" );
                                        }
                                        else
                                        {
                                            map.setCenter( point, 13 );
                                            map.clearOverlays();
                                            map.addOverlay( new GMarker( point ) );
                                            updateLatLngFields( point );
                                        }
                                    });
                                }
                            };
                            
                            var updateLatLngFields = function( point )
                            {
                                document.getElementById(latid).value = point.lat();
                                document.getElementById(longid).value = point.lng();
                            };
                        
                            var getUserPosition = function()
                            {
                                navigator.geolocation.getCurrentPosition( function( position )
                                {
                                    
                                    var location = '', point = new GLatLng(  position.coords.latitude, position.coords.longitude );
                        
                                    if ( navigator.geolocation.type == 'Gears' && position.gearsAddress )
                                        location = [position.gearsAddress.city, position.gearsAddress.region, position.gearsAddress.country].join(', ');
                                    else if ( navigator.geolocation.type == 'ClientLocation' )
                                        location = [position.address.city, position.address.region, position.address.country].join(', ');
                                    else
                                        location = [position.address.street, position.address.streetNumber, position.address.postalCode, position.address.city, position.address.region, position.address.country].join(' ');
                        
                                    document.getElementById( addressid ).value = location;
                                    map.setCenter( point, 13 );
                                    map.clearOverlays();
                                    map.addOverlay( new GMarker(point) );
                                    updateLatLngFields( point );
                                },
                                function( e )
                                {
                                    alert( 'Errore: ' + e.message );
                                },
                                { 'gearsRequestAddress': true });
                            };
                        
                            if (GBrowserIsCompatible())
                            {
                                var startPoint = null, zoom = 0, map = new GMap2( document.getElementById( mapid ) );
                                if ( document.getElementById( latid ).value && document.getElementById( latid ).value != 0 )
                                {
                                    startPoint = new GLatLng( document.getElementById( latid ).value, document.getElementById( longid ).value );
                                    zoom = 13;
                                }
                                else
                                {
                                    startPoint = new GLatLng(0,0);
                                }
                                map.addControl( new GSmallMapControl() );
                                //map.addControl( new GMapTypeControl() );
                                map.setCenter( startPoint, zoom );
                                map.addOverlay( new GMarker( startPoint ) );
                                geocoder = new GClientGeocoder();
                                GEvent.addListener( map, 'click', function( newmarker, point )
                                {
                                    map.clearOverlays();
                                    map.addOverlay( new GMarker( point ) );
                                    map.panTo( point );
                                    updateLatLngFields( point );
                                    document.getElementById( addressid ).value = '';
                                });
                        
                                document.getElementById( 'ezgml-address-button-' + attributeId ).onclick = showAddress;
                        
                                if ( navigator.geolocation )
                                {
                                    document.getElementById( 'ezgml-mylocation-button-' + attributeId ).onclick = getUserPosition;
                                    document.getElementById( 'ezgml-mylocation-button-' + attributeId ).className = 'button';
                                    document.getElementById( 'ezgml-mylocation-button-' + attributeId ).disabled = false;
                                }
                            }
                        }
                        {/literal}
                        </script>
                        {/run-once}
                        
                        <script type="text/javascript">
                        <!--
                        
                        if ( window.addEventListener )
                            window.addEventListener('load', function(){ldelim} eZGmapLocation_MapControl( {$attribute.id}, "attribute" ) {rdelim}, false);
                        else if ( window.attachEvent )
                            window.attachEvent('onload', function(){ldelim} eZGmapLocation_MapControl( {$attribute.id}, "attribute" ) {rdelim} );
                        
                        -->
                        </script>
    
                        <input type="text" id="ezgml-address-{$attribute.id}" size="62" name="address" value="{$address}"/>
                        <div class="float-break">
                            <input style="font-size:0.77em;width:49% !important;float:left;padding:3px 0;margin:3px 0;" class="button" type="button" id="ezgml-address-button-{$attribute.id}" value="Cerca indirizzo"/>
                            <input style="font-size:0.77em;width:49% !important;float:right;padding:3px 0;margin:3px 0;" class="button-disabled" type="button" id="ezgml-mylocation-button-{$attribute.id}" value="Posizione corrente" onclick="javascript:void( null ); return false" disabled="disabled" />
                        </div>
                        <div id="ezgml-map-{$attribute.id}" style="width: 100%; height: 200px;"></div>
                        <div class="float-break">
                            <input id="ezcoa-attribute_latitude" type="hidden" name="latitude" value="{$latitude}" />
                            <input id="ezcoa-attribute_longitude" type="hidden" name="longitude" value="{$longitude}" />
                        </div>
                    
                    </div>

                    {/if}
                {/case*}
                
            {/switch}
        {/if}
        {/foreach}
    
            <input name="filter[]" value="contentclass_id:{$class.id}" type="hidden" />
            <input name="OriginalNode" value="{$node.node_id}" type="hidden" />
    </div>
    </div>
    </div>

    {if count($facets)}
        {def $filters_parameters = getFilterParameters()
             $cleanFilterParameters = array()
             $tempFilter = false()}
        
        {def $query = cond( ezhttp( 'SearchText','get','hasVariable' ), ezhttp( 'SearchText', 'get' ), '' )}
    
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
        
        {def $baseURI=concat( '/content/advancedsearch?', 'OriginalNode=', $node.node_id, '&SearchText=', $search_text, '&SubTreeArray[]=', $subtreearray|implode( '&SubTreeArray[]=' ) )}
        {def $uriSuffix = $filters_parameters|getFilterUrlSuffix()}
        {foreach $activeFacetParameters as $facetField => $facetValue}
            {set $uriSuffix = concat( $uriSuffix, '&activeFacets[', $facetField, ']=', $facetValue )}
        {/foreach}
        
{set-block variable=$faccette}          
        {if $activeFacetParameters|count()}
            <strong>Stai filtrando per:</strong>
            <ul id="active-facets-list">
            {foreach $facets as $key => $facet}
                {if array_keys( $activeFacetParameters )|contains( concat( $facet['field'], ':', $facet['name']  ) )}
                    {foreach $filters_search_extras.facet_fields.$key.nameList as $key2 => $facetName}
                        {if eq( $activeFacetParameters[concat( $facet['field'], ':', $facet['name'] )], $facetName )}
                            <li class="float-break">
                                <a class="close-facet" href={concat( $baseURI, $uriSuffix|explode( concat( '&filter[]=', $filters_search_extras.facet_fields.$key.queryLimit[$key2] ) )|implode( '' )|explode( concat( '&activeFacets[', $facet['field'], ':', $facet['name'], ']=', $facetName ) )|implode( '' ) )|ezurl}><span>[x]</span></a>
                                <span class="text-facet"><strong>{$facet['name']}</strong>: {def $node =fetch( 'content', 'node', hash( 'node_id', $facetName ))}
                                {if $node}{$node.name|wash()}{else}{$facetName}{/if}</span>
                            </li>
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
            </ul>
        {/if}

        {foreach $facets as $key => $facet}

            {if array_keys( $activeFacetParameters )|contains( concat( $facet['field'], ':', $facet['name']  ) )|not}

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
                
            {/if}
        {/foreach}
{/set-block}
        {if ne($faccette|trim(), '')}
            <div class="block-search-advanced-container square-box-soft-gray-2">
            <div class="block-search-advanced-link">
            <p class="{if $activeFacetParameters|count()}open{/if}">Filtri di ricerca</p>
            <div class="block{if $activeFacetParameters|count()|not()} hide{/if}">
                {$faccette}
            </div>
            </div>
            </div>
        {/if}  
    {/if}   

{/if}

	<input id="search-button-button" class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
	{$close}
	</fieldset>
</form>
