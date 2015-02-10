<div class="global-view-full">
    <form name="signupform" method="post" action={'openpa/signup/'|ezurl}>
        {if $invalid_form}
            <fieldset>
                <div class="message message-warning alert alert-warning">
                    {foreach $errors as $error}<p>{$error}</p>{/foreach}
                </div>

                <div class="block">

                    <label for="Name">{'Nome e cognome'|i18n('openpa')}</label>
                    <input id="Name" name="Name" class="halfbox form-control" required="" type="text" value="{if $name}{$name}{/if}" />

                    <label for="EmailAddress">{'Indirizzo Email'|i18n('openpa')}</label>
                    <input id="Emailaddress" name="EmailAddress" class="halfbox form-control" required="" type="text" value="{if $email}{$email}{/if}" />

                    <label for="Password">{'Password'|i18n('openpa')}</label>
                    <input id="Password" name="Password" class="halfbox form-control" required="" type="password">
                </div>

                <button name="RegisterButton" type="submit" class="button defaultbutton btn btn-success btn-lg">{'Iscriviti'|i18n('openpa')}</button>
                <a class="button" href="{$redirect|ezurl(no)}">Annulla</a>

            </fieldset>

        {elseif $show_captcha}
            {def $bypass_captcha = false()}
            {if $bypass_captcha|not}
                <fieldset>
                    <legend>{'Codice di sicurezza'|i18n( 'openpa/signup' )}</legend>

                    {if ezini( 'RecaptchaSetting', 'PublicKey', 'ezcomments.ini' )|eq('')}
                        <div class="message-warning">
                            {'reCAPTCHA API key non trovata'|i18n( 'openpa/signup' )}
                        </div>

                    {else}
                        <script type="text/javascript">
                            {def $theme = ezini( 'RecaptchaSetting', 'Theme', 'ezcomments.ini' )}
                            {def $language = ezini( 'RecaptchaSetting', 'Language', 'ezcomments.ini' )}
                            {def $tabIndex = ezini( 'RecaptchaSetting', 'TabIndex', 'ezcomments.ini' )}
                            var RecaptchaOptions = {literal}{{/literal} theme : '{$theme}',
                                lang : '{$language}',
                                tabindex : {$tabIndex} {literal}}{/literal};
                        </script>
                        {if $theme|eq('custom')}
                            <p>
                                {'Inserisci il codice di sicurezza'|i18n( 'openpa/signup' )}
                                <a href="javascript:;" onclick="Recaptcha.reload();">{'Clicca qui per ottenere un nuovo codice'|i18n( 'openpa/signup' )}</a>
                            </p>
                            <div id="recaptcha_image" style="margin: 0 auto"></div>
                            <div style="width: 300px;margin: 0 auto">
                                <p><input style="width: 300px;font-size: 2em" type="text" class="box" id="recaptcha_response_field" name="recaptcha_response_field" /></p>
                                <button name="CaptchaButton" type="submit" class="button defaultbutton btn btn-success btn-lg btn-block">{'Prosegui'|i18n('openpa')}</button>
                            </div>
                            {*Customized theme end*}
                        {/if}
                        {fetch( 'openpa', 'recaptcha_html' )}

                    {/if}
                </fieldset>
            {/if}
            {undef $bypass_captcha}

        {/if}
    </form>
</div>
