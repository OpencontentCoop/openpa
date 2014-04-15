<div class="global-view-full">
    <h1>Gestione dei ruoli organizzativi dei dipendenti</h1>
    {ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js' ) )}
    
    {def $nomi = array( 'Segretario generale', 'Dirigente generale', 'Dirigente di Servizio', 'Responsabile di Servizio' )
         $ruoli = fetch( 'openpa', 'ruoli' )}
    
    {foreach $ruoli as $role sequence array(bglight,bgdark) as $style}
        {if $nomi|contains( $role.name )|not()}
            {set $nomi = $nomi|append($role.name)}
        {/if}
    {/foreach}
     
    
    <script type="text/javascript">
    {literal}
    $(document).ready(function() {
        $( "#SelectRuolo" ).bind( 'change', function() {
            $('#ruolo').val( $('option:selected', $(this)).val() );
        });
        $("table.list").tablesorter();
        $("table.list th").css( 'cursor', 'pointer' );
    });
    {/literal}
    </script>
    
    <div class="block">
        <form action={'openpa/roles'|ezurl()} method="post">
        
        {if $error}
        <div class="message-error"><p>{$error}</p></div>
        {/if}
        
        <fieldset>
        <legend>Aggiungi ruolo</legend>
        <p>
            <label>Struttura
            <select name="Struttura">
                <option value="0"></option>
                <optgroup label="Aree">
                {foreach fetch( 'openpa', 'aree' ) as $item}
                    <option value="{$item.contentobject_id}">{$item.name|wash()}</option>
                {/foreach}
                </optgroup>
                <optgroup label="Servizi">
                {foreach fetch( 'openpa', 'servizi' ) as $item}
                    <option value="{$item.contentobject_id}">{$item.name|wash()}</option>
                {/foreach}
                </optgroup>
                <optgroup label="Uffici">
                {foreach fetch( 'openpa', 'uffici' ) as $item}
                    <option value="{$item.contentobject_id}">{$item.name|wash()}</option>
                {/foreach}
                </optgroup>
                <optgroup label="Strutture">
                {foreach fetch( 'openpa', 'strutture' ) as $item}
                    <option value="{$item.contentobject_id}">{$item.name|wash()}</option>
                {/foreach}
                </optgroup>
            </select>
            </label>
        </p>
        <p>
            <label>Dipendente
            <select name="Dipendente">                    
                <option value="0"></option>
                {foreach fetch( 'openpa', 'dipendenti' ) as $item}
                    <option value="{$item.contentobject_id}">{$item.name|wash()}</option>
                {/foreach}                    
            </select>
            </label>
        </p>
        <p>
            <label for"ruolo">Nome del ruolo</label>    
            <select name="RuoliUsati" id="SelectRuolo">
                <option value="">Seleziona tra i nomi usati</option>
                {foreach $nomi as $name}
                <option value="{$name}">{$name}</option>
                {/foreach}
            </select>
            <input id="ruolo" name="Ruolo" type="text" value="" />
        </p>
        <p><input class="button defaultbutton" type="submit" name="AggiungiRuolo" value="Aggiungi Ruolo" /></p>
        </fieldset>
        </form>
    </div>
    
     
    {if count($ruoli)|gt(0)}
    <h2>Ruoli dei dipendenti</h2>
    <table width="100%" cellspacing="0" cellpadding="0" border="0" class="list">
        <thead>        
            <th>Struttura</th>
            <th>Dipendente</th>
            <th>Ruolo</th>
            <th></th>
            <th></th>
        </thead>
        <tbody>
            {foreach $ruoli as $role sequence array(bglight,bgdark) as $style}
            <tr class="{$style}">
                <td>{attribute_view_gui attribute=$role.data_map.struttura_di_riferimento}</td>
                <td>{attribute_view_gui attribute=$role.data_map.utente}</td>
                <td><a href={$role.url_alias|ezurl()}>{$role.name|wash()}</a></td>            
                <td width="1"><div class="listbutton"><a href={concat("content/edit/",$role.contentobject_id,"/f/ita-IT")|ezurl}><img class="button" src={"edit.gif"|ezimage} width="16" height="16" alt="Edit" /></a></div></td>
                <td width="1">
                    {if $role.object.can_remove}
                        <form method="post" action={"content/action"|ezurl} style="display: inline">                        
                            <input type="hidden" name="RedirectURIAfterRemove" value="openpa/roles" />
                            <input type="hidden" name="HasMainAssignment" value="1" />
                            <input type="hidden" name="ContentObjectID" value="{$role.object.id}" />
                            <input type="hidden" name="roleID" value="{$role.node_id}" />
                            <input type="hidden" name="ContentNodeID" value="{$role.node_id}" />
                            <input type="hidden" name="ContentLanguageCode" value="ita-IT" />
                            <input type="hidden" name="ContentObjectLanguageCode" value="ita-IT" />
                            <input type="image" src={"websitetoolbar/ezwt-icon-remove.png"|ezimage}
                                   name="ActionRemove" title="{'Remove'|i18n('design/ezwebin/parts/website_toolbar')}" />            
                        </form>
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    {else}
    <div class="message-error"><p>Nessun ruolo presente</p></div>
    {/if}
</div>