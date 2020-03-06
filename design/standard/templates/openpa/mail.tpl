<div class="global-view-full u-margin-bottom-l">

    {if $errors|count()}
        <div class="message-error">
            {foreach $errors as $error}
                <p>{$error|wash()}</p>
            {/foreach}
        </div>
    {/if}

    <form method="post" action="{'openpa/mail'|ezurl(no)}" class="form Form Form--spaced">
        <fieldset class="Form-fieldset">
            <legend class="Form-legend">Impostazioni indirizzo di posta elettronica</legend>

            <div class="Form-field block">
                {if $can_override}
                    <label class="Form-label font-weight-bold" for="sender">Indirizzo mittente:</label>
                    <input id="sender" class="Form-input u-color-black form-control box" type="text" name="Sender"
                           placeholder="{$senders['ini']}" value="{$senders['current']|wash()}">
                    <div class="text-muted"><em><small>Per abilitare un nuovo indirizzo mail è necessario contattare il supporto per effettuarne la verifica. <br />
                                L'impostazione di un indirizzo email non verificato comporta il fallimento di tutti gli invii.</small></em></div>
                {else}
                    <p><strong>Indirizzo abilitato come mittente:</strong> {$senders['current']|wash()}</p>
                {/if}
            </div>

            <div class="Form-field Form-field--choose block checkbox">
                {if $can_override}
                    <label class="Form-label Form-label--block font-weight-bold pt-5" for="debugsend">
                        {if $debugs['ini']|eq(true())}
                            <small>L'invio di debug è abilitato nelle configurazioni di sistema e non è possibile disabilitarlo</small><br />
                        {/if}
                        <input class="Form-input" id="debugsend" name="DebugSend" value="" type="checkbox"
                            {if $debugs['current']|eq(true())}checked="checked"{/if}
                            {if $debugs['ini']|eq(true())}disabled="disabled"{/if}>
                        <span class="Form-fieldIcon" role="presentation"></span> Abilita invio di debug (tutte le mail sono inviate all'indirizzo di debug)
                    </label>
                {else}
                    <p>Invio di debug {if $debugs['current']|eq(true())}abilitato{else}disabilitato{/if}</p>
                {/if}
            </div>
            <input type="hidden" name="DebugSendField" value="1" />

            <div class="Form-field block">
                {if $can_override}
                    <label class="Form-label font-weight-bold pt-5" for="debugreceiver">Indirizzo destinatario degli invii di debug:</label>
                    <input id="debugreceiver" class="Form-input u-color-black form-control box" type="text" name="DebugReceiver"
                           placeholder="{$receivers['ini']}" value="{$receivers['current']|wash()}">
                {elseif $debugs['current']|eq(true())}
                    <p><strong>Indirizzo di debug:</strong> {$receivers['current']|wash()}</p>
                {/if}
            </div>

            {if $can_override}
            <p class="text-right pt-5">
                {if $receivers['current']|ne('')}
                    <input type="submit" class="button btn btn-info btn-xl" name="SendTestMail" value="Invia mail di test"/>
                {/if}
                <input type="submit" class="defaultbutton btn btn-success btn-xl" name="StoreMailSettings" value="Salva"/>
            </p>
            {/if}
        </fieldset>
    </form>

</div>
