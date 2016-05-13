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
                <h3>La classe è sincronizzata con il prototipo</h3>
            </div>
        {elseif and( count($errors)|eq(0), count($missing_in_remote)|gt(0) )}
            <div class="message-warning">
                <h3>La classe contiene uno o più attributi aggiuntivi che non sono presenti nel prototipo</h3>
                <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                    <input type="submit" name="SyncButton" value="Sincronizza adesso" class="defaultbutton" />

                    <div class="message-error">
                        <h2>Attenzione</h2>
                        <label>
                            <input type="checkbox" name="RemoveExtra" value="1" />
                            Rimuovi attributi aggiuntivi
                        </label>
                        <p><strong>Attenzione:</strong> rimuovendo gli attributi aggiuntivi personalizzati tutti i contenuti attualmente presenti nei campi {foreach $missing_in_remote as $item}"{$item.Identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</p>
                    </div>

                </form>
            </div>
        {else}
            <div class="message-error">
                <h3>La classe non è sincronizzata con il prototipo</h3>
                <form action={concat('openpa/class/', $locale.identifier)ezurl()} method="post">
                    <input type="submit" name="SyncButton" value="Sincronizza adesso" class="defaultbutton" />
                    {if count($errors)|gt(0)}
                        <div class="message-error">
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
                        <div class="message-error">
                            <label>
                                <input type="checkbox" name="RemoveExtra" value="1" />
                                Rimuovi attributi locali personalizzati
                            </label>
                            <p><strong>Attenzione:</strong> rimuovendo gli attributi personalizzati tutti i contenuti attualmente presenti nei campi {foreach $missing_in_remote as $item}"{$item.Identifier}"{delimiter}, {/delimiter}{/foreach} andranno persi</p>
                        </div>
                    {/if}
                </form>
            </div>
        {/if}

        {if count($diff_properties)|gt(0)}
            <h3>Proprietà che differiscono rispetto al prototipo</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Campo</th>
                    <th>Locale</th>
                    <th>Prototipo</th>
                </tr>
                </thead>
                <tbody>
                {foreach $diff_properties as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.field_name}</td>
                        <td>{$item.locale_value}</td>
                        <td>{$item.remote_value}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($missing_in_locale)|gt(0)}
            <h3>Attributi mancanti rispetto al prototipo</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_locale as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($missing_in_remote)|gt(0)}
            <h3>Attributi aggiuntivi rispetto al prototipo</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>DataType</th>
                    {*<th>Numero di oggetti <br/>con attributo valorizzato</th>*}
                </tr>
                </thead>
                <tbody>
                {foreach $missing_in_remote as $item sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}">
                        <td>{$item.Identifier}</td>
                        <td>{$item.DataTypeString}</td>
                        {*<td class="text-center">{$missing_in_remote_details[$item.Identifier].count}</td>*}
                    </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}

        {if count($diff)|gt(0)}
            <h3>Attributi che differiscono dal prototipo</h3>
            <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
                <thead>
                <tr>
                    <th>Campo</th>
                    <th style="text-align: center">Locale</th>
                    <th style="text-align: center">Prototipo</th>
                    {*<th style="text-align: center">Numero di oggetti <br/>con attributo valorizzato</th>*}
                </tr>
                </thead>
                <tbody>
                {foreach $diff as $identifier => $items sequence array(bglight,bgdark) as $style}
                    {if or( is_set( $errors[$identifier] ), is_set( $warnings[$identifier] ) )}
                        {foreach $items as $item}
                            <tr class="{$style}" {if and( is_set($errors[$identifier]), $errors[$identifier]|contains($item.field_name))}style="background:#ff0"{/if}>
                                <td>{$identifier}</td>
                                <td class="text-center"><strong>{$item.field_name}: {$item.locale_value}</strong></td>
                                <td class="text-center">{$item.field_name}: {$item.remote_value}</td>
                                {*<td class="text-center">
                                    {if $item.detail}{$item.detail.count}{/if}
                                </td>*}
                            </tr>
                        {/foreach}
                    {/if}
                {/foreach}
                </tbody>
            </table>
        {/if}

    {/if}

</div>