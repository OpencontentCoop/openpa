<div class="widget {$block.view}">
    <div class="widget_title">
        <h3>{$block.name|wash()}</h3>
    </div>
    <div class="widget_content">


    <form id="search-form-{$block.id}" action="{'ezajax/search'|ezurl('no')}" method="post">
        <fieldset>
            <legend class="hide">{$block.name}</legend>
            <input id="search-string-{$block.id}" type="text" name="SearchStr" value="" />
            <input id="search-button-{$block.id}" class="button" type="submit" name="SearchButton" value="Search" />

            <ul id="search-results-{$block.id}" class="list-unstyled"></ul>

            {ezscript_require( array( 'ezjsc::yui3', 'ezjsc::yui3io', 'ezajaxsearch.js' ) )}

            <script type="text/javascript">
            eZAJAXSearch.cfg = {ldelim}
                                    searchstring: '#search-string-{$block.id}',
                                    searchbutton: '#search-button-{$block.id}',
                                    searchresults: '#search-results-{$block.id}',
                                    resulttemplate: '<li><a href="{ldelim}url_alias{rdelim}">{ldelim}title{rdelim}</a><small>[{ldelim}class_name{rdelim}] {ldelim}date{rdelim}</small></li>'
                               {rdelim};
            eZAJAXSearch.init();
            </script>


        </fieldset>
    </form>

    </div>
</div>
