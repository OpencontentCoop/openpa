{ezscript_require(array( 'ezjsc::jquery' ) )}
<script type="text/javascript">{literal}
$(document).ready(function(){  
	var search = "{/literal}{'Search'|i18n('design/ezwebin/pagelayout')}{literal}";
	function hide_text(){
		if ($(this).val() == search)
			$(this).val('');
	}
	function show_text(){
		if ($(this).val() == '')
			$(this).val(search);
	}
	$('#searchbox_text').val(search);
	$('#searchbox_text').bind('focus', hide_text)
	$('#searchbox_text').bind('blur',  show_text)
});
{/literal}</script>
<div id="searchbox">
    <form action={"/content/search"|ezurl}>
        <fieldset>
            <legend class="hide">Strumenti di ricerca</legend>
            <label for="searchbox_text" class="hide">{'Search'|i18n('design/ezwebin/pagelayout')}</label>
            {if $pagedata.is_edit}
                <input disabled="disabled" id="searchbox_text" name="SearchText" type="text" value="" size="12" />
                <button id="searchbox_submit" type="submit" class="button-disbled searchbutton">{'Search'|i18n('design/ezwebin/pagelayout')}</button>
            {else} 
                <input id="searchbox_text" name="SearchText" type="text" value="{'Search'|i18n('design/ezwebin/pagelayout')}" size="12" />
                <input id="facet_field" name="facet_field" value="class" type="hidden" />
                <input type="hidden" value="Cerca" name="SearchButton" />
                <button name="SearchButton" value="Cerca" id="searchbox_submit" type="submit" class="button searchbutton">{'Search'|i18n('design/ezwebin/pagelayout')}</button>
                {if eq( $ui_context, 'browse' )}
                    <input name="Mode" type="hidden" value="browse" />
                {/if}
            {/if}
        
            {if is_area_tematica()}
                <input type="hidden" value="{is_area_tematica().node_id}" name="SubTreeArray[]" />
            {/if}
        
        </fieldset>
    </form>
    
    {if openpaini( 'LinkSpeciali', 'NodoIstruzioniRicerca' )}
        {def $link_istruzioni_ricerca = fetch('content','node', hash( 'node_id', openpaini('LinkSpeciali', 'NodoIstruzioniRicerca') ))}
        <div id="searchbox_istruzioni">
            <a href={$link_istruzioni_ricerca.url_alias|ezurl()} title="Guarda il video-guida su come sfruttare al massimo il motore di ricerca del sito">
                Istruzioni Ricerca
            </a>
        </div>
    {/if}

</div>
