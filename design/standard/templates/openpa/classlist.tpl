<div class="global-view-full">
    {ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}
    <script type="text/javascript">
        var OpenpaClassBaseUrl = {'/openpa/class'|ezurl()};
        {literal}
        $(document).ready(function() {
            $("table.list").tablesorter();
            $("table.list th").css( 'cursor', 'pointer' );
            var loadData = function($tr){
                var id = $tr.attr( 'id' );
                var url = OpenpaClassBaseUrl + '/' + id + '?format=json';
                $.get( url, function(data){
                    if (data.error) {
                        $tr.find( 'td.result' ).html('');
                    }else{
                        $.each( data, function( index, value){
                            if ($tr.find( 'td.'+index ).length > 0) {
                                var errorClass = $tr.find( 'td.'+index ).data('errorclass');
                                if (value) {
                                    $tr.find( 'td.'+index ).html( '<div class="'+errorClass+' class-alert text-center"><i class="fa fa-warning"></i><strong class="sr-only">SI</strong></div>' );
                                }else{
                                    $tr.find( 'td.'+index ).html( '<div class="message-feedback class-alert text-center"><i class="fa fa-thumbs-up"></i><strong class="sr-only">NO</strong></div>' );
                                }
                                $("table.list").trigger("update");
                            }
                        });
                    }
                });
            };
            $("table tr.class").each( function(){
                loadData($(this));
            });
            $('a.refresh').bind('click',function(e){
                loadData($(this).parents('tr.class'));
                e.preventDefault();
            });
        });
        {/literal}
    </script>
    <style>
        .message-info{ldelim}
            margin: 0.5em 0 1em;
            padding: 0.5em 1em;
            color: #666;
        {rdelim}
        .class-alert{ldelim}
            margin: 0 auto;
            text-align: center;
            width: 50px;
        {rdelim}
    </style>

    {def $classList = fetch( 'class', 'list', hash( 'sort_by', array( 'name', true() ) ) )}

    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list table table-striped">
        <thead>
        <tr>
            <th style="vertical-align: middle;text-align: center">Classe</th>
            <th style="vertical-align: middle;text-align: center">Attributi<br />mancanti</th>
            <th style="vertical-align: middle;text-align: center">Errori<br />gravi</th>
            <th style="vertical-align: middle;text-align: center">Errori</th>
            <th style="vertical-align: middle;text-align: center">Errori<br />trascurabili</th>
            <th></th>
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
                <td class="hasMissingAttributes result" data-errorclass="message-error"><em><small>caricamento</small></em></td>
                <td class="hasError result" data-errorclass="message-error"><em><small>caricamento</small></em></td>
                <td class="hasWarning result" data-errorclass="message-error"><em><small>caricamento</small></em></td>
                <td class="hasNotice result" data-errorclass="message-info"><em><small>caricamento</small></em></td>
                <td class="result" style="vertical-align: middle"><a href="#" class="refresh"><i class="fa fa-refresh"></i><span class="sr-only">Ricarica</span></a></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
