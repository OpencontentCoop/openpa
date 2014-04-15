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

*}

{if is_set($search_included)|not()}{def $search_included=false()}{/if}
{if is_set($search_text)|not()}{def $search_text = ''}{/if}
{if is_set($argomenti)|not()}{def $argomenti=hash(0, 'none')}{/if}
{if is_set($subfilter_arr)|not()}{def $subfilter_arr=array()}{/if}
{if is_set($servizi)|not()}{def $servizi=hash(0, 'none')}{/if}
{if is_set($anno_s)|not()}{def $anno_s=hash(0, 'none')}{/if}

{def $foldersClasses = array( 'folder', 'pagina_sito' )}

{def $subtreearray=ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
{if $foldersClasses|contains( $node.class_identifier )}
	{if $node.data_map.classi_filtro.has_content}
		{def $related_nodes = fetch('content','related_objects', hash('object_id', $node.contentobject_id, 'attribute_identifier', concat( $node.class_identifier, '/subfolders' ) ))}
		{if $related_nodes|count()|gt(0)}
			{set $subtreearray=$related_nodes[0].main_node_id}
		{elseif is_area_tematica()}
			{set $subtreearray=is_area_tematica().node_id}
		{/if}
	{/if}
{elseif $foldersClasses|contains( $node.parent.class_identifier )}
	{if $node.parent.data_map.classi_filtro.has_content}
		{def $related_nodes = fetch('content','related_objects', hash('object_id', $node.parent.contentobject_id, 'attribute_identifier', concat( $node.parent.class_identifier, '/subfolders' ) ))}	
		{if $related_nodes|count()|gt(0)}
			{set $subtreearray=$related_nodes[0].main_node_id}
		{elseif is_area_tematica()}
			{set $subtreearray=is_area_tematica().node_id}
		{/if}
	{else}
		{if is_area_tematica()}
			{set $subtreearray=is_area_tematica().node_id}
		{/if}
	{/if}
{elseif is_area_tematica()}
	{set $subtreearray=is_area_tematica().node_id}
{/if}

{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">
{literal}
$(function() {
	$(".block-search-advanced-link p").click(function () {
		$(this).next().slideToggle("slow");
		$(this).toggleClass('open');
    });
});
{/literal}
</script>

{def $class=''
	 $attributi_da_escludere_dalla_ricerca= openpaini( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca', array())
     $anni = openpaini( 'MotoreDiRicerca', 'RicercaAvanzataSelezionaAnni', array())
}


{set-block variable=$open}
<div class="border-box box-gray block-search">
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


<form id="search-form-box" action="{'content/search'|ezurl('no')}" method="get">
	<fieldset>
		<legend class="block-title"><span>Cerca in {$folder}</span></legend>
{if $search_included}
	
	<div class="content-navigator float-break">
		<div class="content-navigator-previous">
			<div class="content-navigator-arrow"></div>
			<a href="/content/advancedsearch/?SearchText=&facet_field=class&SearchButton=Cerca">Torna alla ricerca generale</a>
		</div>
	</div>
	
{/if}
	{$open}
	<label for="search-string">Ricerca libera</label>
	<input {if $search_included} id="Search" size="20" class="halfbox" {else} id="search-string"{/if} type="text" name="SearchText" value="{$search_text}" />

{if $foldersClasses|contains( $node.class_identifier )}
	{set $class_filters = $node.data_map.classi_filtro.content|explode(',')}
{/if}

{if $class_filters[0]|ne('')}
<div class="block-search-advanced-container square-box-soft-gray-2">
<div class="block-search-advanced-link">

<p {if or( $foldersClasses|contains( $node.class_identifier ), $search_included)}class="open"{/if}>Ricerca avanzata</p>
	{foreach $class_filters as $class_filter}
		{set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter ) )}
	{/foreach}
{* data classi TODO *}

<div class="block-search-advanced {if and( $foldersClasses|contains( $node.class_identifier ), $search_included|not())}hide{/if}">
	{foreach $class.data_map as $attribute}
	{if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
		{switch match=$attribute.data_type_string}
			{case in=array('ezstring','eztext')}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				<input id="{$attribute.identifier}"
					type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
			{/case}
			{case in=array('ezobjectrelationlist')}
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
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				<input id="{$attribute.identifier}" size="5" type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
				{/if}
			{/case}
		{/switch}
	{/if}
	{/foreach}
		<input name="Filtri[]" value="contentclass_id:{$class.id}" type="hidden" />
		<input name="facet_field" value="class" type="hidden" />
		<input name="OriginalNode" value="{$node.node_id}" type="hidden" />
		<input name="SubTreeArray[]" value="{$subtreearray}" type="hidden" />
</div>

</div>
</div>
{/if}

	<input id="search-button-button" class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />
	{$close}
	</fieldset>
</form>
