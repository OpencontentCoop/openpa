{def $show = true()}
{if openpaini( 'GestioneClassi', 'NascondiTuttiUltimaModifica', '' )|eq( 'enabled' )}
    {set $show = false()}
{/if}
{if openpaini( 'GestioneClassi', 'NascondiUltimaModifica', array() )|contains( $node.class_identifier )}
    {set $show = false()}
{/if}
{if $show}
<div class="last-modified">di {$node.object.published|l10n(date)} {if $node.object.modified|gt(sum($node.object.published,86400))}- Ultima modifica: <strong>{$node.object.modified|l10n(date)}</strong>{/if}</div>
{/if}