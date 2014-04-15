{ezscript_require( array( 'ezjsc::jquery', 'jquery.tablesorter.min.js', 'jquery.quicksearch.js' ) )}
<script type="text/javascript">
{literal}
$(document).ready(function() {
    $("table.sort").tablesorter();
    $("table.sort th").css( 'cursor', 'pointer' );    
});
{/literal}
</script>

{def $groupclasses=fetch( 'class', 'list', hash( 'sort_by',  array('name', 'asc') ) )}

{def $main_attributes = array('title','abstract', 'image', 'file', 'geo', 'from_time', 'to_time', 'description', 'ufficio', 'servizio', 'argomento', 'link', 'tags')}
{def $wrong_attributes = array('parola_chiave', 'gps', 'name', 'name', 'descrizione', 'data_iniziopubblicazione', 'data_inizio_validita', 'data_archiviazione', 'data_inizio_attivita', 'data_fine_validita', 'data_finepubblicazione', 'url', 'titolo', 'oggetto')}


<h1 class="context-title">
	Report delle classi ({$groupclasses|count})
</h1>
    
<table id="class-list" class="list sort" cellspacing="0" summary="{'List of classes inside %group_name class group (%class_count)'|i18n( 'design/admin/class/classlist',, hash( '%group_name', $group.name, '%class_count', $class_count ) )|wash}">
<thead>
<tr>
    <th>Gruppo</th>
    <th>Nome classe</th>
    <th>ID classe</th>
    <th>Identificatore classe</th>
    <th>Numero di oggetti</th>
    <th class="tight"></th>
</tr>
</thead>
<tbody>
{section var=Classes loop=$groupclasses sequence=array( bglight, bgdark )}
<tr class="{$Classes.sequence}">
    <td>
        {foreach $Classes.item.ingroup_list as $group }
            <a href={concat( $module.functions.classlist.uri, '/', $group.group_id)|ezurl}>{$group.group_name}</a>
            {delimiter}, {/delimiter}
        {/foreach}
    </td>
    <td>
        {$Classes.item.identifier|class_icon( small, $Classes.item.name|wash )}&nbsp;<a href={concat( "/class/view/", $Classes.item.id )|ezurl}>{$Classes.item.name|wash}</a>
    </td>
    <td>
    	{$Classes.item.id}
    </td>
    <td>
        {$Classes.item.identifier|wash}
    </td>
    <td>
        {if ezmodule( 'classlists' )}
            <a href={concat( 'classlists/list/', $Classes.item.identifier )|ezurl()}>{$Classes.item.object_count}</a>
        {else}
            {$Classes.item.object_count}
        {/if}
    </td>
    <td>
        <a href={concat( 'class/edit/', $Classes.item.id, '/(language)/', $Classes.item.top_priority_language_locale )|ezurl} title="{'Edit the <%class_name> class.'|i18n( 'design/admin/class/classlist',, hash( '%class_name', $Classes.item.name ) )|wash}"><img class="button" src={'edit.gif'|ezimage} width="16" height="16" alt="edit" /></a>
    </td>
</tr>
{/section}
</tbody>
</table>



<form name="GroupList" method="post" action={'class/grouplist'|ezurl}>

<div class="context-block">

{* DESIGN: Header START *}<div class="box-header">

<h1 class="context-title">{'Class groups (%group_count)'|i18n( 'design/admin/class/grouplist',, hash( '%group_count', $groups|count ) )|wash}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div>

{* DESIGN: Content START *}<div class="box-content">

<table class="list" cellspacing="0" summary="{'List of class groups'|i18n( 'design/admin/class/grouplist' )}">
<tr>
    <th class="tight"><img src={'toggle-button-16x16.gif'|ezimage} width="16" height="16" alt="{'Invert selection.'|i18n( 'design/admin/class/grouplist' )}" title="{'Invert selection.'|i18n( 'design/admin/class/grouplist' )}" onclick="ezjs_toggleCheckboxes( document.GroupList, 'DeleteIDArray[]' ); return false;"/></th>
    <th>{'Name'|i18n( 'design/admin/class/grouplist' )}</th>
    <th>{'Modifier'|i18n( 'design/admin/class/grouplist' )}</th>
    <th>{'Modified'|i18n( 'design/admin/class/grouplist' )}</th>
    <th class="tight">&nbsp;</th>
</tr>

