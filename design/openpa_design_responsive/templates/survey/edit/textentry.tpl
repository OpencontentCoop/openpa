<h4 class="attributetype">{"Text entry"|i18n('survey')}</h4>

<div class="form-group">
  <label>{"Text of question"|i18n('survey')}:</label>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text_{$attribute_id}" value="{$question.text|wash('xhtml')}" size="70" />
</div>

<div class="form-group">
  <label>{"Number of columns for an answer textarea"|i18n('survey')}:</label>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num_{$attribute_id}" value="{$question.num|wash('xhtml')}" size="3" />
</div>

<div class="form-group">
  <label>{"Number of rows"|i18n('survey')}:</label>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num2_{$attribute_id}" value="{$question.num2|wash('xhtml')}" size="3" />
</div>

<div class="checkbox">
  <input type="hidden" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_hidden_{$attribute_id}" value="1" />
  <label>
	<input type="checkbox" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_{$attribute_id}" value="1"{section show=$question.mandatory} checked="checked"{/section} /> {"Mandatory answer"|i18n('survey')}
  </label>
</div>

<div class="form-group">
  <label>{"Default settings"|i18n('survey')}:</label><div class="labelbreak"></div>
  <select class="form-control" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text3_{$attribute_id}">
	<option value="" {section show=$question.text3|eq('')} selected="selected"{/section}>{"Default answer"|i18n('survey')}</option>
	{foreach $question.default_answers as $identifier => $default_answer}
	<option value="{$identifier}" {if $question.text3|eq($identifier)} selected="selected"{/if}>{$default_answer}</option>
	{/foreach}
  </select>
</div>

<div class="form-group">
  <label>{"Default answer"|i18n('survey')}:</label><div class="labelbreak"></div>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_default_{$attribute_id}" value="{$question.default_value|wash('xhtml')}" size="70" />
</div>
