{ezpagedata_set( 'has_container', true() )}
<div class="changesectionsettings u-padding-all-xl">

    {if $messages|count()|gt(0)}
        <div class="message-warning">
            <ul class="list-unstyled">
                {foreach $messages as $message}
                    <li style="list-style: none">{$message}</li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {if $error}
        <div class="message-error">
            <p>{$error|wash()}</p>
        </div>
    {/if}

    <h2 class="u-text-h2">{$page_title|wash()}</h2>
    <form method="post" action="{'openpa/changesectionsettings'|ezurl(no)}" class="form">
        <table class="table list table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
            <thead>
            <tr>
                <th>Class</th>
                <th>Root</th>
                <th>Attribute</th>
                <th>Section</th>
                <th>Seconds</th>
                <th>Override</th>
                <th>Ignore</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $classes as $class}
                <tr>
                    <td>
                        {if and($current_edit_class|eq($class), $is_add_settings)}
                            <input type="text" name="classIdentifier" value="{$class|wash()}" class="box form-control" />
                            <input type="hidden" name="new" />
                        {else}
                            {if $current_edit_class|eq($class)}
                                <input type="hidden" name="classIdentifier" value="{$class|wash()}"/>
                            {/if}
                            {$class|wash()}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="rootNodeId" value="{if is_set($settings['rootNodeIdList'][$class])}{$settings['rootNodeIdList'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['rootNodeIdList'][$class])}{$settings['rootNodeIdList'][$class]|wash()}{/if}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="dataTimeAttributeIdentifier" value="{if is_set($settings['dataTimeAttributeIdentifierList'][$class])}{$settings['dataTimeAttributeIdentifierList'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['dataTimeAttributeIdentifierList'][$class])}{$settings['dataTimeAttributeIdentifierList'][$class]|wash()}{/if}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="sectionId" value="{if is_set($settings['sectionIdList'][$class])}{$settings['sectionIdList'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['sectionIdList'][$class])}{$settings['sectionIdList'][$class]|wash()}{else}<em>{$default_section}</em>{/if}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="secondsExpire" value="{if is_set($settings['secondsExpire'][$class])}{$settings['secondsExpire'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['secondsExpire'][$class])}{$settings['secondsExpire'][$class]|wash()}{else}<em>{$default_expire}</em>{/if}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="overrideValue" value="{if is_set($settings['overrideValue'][$class])}{$settings['overrideValue'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['overrideValue'][$class])}{$settings['overrideValue'][$class]|wash()}{/if}
                        {/if}
                    </td>
                    <td>
                        {if $current_edit_class|eq($class)}
                            <input type="text" name="ignore" value="{if is_set($settings['ignore'][$class])}{$settings['ignore'][$class]|wash()}{/if}" class="box form-control" />
                        {else}
                            {if is_set($settings['ignore'][$class])}{$settings['ignore'][$class]|wash()}{/if}
                        {/if}
                    </td>
                    <td style="white-space: nowrap">
                        {if $is_edit|not()}
                            <button type="submit" class="button btn" name="EditSetting" value="{$class}"><i class="fa fa-edit"></i> <span class="u-hiddenVisually sr-only">Modifica</span></button>
                            <button type="submit" class="button btn" name="RemoveSetting" value="{$class}" onclick="return confirm( 'Sei sicuro di voler eliminare questa impostazione?' );"><i class="fa fa-trash"></i> <span class="u-hiddenVisually sr-only">Elimina</span></button>
                        {elseif $current_edit_class|eq($class)}
                            <button type="submit" class="button btn" name="StoreSetting" value="{$class}"><i class="fa fa-save"></i> <span class="u-hiddenVisually sr-only">Salva</span></button>
                            <button type="submit" class="button btn" name="Abort"><i class="fa fa-times"></i> <span class="u-hiddenVisually sr-only">Annulla</span></button>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        {if and($is_add_settings|not(), $is_edit|not())}
            <div class="clearfix float-break" style="margin: 20px">
                <div class="pull-right object-right">
                    <input type="submit" class="button defaultbutton btn" name="AddSetting" value="Aggiungi configurazione" />
                </div>
            </div>
        {/if}

        <hr />

        <input type="submit" class="button btn" name="ResetRules" value="Reset da file di configurazione" onclick="return confirm( 'Sei sicuro di voler resettare le impostazioni?' );" />
        {if $has_backup}
            <input type="submit" class="button btn" name="RestoreRules" value="Annulla ultima modifica" onclick="return confirm( 'Sei sicuro di voler annullare l\'ultima modifica?' );" />
        {/if}

    </form>

</div>