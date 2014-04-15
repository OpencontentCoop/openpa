{if is_set($lingua)|not()}
{def $lingua = ''}
{/if}
{if is_set($sigla)|not()}
{def $sigla = '???'}
{/if}
<acronym xml:lang="{$lingua}" title="{$content}">{$sigla}</acronym>