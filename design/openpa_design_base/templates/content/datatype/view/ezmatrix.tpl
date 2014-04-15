{def $notes = hash()}
{let matrix=$attribute.content}
<table class="list" cellspacing="0">
<tr>
{section var=ColumnNames loop=$matrix.columns.sequential}
{def $header = $ColumnNames.item.name|explode('|')}
<th>{$header[0]}{if is_set( $header[1] )}{$header[1]}{/if}</th>
{if and( is_set( $header[1] ), is_set( $header[2] ) )}
    {set $notes = $notes|merge( hash( $header[1], $header[2] ) )}
{/if}
{undef $header}
{/section}
</tr>
{section var=Rows loop=$matrix.rows.sequential sequence=array( bglight, bgdark )}
<tr class="{$Rows.sequence}">
    {section var=Columns loop=$Rows.item.columns}
    <td>{$Columns.item|wash( xhtml )}</td>
    {/section}
</tr>
{/section}
</table>
{/let}
{if count( $notes )|gt(0)}
<p>
{foreach $notes as $key => $note}
    <small>({$key}) {$note}</small>
    {delimiter}<br />{/delimiter}
{/foreach}
</p>
{/if}
{undef $notes}