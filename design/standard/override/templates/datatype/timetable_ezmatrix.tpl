{let matrix=$attribute.content}
<table class="list table table-bordered table-condensed" cellspacing="0">
<tr>
{section var=ColumnNames loop=$matrix.columns.sequential}
<th class="text-center">{$ColumnNames.item.name}</th>
{/section}
</tr>
{section var=Rows loop=$matrix.rows.sequential sequence=array( bglight, bgdark )}
<tr class="{$Rows.sequence}">
    {section var=Columns loop=$Rows.item.columns}
    <td class="text-center">{$Columns.item|wash( xhtml )}</td>
    {/section}
</tr>
{/section}
</table>
{/let}