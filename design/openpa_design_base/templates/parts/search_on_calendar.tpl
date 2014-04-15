{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js', 'ui-datepicker-it.js' ) )}
{ezcss_require( array( 'datepicker.css' ) )}
<script type="text/javascript">
{literal}
$(function() {
    $( ".from_picker" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-mm-yy",
        numberOfMonths: 1

    });
});
{/literal}
</script>

<form  method='GET' action={concat('openpa/calendar/', $node.node_id)|ezurl}>
	<fieldset>
        
		<legend class="block-title"><span>Cerca eventi</span></legend>        
        
        <div class="border-box block-search">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
        <div class="border-ml"><div class="border-mr"><div class="border-mc">
        <div class="border-content">
        
        
        <label for="search-string">Ricerca libera</label>
        <input id="search-string" type="text" name="Query" value="" />
    
        <label for="from">Dalla data: <small class="no-js-show"> (GG-MM-AAAA)</small>
        <input type="text" class="from_picker" name="SearchDate" title="Dalla data" value="" /></label>        
    
    
        <input id="search-button-button" class="defaultbutton" type="submit" name="SearchBlockButton" value="Cerca" />

        </div>
        </div></div></div>
        <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>					
        </div>
        <input type='hidden' name="UrlAlias" value="{$node.parent.url_alias}" />
        <input type='hidden' name="View" value="program" />
	</fieldset>
</form>