{section var=Groups loop=$groups sequence=array( bglight, bgdark )}
<tr class="{$Groups.sequence}">

    {* Remove. *}
    <td><input type="checkbox" name="DeleteIDArray[]" value="{$Groups.item.id}" title="{'Select class group for removal.'|i18n( 'design/admin/class/grouplist' )}" /></td>

    {* Name. *}
    <td>{$Groups.item.name|wash|classgroup_icon( small, $Groups.item.name|wash )}&nbsp;<a href={concat( $module.functions.classlist.uri, '/', $Groups.item.id)|ezurl}>{$Groups.item.name|wash}</a></td>

    {* Modifier. *}
    <td></td>

    {* Modified. *}
    <td>{$Groups.item.modified|l10n( shortdatetime )}</td>

    {* Edit. *}
    <td><a href={concat( $module.functions.groupedit.uri, '/', $Groups.item.id )|ezurl}><img class="button" src={'edit.gif'|ezimage} width="16" height="16" alt="{'Edit'|i18n( 'design/admin/class/grouplist' )}" title="{'Edit the <%class_group_name> class group.'|i18n( 'design/admin/class/grouplist',, hash( '%class_group_name', $Groups.item.name ) )|wash}" /></a></td>

</tr>
{/section}
</table>

{* DESIGN: Content END *}</div>
<div class="block">
<div class="controlbar">
{* DESIGN: Control bar START *}
<div class="block">
    <input class="button" type="submit" name="RemoveGroupButton" value="{'Remove selected'|i18n( 'design/admin/class/grouplist' )}" title="{'Remove the selected class groups. This will also remove all classes that only exist within the selected groups.'|i18n( 'design/admin/class/grouplist' )}" />
    <input class="button" type="submit" name="NewGroupButton" value="{'New class group'|i18n( 'design/admin/class/grouplist' )}" title="{'Create a new class group.'|i18n( 'design/admin/class/grouplist' )}" />
</div>
{* DESIGN: Control bar END *}
</div>
</div>

</div>


<div class="context-block">
{* DESIGN: Header START *}<div class="box-header">
<h2 class="context-title">{'Recently modified classes'|i18n( 'design/admin/class/grouplist' )}</h2>

{* DESIGN: Header END *}</div>

{* DESIGN: Content START *}<div class="box-bc"><div class="box-content">

{let latest_classes=fetch( class, latest_list, hash( limit, 10 ) )}

{section show=$latest_classes}

    <table class="list" cellspacing="0" summary="{'List of recently modified classes'|i18n( 'design/admin/class/grouplist' )}">
    <tr>
        <th>{'Name'|i18n( 'design/admin/class/grouplist')}</th>
        <th class="tight">{'ID'|i18n( 'design/admin/class/grouplist' )}</th>
        <th>{'Identifier'|i18n( 'design/admin/class/grouplist' )}</th>
        <th>{'Modifier'|i18n( 'design/admin/class/grouplist' )}</th>
        <th>{'Modified'|i18n( 'design/admin/class/grouplist' )}</th>
        <th>{'Objects'|i18n('design/admin/class/grouplist')}</th>
        <th class="tight">&nbsp;</th>
    </tr>

    {section var=LatestClasses loop=$latest_classes sequence=array( bglight, bgdark )}
        <tr class="{$LatestClasses.sequence}">

            {* Name. *}
            <td>{$LatestClasses.identifier|class_icon( small, $LatestClasses.name|wash )}&nbsp;<a href={concat( '/class/view/', $LatestClasses.item.id )|ezurl}>{$LatestClasses.item.name|wash}</a></td>

            {* ID. *}
            <td class="number" align="right">{$LatestClasses.item.id}</td>

            {* Identifier. *}
            <td>{$LatestClasses.item.identifier|wash}</td>

            {* Modifier. *}
            <td><a href={$LatestClasses.item.modifier.contentobject.main_node.url_alias|ezurl}>{$LatestClasses.item.modifier.contentobject.name|wash}</a></td>

            {* Modified. *}
            <td>{$LatestClasses.item.modified|l10n(shortdatetime)}</td>

            {* Object count. *}
            <td class="number" align="right">{$LatestClasses.item.object_count}</td>

            {* Edit. *}
            <td><a href={concat( 'class/edit/', $LatestClasses.item.id, '/(language)/', $LatestClasses.item.top_priority_language_locale )|ezurl}><img class="button" src={'edit.gif'|ezimage} width="16" height="16" alt="{'Edit'|i18n( 'design/admin/class/grouplist' )}" title="{'Edit the <%class_name> class.'|i18n( 'design/admin/class/grouplist',, hash( '%class_name', $LatestClasses.item.name) )|wash}" /></a></td>

        </tr>
    {/section}
    </table>

{/section}

{/let}

{* DESIGN: Content END *}</div></div>

</div>

</form>