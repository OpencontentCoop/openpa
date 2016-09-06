<div class="global-view-full">

    {ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}

    <script type="text/javascript">
        {literal}
        $(document).ready(function() {
            $("table.list").tablesorter();
            $("table.list th").css( 'cursor', 'pointer' );
        });
        {/literal}
    </script>
    <style>
      tr.bgdark {ldelim}
          background-color: #f9f9f9;
      {rdelim}
    </style>
    {if is_set($data.error)}
        <div class="message-error">
            <p>{$data.error}</p>
            {if $locale_not_found}
                <form action={concat('openpa/class/', $request_id)ezurl()} method="post">
                    <input type="submit" name="InstallButton" value="Installa classe {$request_id}" class="defaultbutton" />
                </form>
            {/if}
        </div>
    {else}

        <div class="object-right">
            <a class="button" href="{concat('exportas/csv/', $locale.identifier, '/1')|ezurl(no)}">Salva oggetti in CSV</a>
            <a class="button" href="{concat('exportas/xml/', $locale.identifier, '/1')|ezurl(no)}">Salva oggetti in XML</a>
        </div>

        <h1>
            <a target="_blank" href="{concat('class/view/', $locale.id)|ezurl(no)}">{$locale.name|wash()}</a>
            <a target="_blank" href="{concat('classlists/list/', $locale.identifier)|ezurl(no)}">[{$locale.object_count} oggetti]</a>
        </h1>



        {if and( count($diff_properties)|eq(0), count($missing_in_remote)|eq(0), count($missing_in_locale)|eq(0), count($diff)|eq(0) )}
            <div class="message-feedback">
                <h2>La classe è sincronizzata con il modello</h2>
            </div>
        {elseif and( count($errors)|eq(0), count($warnings)|eq(0), count($missing_in_locale)|eq(0))}
            <div class="message-feedback">
                <h2>La classe è compatibile con il modello</h2>
            </div>
        {else}
            <div class="message-error">
                <h2>La classe non è compatibile con il modello</h2>
            </div>
        {/if}

        {if or( count($diff_properties)|ne(0), count($missing_in_remote)|ne(0), count($missing_in_locale)|ne(0), count($diff)|ne(0) )}
        <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
            <input type="submit" name="SyncButton" value="Sincronizza adesso" class="defaultbutton" />
            {if count($errors)|gt(0)}
                <div class="message-error" style="margin-top: 10px">
                    <h2>Attenzione</h2>
                    <p>
                        La classe contiene uno o più elementi che impediscono la sincronizzazione automatica, per forzare la sincronizzazione spunta la casella seguente.
                    </p>
                    <label>
                        <input type="checkbox" name="ForceSync" value="1" />
                        Forza la sincronizzazione
                    </label>
                    <p><strong>Attenzione:</strong> forzando la sincronizzazione tutti i contenuti attualmente presenti nei campi {foreach $errors as $identifier => $value}"{$identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</p>
                </div>
            {/if}
            {if count($missing_in_remote)|gt(0)}
                <div class="message-error" style="margin-top: 10px">
                    <label>
                        <input type="checkbox" name="RemoveExtra" value="1" />
                        Rimuovi attributi locali personalizzati
                    </label>
                    <p><strong>Attenzione:</strong> rimuovendo gli attributi personalizzati tutti i contenuti attualmente presenti nei campi {foreach $missing_in_remote as $item}"{$item.Identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</p>
                </div>
            {/if}
        </form>
        {/if}


        {if count($diff_properties)|gt(0)}
            <h3>Proprietà che differiscono rispetto al modello</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Proprietà</th>
                    <th>Sito</th>
                    <th>Modello</th>
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $diff_properties as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.field_name}</td>
                        <td>{$item.locale_value}</td>
                        <td>{$item.remote_value}</td>
                        <td style="vertical-align: middle">
                            {if array('class_group')|contains($item.field_name)|not()}
                              <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                                <input type="hidden" name="SyncPropertyIdentifier" value="{$item.field_name}" />
                                <button class="defaultbutton btn btn-primary" type="submit" name="SyncPropertyButton"><i class="fa fa-exchange"></i> <span class="sr-only">Sincronizza</span></button>
                              </form>
                            {/if}
                        </td>  
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($missing_in_locale)|gt(0)}
            <h3>Attributi mancanti rispetto al modello</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_locale as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                        <td style="vertical-align: middle">
                          <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                            <input type="hidden" name="AddAttributeIdentifier" value="{$item.Identifier}" />
                            <button class="defaultbutton btn btn-primary" type="submit" name="AddAttributeButton"><i class="fa fa-plus"></i> <span class="sr-only">Aggiungi</span></button>
                          </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($diff)|gt(0)}
            <h3>Attributi che differiscono dal modello</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>Proprietà</th>
                    <th style="text-align: center">Sito</th>
                    <th style="text-align: center">Modello</th>
                    {*<th style="text-align: center">Numero di oggetti <br/>con attributo valorizzato</th>*}
                    <th width="1"></th></th>                    
                </tr>
                </thead>
                <tbody>
                {foreach $diff as $identifier => $items sequence array(bglight,bgdark) as $style}
                    
                    {def $firstRow = false()}
                    {foreach $items as $item}
                        <tr class="{$style}"
                            {if and( is_set($errors[$identifier]), is_set( $errors[$identifier][$item.field_name] ) )}style="background:#ff0"
                            {elseif and( is_set($warnings[$identifier]), is_set( $warnings[$identifier][$item.field_name] ) )}style="background:#f2dede"                                
                            {/if}>
                            {if $firstRow|not()}
                              <td rowspan="{$items|count()}" style="vertical-align: middle">{$identifier}</td>
                              {set $firstRow = true()}
                            {/if}
                            <td style="vertical-align: middle">{$item.field_name}</td>
                            <td style="text-align: center"><strong>{$item.locale_value}</strong></td>
                            <td style="text-align: center">{$item.remote_value}</td>                            
                            {*<td class="text-center">
                                {if $item.detail}{$item.detail.count}{/if}
                            </td>*}
                            <td style="vertical-align: middle">
                              {if array('placement', 'data_type_string')|contains($item.field_name)|not()}
                                <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                                    <input type="hidden" name="SyncAttributeIdentifier" value="{$identifier}/{$item.field_name}" />
                                    <button class="defaultbutton btn btn-primary" type="submit" name="SyncAttributeButton"><i class="fa fa-exchange"></i> <span class="sr-only">Sincronizza</span></button>
                                  </form>
                              {/if}
                            </td>                            
                        </tr>
                    {/foreach}
                    {undef $firstRow}
                    
                {/foreach}
                </tbody>
            </table>
        {/if}
        
        {if count($missing_in_remote)|gt(0)}
            <h3>Attributi aggiuntivi rispetto al modello</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>                    
                    {*<th>Numero di oggetti <br/>con attributo valorizzato</th>*}
                    <th width="1"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_remote as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                        {*<td class="text-center">{$missing_in_remote_details[$item.Identifier].count}</td>*}
                        <td>
                          <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                            <input type="hidden" name="RemoveAttributeIdentifier" value="{$item.Identifier}" />
                            <button class="defaultbutton btn btn-primary" type="submit" name="RemoveAttributeButton"><i class="fa fa-minus"></i> <span class="sr-only">Rimuovi</span></button>
                          </form>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

    {/if}

</div>
