<h4 class="attributetype">{"Number entry"|i18n( 'survey' )}</h4>

<div class="form-group">
  <label>{"Text of question"|i18n( 'survey' )}:</label>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text_{$attribute_id}" value="{$question.text|wash('xhtml')}" size="70" />
</div>

<div class="checkbox">
  <input type="hidden" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_hidden_{$attribute_id}" value="1" />
  <label>
	<input type="checkbox" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_mandatory_{$attribute_id}" value="1"{section show=$question.mandatory} checked{/section} /> {"Mandatory answer"|i18n( 'survey' )}
  </label>
</div>

<div class="checkbox">
  <input type="hidden" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num_hidden_{$attribute_id}" value="1" />
  <label>
	<input type="checkbox" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_num_{$attribute_id}" value="1"{section show=$question.num|eq(1)} checked="checked"{/section} /> {"Integer values only"|i18n( 'survey' )}
  </label>
</div>

<div class="form-group">
  <label>{"Minimum value"|i18n( 'survey' )}:</label>
  <input class="form-control" type="text" size="20" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text2_{$attribute_id}" value="{$question.text2|number($question.num)|wash('xhtml')}" />
</div>

<div class="form-group">
  <label>{"Maximum value"|i18n( 'survey' )}:</label>
  <input class="form-control" type="text" size="20" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text3_{$attribute_id}" value="{$question.text3|number($question.num)|wash('xhtml')}" />
</div>

<div class="form-group">
  <label>{"Default answer"|i18n( 'survey' )}:</label><div class="labelbreak"></div>
  <input class="form-control" type="text" size="20" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_default_{$attribute_id}" value="{$question.default_value|number($question.num)|wash('xhtml')}" />
</div>
