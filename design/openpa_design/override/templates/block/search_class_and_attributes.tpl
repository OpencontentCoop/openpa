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

{def $subtreearray=2 $customs=$block.custom_attributes }

{if $customs.node_id|gt(0)}
                {def $node_id=$customs.node_id}
        {else}
                {def $node_id=2}
{/if}

{if $customs.class|ne('')}
                {def $class=$customs.class}
        {else}
                {def $class='user'}
{/if}

{if is_set($customs.limit)}
                {def $limit=$customs.limit}
        {else}
                {def $limit=10}
{/if}


{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">
{literal}
$(function() {
	$("form#search-form-box-{/literal}{$block.id}{literal} .block-search-advanced-link p").click(function () {
		$(this).next().slideToggle("slow");
		$(this).toggleClass('open');
    });
});
{/literal}
</script>

{def 	
	$node = fetch(content,node,hash(node_id,$node_id))
    $attributi_da_escludere_dalla_ricerca= ezini( 'GestioneAttributi', 'attributi_da_escludere_dalla_ricerca', 'content.ini')
	$node_id_servizi_attivi = ezini( 'Servizi', 'attivi', 'content.ini')
	$node_id_servizi_non_attivi = ezini( 'Servizi', 'non_attivi', 'content.ini')
	$node_id_argomenti = ezini( 'Argomenti', 'argomenti', 'content.ini')
	$node_servizi_attivi = fetch(content,node,hash(node_id,$node_id_servizi_attivi))
	$node_servizi_non_attivi = fetch(content,node,hash(node_id,$node_id_servizi_non_attivi))
	$node_argomenti = fetch(content,node,hash(node_id,$node_id_argomenti))
	$servizi_attivi=fetch(content, list, hash(parent_node_id, $node_servizi_attivi.node_id, 'sort_by', array('name', true()),
                                        'class_filter_type',  'include', 'class_filter_array', array( 'servizio')))
	$servizi_non_attivi=fetch(content, list, hash(parent_node_id, $node_servizi_non_attivi.node_id, 'sort_by', array('name', true()),
                                        'class_filter_type',  'include', 'class_filter_array', array( 'servizio')))
	$anni = array('2010','2009','2008','2007','2006','2005','2004','2003','2002','2001','2000')
	$margomenti=fetch(content, list, hash(parent_node_id, $node_argomenti.node_id, 'sort_by', array('name', true()),
                               'class_filter_type',  'include', 'class_filter_array', array( 'macroargomento')))	
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


<form id="search-form-box-{$block.id}" action="{'content/search'|ezurl('no')}" method="get">
	<fieldset>
		<legend class="block-title"><span>{$block.name}</span></legend>
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

	{def $class_filters = $class|explode(',')}

{if $class_filters[0]|ne('')}
<div class="block-search-advanced-container square-box-soft-gray-2">
<div class="block-search-advanced-link">

<p {if or($node.class_identifier|eq('folder'), $search_included)}class="open"{/if}>Ricerca avanzata</p>
	{foreach $class_filters as $class_filter}
		{set $class = fetch( 'content', 'class', hash( 'class_id', $class_filter ) )}
	{/foreach}

<div class="block-search-advanced {if and($node.class_identifier|ne('folder'), $search_included|not())}hide{/if}">
	{foreach $class.data_map as $attribute}
	{if and($attribute.is_searchable, $attribute.identifier|ne('errors'), $attributi_da_escludere_dalla_ricerca|contains($attribute.identifier)|not())}
		{switch match=$attribute.data_type_string}
			{case in=array('ezstring','eztext')}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				{*<input id="{$attribute.identifier}"
					type="text" name="subfilter_arr[{$class.identifier}/{$attribute.identifier}]" value="{if is_set($subfilter_arr[concat($class.identifier,'/',$attribute.identifier)])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />*}

				<input id="{$attribute.identifier}" 
					type="text" name="filter[attr_{$attribute.identifier}_t]" value="{if is_set($subfilter[concat('attr_',$attribute.identifier,'_t')])}{$subfilter_arr[concat($class.identifier,'/',$attribute.identifier)]}{/if}" />


			{/case}
			{case in=array('ezobjectrelationlist')}
				{if $attribute.identifier|eq('')}
				{/if}
				{if $attribute.identifier|eq('servizio')}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				<select name="Servizi[]" id="{$attribute.identifier}">
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
				{/if}
				{if $attribute.identifier|eq('argomento')}
				<label for="{$attribute.identifier}">{$attribute.name}</label>
				<select id="{$attribute.identifier}" name="Argomenti[]" {if $argomenti[0]}class="marked"{/if}>
					<option value="">Qualsiasi argomento</option>
					{def $argomenti_tutti=array()}
					{foreach $margomenti as $k => $margomento}
						<optgroup  label="{$margomento.name|wash}">
						{set $argomenti_tutti=fetch(content, list,
								hash(parent_node_id, $margomento.node_id,
                                              				'sort_by', array('name', true()),
									'class_filter_type',  'include', 
									'class_filter_array', array( 'argomento' )))}
						{if $argomenti_tutti|count()|gt(0)}
				                       {foreach $argomenti_tutti as $k => $argomento}
							<option {if and(concat('"',$argomento.name,'"')|eq($argomenti[0]), $search_included)} class="marked" selected="selected"{/if} value='"{$argomento.name}"'>{$argomento.name|wash}</option>
							{/foreach}
						{/if}
						</optgroup>
					{/foreach}
					{undef $argomenti_tutti}
				</select>
				{/if}
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
		{*<input name="Filtri[]" value="contentclass_id:{$class.id}" type="hidden" />*}
		<input name="filter[]" value="contentclass_id:{$class.id}" type="hidden" />
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
