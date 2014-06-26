{*
	TEMPLATE  per la valutazione delle pagine da parte degli utenti
	node_id	nodo di riferimento
*}

{def $valuations=fetch( 'content', 'class', hash( 'class_id', 'valuation' ) )}

<div id="valutazione-position">

{def $valutazione=$valuations.object_list[0]
	 $node = fetch(content,node,hash(node_id,$node_id))
	 $data_map=$valutazione.data_map}

  <div id="valutazione" class="float-break">
	<form action="/content/action" method="post">
	  <fieldset>
		<legend>{$valutazione.name|wash()}</legend>
		
		{if is_set($data_map.useful)}
		<div class="block">
		  <input type="hidden" value="" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.useful.id}" />
		  <span class="grouplabel">{$data_map.useful.contentclass_attribute_name|wash()}</span>
		  <label for="utilita1"><input id="utilita1" type="radio" value="0" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.useful.id}[]" />per nulla</label>
		  <label for="utilita2"><input id="utilita2" type="radio" value="1" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.useful.id}[]" />poco</label>
		  <label for="utilita3"><input id="utilita3" type="radio" value="2" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.useful.id}[]" />abbastanza</label>
		  <label for="utilita4"><input id="utilita4" type="radio" value="3" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.useful.id}[]" />molto</label>
		</div>
		{/if}
		
		{if is_set($data_map.easy)}
		<div class="block">
		  <span class="grouplabel">{$data_map.easy.contentclass_attribute_name|wash()}</span>
		  <input type="hidden" value="" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.easy.id}" />
		  <label for="semplicita1"><input id="semplicita1" type="radio" value="0" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.easy.id}[]" />per nulla</label>
		  <label for="semplicita2"><input type="radio" id="semplicita2" value="1" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.easy.id}[]" />poco</label>
		  <label for="semplicita3"><input type="radio" id="semplicita3" value="2" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.easy.id}[]" />abbastanza</label>
		  <label for="semplicita4"><input type="radio" id="semplicita4" value="3" name="ContentObjectAttribute_ezselect_selected_array_{$data_map.easy.id}[]" />molto</label>
		</div>
		{/if}
		
		{if is_set($data_map.comment)}
		<div class="block">
		  <label for="helpcomment" class="grouplabel">{$data_map.comment.contentclass_attribute_name|wash()}</label>
		  <input id="helpcomment" class="halfbox left" type="text" value="" name="ContentObjectAttribute_ezstring_data_text_{$data_map.comment.id}"  />
		</div>
		{/if}
		
		{if is_set($data_map.email_aiutaci)}
		<div class="block">
		  <label for="helpemail_aiutaci" class="grouplabel">{$data_map.email_aiutaci.contentclass_attribute_name|wash()}</label>
		  <input id="helpemail_aiutaci" class="halfbox left" type="text" value="" name="ContentObjectAttribute_ezstring_data_text_{$data_map.email_aiutaci.id}"  />
		</div>
		{/if}
		
		<input class="box" type="hidden" value="Nodo: {$node.node_id}; Oggetto:{$node.contentobject_id}; Versione: {$node.contentobject_version}; Titolo: {$node.name|wash()}" name="ContentObjectAttribute_ezstring_data_text_{$data_map.nodo.id}" />
		<input class="box" type="hidden" value="/{$node.url_alias}" name="ContentObjectAttribute_ezstring_data_text_{$data_map.link.id}" />
		<input type="hidden" value="{$valutazione.main_node.node_id}" name="TopLevelNode"/>
		<input type="hidden" value="{$valutazione.main_node.node_id}" name="ContentNodeID"/>
		<input type="hidden" value="{$valutazione.id}" name="ContentObjectID"/>
		<div class="right">
		  
		  {ezcss_require( array( 'nxc.captcha.css' ) )}
		  {ezscript_require( array( 'nxc.captcha.js' ) )}
		  
		  {def $attribute = $data_map.antispam
			   $class_content = $attribute.contentclass_attribute.content
			   $collection_attribute = $collection_attributes[$attribute.id]
			   $regenerate = 1}
		  {if ezhttp( 'ActionCollectInformation', 'post', true() )}
			  {set $regenerate = 0}
		  {/if}
		  
		  {if eq( $collection_attribute.data_int, 0 )}                                        
			<label for="nxc-captcha-collection-input-{$attribute.id}" class="control-label">Antispam</label>
			<p>
			  <img id="nxc-captcha-{$attribute.id}" alt="{'Secure code'|i18n( 'extension/nxc_captcha' )}" title="{'Secure code'|i18n( 'extension/nxc_captcha' )}" src="{concat( 'nxc_captcha/get/', $attribute.contentclass_attribute.id, '/nxc_captcha_collection_attribute_', $attribute.id, '/', $regenerate )|ezurl( 'no' )}" />
			  <a href="{concat( 'nxc_captcha/get/', $attribute.contentclass_attribute.id, '/nxc_captcha_collection_attribute_', $attribute.id, '/1' )|ezurl( 'no' )}" class="nxc-captcha-regenerate" id="nxc-captcha-regenerate-{$attribute.id}">Ricarica</a>
			</p>
			<p>
			  <input class="captcha-input form-control" id="nxc-captcha-collection-input-{$attribute.id}" type="text" name="nxc_captcha_{$attribute.id}" value="{$collection_attribute.data_text}" size="{$class_content.length.value}" maxlength="{$class_content.length.value}" />
			</p>
		  {else}
			{*<p>{'Secure code is allready entered'|i18n( 'extension/nxc_captcha' )}</p>*}
			<input type="hidden" name="nxc_captcha_{$attribute.id}" value="{$collection_attribute.data_text}" />                        
		  {/if}
		  
		  {undef $attribute $class_content $collection_attribute $regenerate}
		  
		  <input class="defaultbutton" type="submit" value="Invia la valutazione" name="ActionCollectInformation"/>
		</div>
	  </fieldset>	
	</form>
  </div>
</div>
