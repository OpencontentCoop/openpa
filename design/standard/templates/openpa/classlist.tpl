<div class="global-view-full">
{ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}
<script type="text/javascript">
var OpenpaClassBaseUrl = {'/openpa/class'|ezurl()};
{literal}
$(document).ready(function() {
    $("table.list").tablesorter();
    $("table.list th").css( 'cursor', 'pointer' );
    $("table tr.class").each( function(){
        var self = $(this);
        var id = $(this).attr( 'id' );
        var url = OpenpaClassBaseUrl + '/' + id + '?format=json';
        $.get( url, function(data){
            $.each( data, function( index, value){
                if (self.find( 'td.'+index ).length > 0) {
                    if (value) {
                        self.find( 'td.'+index ).html( '<div class="message-error text-center"><strong>KO</strong></div>' );                        
                    }else{
                        self.find( 'td.'+index ).html( '<div class="message-feedback text-center">OK</div>' );
                    }
                    $("table.list").trigger("update"); 
                }
            });
        });
    });
});
{/literal}
</script>

{def $classList = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}

<table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
    <thead>                
        <tr>
            <th style="vertical-align: middle">Classe</th>
            <th style="vertical-align: middle">Attributi mancanti</th>            
            <th style="vertical-align: middle">Attributi aggiuntivi</th>            
            <th style="vertical-align: middle">Proprietà<br />non sincronizzate</th>            
            <th style="vertical-align: middle">Attributi<br />non sincronizzati</th>            
            <th style="vertical-align: middle">Richiede controllo</th>            
        </tr>
    </thead>
    <tbody>
        {foreach $classList as $class sequence array(bglight,bgdark) as $style}
        <tr id="{$class.identifier}" class="class {$style}">            
            <td style="vertical-align: middle">
                <a target="_blank" href={concat('/openpa/class/',$class.identifier)|ezurl()}>
                    {$class.name} ({$class.identifier})
                </a>
            </td>
            <td class="hasMissingAttributes"><em><small>caricamento</small></em></td>                        
            <td class="hasExtraAttributes"><em><small>caricamento</small></em></td>                        
            <td class="hasDiffProperties"><em><small>caricamento</small></em></td>                        
            <td class="hasDiffAttributes"><em><small>caricamento</small></em></td>                      
            <td class="hasError"><em><small>caricamento</small></em></td>                      
        </tr>
        {/foreach}
    </tbody>
</table>
</div>