<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <h3>{"Object info"|i18n("design/standard/content/edit")}</h3>        
	<p>
        <strong>{"Created"|i18n("design/standard/content/edit")}: </strong>
        {if $object.published}{$object.published|l10n(date)}{else}{"Not yet published"|i18n("design/standard/content/edit")}{/if}
    </p>
    
    <p>
	    <strong>{"Last Modified"|i18n("design/standard/content/edit")}: </strong>
	    {if $object.modified}{$object.modified|l10n(date)}{else}{"Not yet published"|i18n("design/standard/content/edit")}{/if}
    </p>
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>


<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <h3>{"Sezioni"|i18n("design/standard/content/edit")}</h3>        
	<div class="sections">
        {include uri='design:content/parts/edit_sections.tpl'}
	</div>
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>


<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <h3>{"Versions"|i18n("design/standard/content/edit")}</h3>
    <p>
        <strong>{"Editing"|i18n("design/standard/content/edit")}: </strong> {$edit_version}
    </p>
	<p>
        <strong>{"Current"|i18n("design/standard/content/edit")}: </strong> {$object.current_version}
    </p>
    <p>
        <input class="button" type="submit" name="VersionsButton" value="{'Manage'|i18n('design/standard/content/edit')}" />
        <input class="defaultbutton" type="submit" name="PreviewButton" value="{'Preview'|i18n('design/standard/content/edit')}" />
    </p>        
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>


<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <!-- Translation box start-->
    <h3>{"Translations"|i18n("design/standard/content/edit")}</h3>
    <p>
        <input type="radio" name="FromLanguage" value=""{if $from_language|not} checked="checked"{/if}{if $object.status|eq(0)} disabled="disabled"{/if} />
        {'No translation'|i18n( 'design/standard/content/edit' )}
    </p>
    
    {foreach $object.languages as $language}
    <p>
        <input type="radio" name="FromLanguage" value="{$language.locale}"{if $language.locale|eq($from_language)} checked="checked"{/if} />
        {$language.name|wash}
    </p>
    {/foreach}

    <p>
        <input class="button" type="submit" name="FromLanguageButton" value="{'Translate'|i18n( 'design/standard/content/edit' )}" />
    </p>
    <!-- Translation box end-->
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>


<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <h3>{"Related objects"|i18n("design/standard/content/edit")}</h3>
    {section name=Object loop=$related_contentobjects}
        <p>
            <input type="checkbox" name="DeleteRelationIDArray[]" value="{$Object:item.id}" />
            {node_view_gui view=thumb content_node=$Object:item.main_node}
        </p>
    {/section}
    <p>
        <input class="defaultbutton" type="image" name="BrowseObjectButton" value="{'Find'|i18n('design/standard/content/edit')}" src={"find.png"|ezimage} />
    {section show=$related_contentobjects}
        <input class="button" type="image" name="DeleteRelationButton" value="{'Remove'|i18n('design/standard/content/edit')}" src={"trash.png"|ezimage} />
    {/section}
    </p>
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

<div class="border-box box-gray box-singolo" style="margin-bottom: 1em">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">
    <h3>{"Stati"|i18n("design/standard/content/edit")}</h3>        
	<div class="states">
		{include uri='design:content/parts/edit_states.tpl'}
	</div>
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div> 