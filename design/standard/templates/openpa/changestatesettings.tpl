{ezpagedata_set( 'has_container', true() )}
<div class="changestatesettings u-padding-all-xl">

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
    <form method="post" action="{'openpa/changestatesettings'|ezurl(no)}" class="form">

        <div class="block u-padding-bottom-l">
            <h3 class="u-text-h3">Regole di cambio stato</h3>
            <table class="table list table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
                <thead>
                <tr>
                    <th>Identificatore</th>
                    <th>Stato di partenza</th>
                    <th>Stato di arrivo</th>
                    <th>Condizioni</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $definitions as $identifier => $definition sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}" data-identifier="{$identifier}">
                        <td>
                            {if $current_edit_definition|eq($identifier)}
                                <input type="{if $is_add_definition}text{else}hidden{/if}" name="Identifier" value="{$identifier|wash()}" class="box form-control" />
                                {if $is_add_definition|not()}{$identifier|wash()}{/if}
                            {else}
                                {$identifier|wash()}
                            {/if}
                        </td>
                        <td>
                            {if $current_edit_definition|eq($identifier)}
                                <input type="text" name="CurrentState" value="{$definition['CurrentState']|wash()}" class="box form-control" />
                            {else}
                                {$definition['CurrentState']|wash()}
                            {/if}
                        </td>
                        <td>
                            {if $current_edit_definition|eq($identifier)}
                                <input type="text" name="DestinationState" value="{$definition['DestinationState']|wash()}" class="box form-control" />
                            {else}
                                {$definition['DestinationState']|wash()}
                            {/if}
                        </td>
                        <td>
                            {if $current_edit_definition|eq($identifier)}
                                <textarea name="Conditions" class="box form-control" rows="10">{$definition['Conditions']|implode('\n')|wash()}</textarea>
                            {else}
                                {foreach $definition['Conditions'] as $condition}
                                    <p>{$condition|wash()}</p>
                                {/foreach}
                            {/if}
                        </td>
                        <td style="white-space: nowrap">
                            {if $is_edit|not()}
                                <button type="submit" class="button btn" name="EditDefinition" value="{$identifier}"><i class="fa fa-edit"></i> <span class="u-hiddenVisually sr-only">Modifica</span></button>
                                <button type="submit" class="button btn" name="RemoveDefinition" value="{$identifier}" onclick="return confirm( 'Sei sicuro di voler eliminare questa impostazione?' );"><i class="fa fa-trash"></i> <span class="u-hiddenVisually sr-only">Elimina</span></button>
                            {elseif $current_edit_definition|eq($identifier)}
                                <button type="submit" class="button btn" name="StoreDefinition" value="{$identifier}"><i class="fa fa-save"></i> <span class="u-hiddenVisually sr-only">Salva</span></button>
                                <button type="submit" class="button btn" name="Abort"><i class="fa fa-times"></i> <span class="u-hiddenVisually sr-only">Annulla</span></button>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {if and($is_add_definition|not(), $is_add_rule|not(), $is_edit|not())}
                <div class="clearfix float-break" style="margin: 20px">
                    <div class="pull-right object-right">
                        <input type="submit" class="button defaultbutton btn" name="AddDefinition" value="Aggiungi regola" />
                    </div>
                </div>
            {/if}
        </div>

        <div class="block u-padding-bottom-l">
            <h3 class="u-text-h3">Applicazione delle regole per classe di contenuto</h3>
            <table class="table list table-striped" width="100%" cellspacing="0" cellpadding="0" border="0">
                <thead>
                <tr>
                    <th>Identificatore di classe</th>
                    <th>Regole</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {foreach $rules as $class_identifier => $rule_list sequence array(bglight,bgdark) as $style}
                    <tr class="{$style}" data-identifier="{$class_identifier}">
                        <td>
                            {if $current_edit_rule|eq($class_identifier)}
                                <input type="{if $is_add_rule}text{else}hidden{/if}" name="ClassIdentifier" value="{$class_identifier|wash()}" class="box form-control" />
                                {if $is_add_rule|not()}{$class_identifier|wash()}{/if}
                            {else}
                                {$class_identifier|wash()}
                            {/if}
                        </td>
                        <td>
                            {if $current_edit_rule|eq($class_identifier)}
                                <textarea name="RuleList" class="box form-control" rows="10">{$rule_list|implode('\n')|wash()}</textarea>
                            {else}
                                <ol>
                                    {foreach $rule_list as $rule_identifier}
                                        <li style="list-style: decimal">{$rule_identifier|wash()}</li>
                                    {/foreach}
                                </ol>
                            {/if}
                        </td>
                        <td style="white-space: nowrap">
                            {if $is_edit|not()}
                                <button type="submit" class="button btn" name="EditRule" value="{$class_identifier}"><i class="fa fa-edit"></i> <span class="u-hiddenVisually sr-only">Modifica</span></button>
                                <button type="submit" class="button btn" name="RemoveRule" value="{$class_identifier}" onclick="return confirm( 'Sei sicuro di voler eliminare questa impostazione?' );"><i class="fa fa-trash"></i> <span class="u-hiddenVisually sr-only">Elimina</span></button>
                            {elseif $current_edit_rule|eq($class_identifier)}
                                <button type="submit" class="button btn" name="StoreRule" value="{$class_identifier}"><i class="fa fa-save"></i> <span class="u-hiddenVisually sr-only">Salva</span></button>
                                <button type="submit" class="button btn" name="Abort"><i class="fa fa-times"></i> <span class="u-hiddenVisually sr-only">Annulla</span></button>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            {if and($is_add_definition|not(), $is_add_rule|not(), $is_edit|not())}
                <div class="clearfix float-break" style="margin: 20px">
                    <div class="pull-right object-right">
                        <input type="submit" class="button defaultbutton btn" name="AddRule" value="Aggiungi applicazione regole" />
                    </div>
                </div>
            {/if}
        </div>

        <hr />

        <input type="submit" class="button btn" name="ResetRules" value="Reset da file di configurazione" onclick="return confirm( 'Sei sicuro di voler resettare le impostazioni?' );" />
        {if $has_backup}
            <input type="submit" class="button btn" name="RestoreRules" value="Annulla ultima modifica" onclick="return confirm( 'Sei sicuro di voler annullare l\'ultima modifica?' );" />
        {/if}

    </form>
</div>