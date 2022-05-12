{ezpagedata_set( show_path, false() )}
<div class="global-view-full u-margin-bottom-l">

    {if is_set($message)}
        <div class="message-error">
            <p>{$message}</p>
        </div>
    {/if}

    <form method="post" action="{'openpa/seo'|ezurl(no)}" class="form Form Form--spaced">
        <fieldset class="Form-fieldset">
            <legend class="Form-legend">Impostazioni SEO e Cookie</legend>

            <div class="Form-field form-group Form-field--choose block checkbox">
                <label class="Form-label font-weight-bold Form-label--block" for="robots">
                    <input class="Form-input" id="robots" name="Robots" value="" type="checkbox"
                           {if $robots|eq('enabled')}checked="checked"{/if}>
                    <span class="Form-fieldIcon" role="presentation"></span> Permetti l'accesso ai motori di ricerca
                </label>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="Form-field form-group block m-0">
                        <label class="Form-label font-weight-bold" for="webAnalyticsItaliaId">Codice Web Analytics Italia</label>
                        <input id="webAnalyticsItaliaId" class="Form-input u-color-black form-control border box" type="text" name="WebAnalyticsItaliaID"
                               value="{$webAnalyticsItaliaId|wash()}">
                    </div>
                    <div class="Form-field form-group block m-0">
                        <label class="Form-label font-weight-normal Form-label--block" for="webAnalyticsItaliaCookieless">
                            <input class="Form-input" id="webAnalyticsItaliaCookieless" name="WebAnalyticsItaliaCookieless" value="" type="checkbox"
                                   {if $webAnalyticsItaliaCookieless|eq('enabled')}checked="checked"{/if}>
                            <span class="Form-fieldIcon" role="presentation"></span> Disattiva cookie Web Analytics Italia (modalità cookieless)
                        </label>
                    </div>

                    <div class="Form-field form-group block m-0">
                        <label class="Form-label font-weight-bold" for="googleid">Codice Google Analytics</label>
                        <input id="googleid" class="Form-input u-color-black form-control border box" type="text" name="GoogleID"
                               value="{$googleId|wash()}">
                    </div>
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-normal Form-label--block" for="googleCookieless">
                            <input class="Form-input" id="googleCookieless" name="GoogleCookieless" value="" type="checkbox"
                                   {if $googleCookieless|eq('enabled')}checked="checked"{/if}>
                            <span class="Form-fieldIcon" role="presentation"></span> Disattiva cookie Google Analytics (modalità cookieless)
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    {if openpaini('CookiesSettings', 'Consent', 'simple')|eq('advanced')}
                        <div class="alert alert-info">
                            Impostando Web Analytics Italia e Google Analytics in <i>modalità cookieless</i>, verrà disabilitata la sezione Cookie di analisi e misurazione dell'avviso cookie
                        </div>
                    {/if}
                </div>
            </div>

            {if openpaini('CookiesSettings', 'Consent', 'simple')|eq('advanced')}
            <div class="row">
                <div class="col-md-8">
                    <div class="Form-field form-group block m-0">
                        <label class="Form-label font-weight-bold Form-label--block" for="cookieConsentMultimedia">
                            <input class="Form-input" id="cookieConsentMultimedia" name="CookieConsentMultimedia" value="" type="checkbox"
                                   {if $cookieConsentMultimedia|eq('enabled')}checked="checked"{/if}>
                            <span class="Form-fieldIcon" role="presentation"></span> Visualizza cookie di terze parti nel cookie consent
                        </label>
                    </div>
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-normal " for="cookieConsentMultimediaText">Personalizza il testo degli strumenti dei cookie di terze parti</label>
                        <input id="cookieConsentMultimediaText" class="Form-input u-color-black form-control border box" type="text" name="CookieConsentMultimediaText"
                               value="{$cookieConsentMultimediaText|wash()}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info">
                        Disabilitando l'opzione <i>Visualizza cookie di terze parti nel cookie consent</i>, verrà disabilitata la sezione Cookie di terze parti dell'avviso cookie
                    </div>
                    <div class="alert alert-info">
                        Qualora le sezioni siano entrambe disabilitate, l'avviso cookie non sarà visualizzato
                    </div>
                </div>
            </div>
            {/if}


            <div class="row">
                <div class="col-md-8">
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="googleTagManagerId">Codice Google Tag Manager</label>
                        <input id="googleTagManagerId" class="Form-input u-color-black form-control border box" type="text" name="GoogleTagManagerID"
                               value="{$googleTagManagerID|wash()}">
                    </div>

                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="googleSiteVerificationID">Codice Google Site Verification (richiesto per utilizzare la Google Search Console)</label>
                        <input id="googleSiteVerificationID" class="Form-input u-color-black form-control border box" type="text" name="GoogleSiteVerificationID"
                               value="{$googleSiteVerificationID|wash()}">
                    </div>

                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="RobotsText">Contenuto robots.txt {if $isRobotsTextDefault}(default){/if}</label>
                        <textarea id="RobotsText" class="form-control border box" rows="10" type="text" name="RobotsText">{$robotsText|wash()}</textarea>
                    </div>

                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="metaAuthor">Meta Author</label>
                        <input id="metaAuthor" class="Form-input u-color-black form-control border box" type="text" name="MetaAuthor"
                               value="{$metaAuthor|wash()}">
                    </div>
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="metaCopyright">Meta Copyright</label>
                        <input id="metaCopyright" class="Form-input u-color-black form-control border box" type="text" name="MetaCopyright"
                               value="{$metaCopyright|wash()}">
                    </div>
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="metaDescription">Meta Description</label>
                        <input id="metaDescription" class="Form-input u-color-black form-control border box" type="text" name="MetaDescription"
                               value="{$metaDescription|wash()}">
                    </div>
                    <div class="Form-field form-group block">
                        <label class="Form-label font-weight-bold" for="metaKeywords">Meta Keywords</label>
                        <input id="metaKeywords" class="Form-input u-color-black form-control border box" type="text" name="MetaKeywords"
                               value="{$metaKeywords|wash()}">
                    </div>

                    <p class="text-right">
                        <input type="submit" class="defaultbutton btn btn-success btn-lg" name="StoreSeo" value="Salva"/>
                    </p>
                </div>
                <div class="col-md-4"></div>
            </div>

        </fieldset>
    </form>

</div>
