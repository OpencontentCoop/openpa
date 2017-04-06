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
{def $openpa_search_node = object_handler($node)}

{def $subtreearray=ezini( 'NodeSettings', 'RootNode', 'content.ini' )}
{if $foldersClasses|contains( $node.class_identifier )}
	{if $openpa_search_node.content_virtual.folder}
		{def $related_nodes = fetch('content','related_objects', hash('object_id', $node.contentobject_id, 'attribute_identifier', concat( $node.class_identifier, '/subfolders' ) ))}
		{if $related_nodes|count()|gt(0)}
			{set $subtreearray=$related_nodes[0].main_node_id}
		{elseif is_area_tematica()}
			{set $subtreearray=is_area_tematica().node_id}
		{/if}
	{/if}
{elseif $foldersClasses|contains( $node.parent.class_identifier )}
    {def $openpa_search_parent_node = object_handler($node.parent)}
    {if $openpa_search_parent_node.content_virtual.folder}
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

<div class="widget {$block.view}">
    <div class="widget_title">
        <h3>Cerca in {$folder}</h3>
    </div>
    <div class="widget_content">

<form id="search-form-box" action="{'content/search'|ezurl('no')}" method="get">

	<input placeholder="Ricerca libera" {if $search_included} id="Search" size="20" class="halfbox" {else} id="search-string"{/if} type="text" name="SearchText" value="{$search_text}" />

{if and($foldersClasses|contains( $node.class_identifier ), $openpa_search_node.content_virtual.folder)}
	{set $class_filters = $openpa_search_node.content_virtual.folder.classes}
{/if}

{if $class_filters[0]|ne('')}

<button type="button" class="btn btn-link btn-sm" data-toggle="collapse" data-target="#AdvancedNodePanel">
    Ricerca avanzata
</button>

	{foreach $class_filters as $class_filter}
		{set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter ) )}
	{/foreach}
{* data classi TODO *}

<div id="AdvancedNodePanel" class="collapse">
	{foreach $class.data_map as $attribute}
	{if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
		{switch match=$attribute.data_type_string}
			{case in=array('ezstring','eztext')}
				<div class="form-group">
                <label for="{$attribute.identifier}">{$attribute.name}</label>
				<input class="form-control" id="{$attribute.identifier}"
					type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
                </div>
			{/case}
			{case in=array('ezobjectrelationlist')}
			{/case}
			{case}
			{/case}
			{case in=array('ezinteger')}
				<div class="form-group">
                {if $attribute.identifier|eq('annoxxx')}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
			        <select class="form-control"  id="{$attribute.identifier}" name="anno_s[]">
			                <option value="">Qualsiasi anno</option>
                			{foreach $anni as $anno}
        			        <option {if $anno|eq($anno_s[0])} class="marked" selected="selected"{/if} value="{$anno}">{$anno}</option>
			                {/foreach}
			        </select>
				{else}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				<input class="form-control"  id="{$attribute.identifier}" size="5" type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />
				{/if}
                </div>
			{/case}
		{/switch}
	{/if}
	{/foreach}
		<input name="Filtri[]" value="contentclass_id:{$class.id}" type="hidden" />
		<input name="facet_field" value="class" type="hidden" />
		<input name="OriginalNode" value="{$node.node_id}" type="hidden" />
		<input name="SubTreeArray[]" value="{$subtreearray}" type="hidden" />
</div>

{/if}
<div class="form-group margin-top clearfix">
	<input id="search-button-button" class="defaultbutton pull-right" type="submit" name="SearchButton" value="Cerca" />
</div>

</form>

    </div>
</div>
