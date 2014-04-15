<form id="search-form-{$block.id}" action="{'ezajax/search'|ezurl('no')}" method="post">
<fieldset>
	<legend class="block-title"><span>{$block.name}</span></legend>
        <div class="border-box box-gray block-search">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
            <input id="search-string-{$block.id}" type="text" name="SearchStr" value="" />
            <input id="search-button-{$block.id}" class="button" type="submit" name="SearchButton" value="Search" />
        
        <div id="search-results-{$block.id}"></div>
        
        {ezscript_require( array( 'ezjsc::yui3', 'ezjsc::yui3io', 'ezajaxsearch.js' ) )}
        
        <script type="text/javascript">
        eZAJAXSearch.cfg = {ldelim}
                                searchstring: '#search-string-{$block.id}',
                                searchbutton: '#search-button-{$block.id}',
                                searchresults: '#search-results-{$block.id}',
                                resulttemplate: '<div class="result-item float-break"><div class="item-title"><a href="{ldelim}url_alias{rdelim}">{ldelim}title{rdelim}</a></div><div class="item-published-date">[{ldelim}class_name{rdelim}] {ldelim}date{rdelim}</div></div>'
                           {rdelim};
        eZAJAXSearch.init();
        </script>
        
        
        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
        </div>
</fieldset>
</form>
