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

{$open}
<form id="search-form-{$block.id}" action="{'ezajax/search'|ezurl('no')}" method="post">
    <fieldset>
		<legend class="block-title"><span>{$block.name}</span></legend>        
        <input id="search-string-{$block.id}" type="text" name="SearchStr" value="" />
        <input id="search-button-{$block.id}" class="defaultbutton" type="submit" name="SearchButton" value="Cerca" />        
	</fieldset>
</form>


<ul id="search-results-{$block.id}"></ul>
{ezscript_require( array( 'ezjsc::yui3', 'ezjsc::yui3io', 'ezajaxsearch.js' ) )}

<script type="text/javascript">
eZAJAXSearch.cfg = {ldelim}
                        searchstring: '#search-string-{$block.id}',
                        searchbutton: '#search-button-{$block.id}',
                        searchresults: '#search-results-{$block.id}',
                        resulttemplate: '<li class="item-title"><small>{ldelim}class_name{rdelim}</small><br/><a href="{ldelim}url_alias{rdelim}">{ldelim}title{rdelim}</a></li>'
                   {rdelim};
eZAJAXSearch.init();
</script>
{$close}