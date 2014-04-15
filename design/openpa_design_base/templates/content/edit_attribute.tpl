{ezscript_require(array( 'ezjsc::jquery', 'ui-widgets.js' ) )}
<script type="text/javascript">
{literal}
$(function() {
	$(".ui-tabs .border-content").tabs({ tabTemplate: '<![CDATA[<li><a href="#{href}"><span>#{label}</span></a></li>]]>' });
});
{/literal}
</script>
{default $view_parameters            = array()
         $attribute_categorys        = ezini( 'ClassAttributeSettings', 'CategoryList', 'content.ini' )
         $attribute_default_category = ezini( 'ClassAttributeSettings', 'DefaultCategory', 'content.ini' )
         $index = 0}


<div class="block-lista_tab">	
<div class="ui-tabs">	

    <div class="border-box box-trans-blue box-tabs-header tabs">
        <div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
		<div class="border-ml"><div class="border-mr"><div class="border-mc">
		<div class="border-content">
            <ul class="ui-tabs-nav">							 
                {foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
                {if $attribute_group|ne('hidden')}
                <li class="{if $index|eq(0)}ui-state-active{else}ui-state-default{/if}">											
                    <a href="#{$attribute_group}">
                        <span>{$attribute_categorys[$attribute_group]}</span>
                    </a>
                </li>
                {set $index = $index|inc()}
                {/if}
                {/foreach}
                <li class="ui-state-default" style="float: right">
                    <a href="#infos">
                        <span>Informazioni generali</span>
                    </a>
                </li>
            </ul>
		</div>
		</div></div></div>
	</div>

    
    {set $index = 0}    
    <div class="tabs-panels">			        
        {foreach $content_attributes_grouped_data_map as $attribute_group => $content_attributes_grouped}
        {set $index = $index|inc()}
        
        <div id="{$attribute_group}" {if $attribute_group|eq('hidden')}style="display: none"{/if}>
            <div class="border-box box-violet box-tabs-panel">
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">
            
            {foreach $content_attributes_grouped as $attribute_identifier => $attribute}
                {def $contentclass_attribute = $attribute.contentclass_attribute}                        
                <div class="block ezcca-edit-datatype-{$attribute.data_type_string} ezcca-edit-{$attribute_identifier}">
                {* Show view GUI if we can't edit, otherwise: show edit GUI. *}
                {if and( eq( $attribute.can_translate, 0 ), ne( $object.initial_language_code, $attribute.language_code ) )}
                    <legend>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                        {if $attribute.can_translate|not} <span class="nontranslatable">({'not translatable'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:                        
                    </legend>
                    {if $contentclass_attribute.description} <em class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</em>{/if}
                    {if $is_translating_content}
                        <div class="original">
                        {attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                        <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                        </div>
                    {else}
                        {attribute_view_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                        <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                    {/if}
                {else}
                    {if $is_translating_content}
                        <legend{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                            {if $attribute.is_required} <span class="required">(obbligatorio)</span>{/if}
                            {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:                            
                        </legend>
                        {if $contentclass_attribute.description} <em class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</em>{/if}
                        <div class="original">
                        {attribute_view_gui attribute_base=$attribute_base attribute=$from_content_attributes_grouped_data_map[$attribute_group][$attribute_identifier] view_parameters=$view_parameters}
                        </div>
                        <div class="translation">
                        {if $attribute.display_info.edit.grouped_input}
                            <fieldset>
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                            </fieldset>
                        {else}
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                        {/if}
                        </div>
                    {else}
                        {if $attribute.display_info.edit.grouped_input}
                            <fieldset>
                            <legend{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                                {if $attribute.is_required} <span class="required">(obbligatorio)</span>{/if}
                                {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}                                
                            </legend>
                            {if $contentclass_attribute.description} <em class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</em>{/if}
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                            </fieldset>
                        {else}
                            <legend{if $attribute.has_validation_error} class="message-error"{/if}>{first_set( $contentclass_attribute.nameList[$content_language], $contentclass_attribute.name )|wash}
                                {if $attribute.is_required} <span class="required">(obbligatorio)</span>{/if}
                                {if $attribute.is_information_collector} <span class="collector">({'information collector'|i18n( 'design/admin/content/edit_attribute' )})</span>{/if}:                                
                            </legend>
                            {if $contentclass_attribute.description} <em class="classattribute-description">{first_set( $contentclass_attribute.descriptionList[$content_language], $contentclass_attribute.description)|wash}</em>{/if}
                            {attribute_edit_gui attribute_base=$attribute_base attribute=$attribute view_parameters=$view_parameters}
                            <input type="hidden" name="ContentObjectAttribute_id[]" value="{$attribute.id}" />
                        {/if}
                    {/if}
                {/if}
                                
                </div>
                {undef $contentclass_attribute}
            {/foreach}
            
            </div>
			</div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
        </div>        
        {/foreach}
        
        <div id="infos"  class="ui-tabs-hide">
            <div class="border-box box-violet box-tabs-panel">
            <div class="border-ml"><div class="border-mr"><div class="border-mc">
			<div class="border-content">
            {include uri="design:content/edit_infos.tpl"}
            </div>
			</div></div></div>
            <div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
			</div>
        </div>
        
    </div>		   
</div>
</div>

{/default}
