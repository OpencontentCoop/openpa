<h4 class="attributetype">{"Section header"|i18n( 'survey' )}</h4>

<div class="form-group">
  <label>{"Text of header"|i18n( 'survey' )}:</label><div class="labelbreak"></div>
  <input class="form-control" type="text" name="{$prefix_attribute}_ezsurvey_question_{$question.id}_text_{$attribute_id}" value="{$question.text|wash('xhtml')}" size="30" />
</div>
