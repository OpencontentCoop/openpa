{ezscript_require( 'matrix.js' )}
<h4 class="attributetype">{"Question Matrix"|i18n('survey')}</h4>

<div class="form-group">
    <label>{"Text of question"|i18n('survey')}:</label>
    <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text_{$attribute_id}" value="{$question.text|wash('xhtml')}" size="70" />
</div>

<div class="checkbox">
    <input type="hidden" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_hidden_{$attribute_id}" value="1" />
    <label><input type="checkbox" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_{$attribute_id}" value="1" {if $question.mandatory}checked{/if} />
    {"Mandatory answer"|i18n('survey')}</label>
</div>

<div class="form-group">
    <label>{"Number of columns for the matrix"|i18n('survey')}:</label>
    <input class="form-control" id="matrixcols_{$question.id}_{$attribute_id}" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num_{$attribute_id}" value="{$question.num|wash('xhtml')}" size="3" onchange="generateMatrix('{$prefix_attribute}',{$question.id},{$attribute_id});" />
</div>

<div class="form-group">
    <label>{"Number of rows for the matrix"|i18n('survey')}:</label>
    <input class="form-control" id="matrixrows_{$question.id}_{$attribute_id}" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num2_{$attribute_id}" value="{$question.num2|wash('xhtml')}" size="3" onchange="generateMatrix('{$prefix_attribute}',{$question.id},{$attribute_id});" />
</div>

{def $matrixValues = $question.text2|unserializematrix()}
<div id="matrixtable_{$question.id}_{$attribute_id}" class="table-responsive">
<table cellpadding="0" cellspacing="0" class="table">
{for 0 to $question.num2|wash('xhtml') as $i}
    <tr>
        <td class="full">
        {if gt( $i, 0 )}
            {"Row"|i18n('survey')} {$i}:<br />
            <input for="row" row="{$i|dec}" class="matrix-input" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text2_{$attribute_id}[1][{$i|dec}]" value="{if is_set( $matrixValues.1[$i|dec] )}{$matrixValues.1[$i|dec]}{/if}" style="width: 80px"/>
        {/if}
        </td>

        {for 0 to $question.num|wash('xhtml')|dec as $j}
            {if eq( $i, 0 )}
            <td class="full">{"Column"|i18n('survey')} {$j|inc}:<br /><input for="column" column="{$j}" class="matrix-input" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text2_{$attribute_id}[0][{$j}]" value="{if is_set( $matrixValues.0[$j] )}{$matrixValues.0[$j]}{/if}"  style="width: 80px" /></td>
            {else}
            <td>&nbsp;</td>
            {/if}
        {/for}
    </tr>
{/for}
</table>
</div>