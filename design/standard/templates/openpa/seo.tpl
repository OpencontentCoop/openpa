<div class="global-view-full u-margin-bottom-l">

    {if is_set($message)}
        <div class="message-error">
            <p>{$message}</p>
        </div>
    {/if}

    <form method="post" action="{'openpa/seo'|ezurl(no)}" class="form Form Form--spaced">
        <fieldset class="Form-fieldset">
            <legend class="Form-legend">Impostazioni SEO</legend>

            <div class="Form-field Form-field--choose block checkbox">
                <label class="Form-label Form-label--block" for="robots">
                    <input class="Form-input" id="robots" name="Robots" value="" type="checkbox"
                           {if $robots|eq('enabled')}checked="checked"{/if}>
                    <span class="Form-fieldIcon" role="presentation"></span> Permetti l'accesso ai motori di ricerca
                </label>
            </div>

            <div class="Form-field block pb-5">
                <label class="Form-label" for="webAnalyticsItaliaId">Codice Web Analytics Italia</label>
                <input id="webAnalyticsItaliaId" class="Form-input u-color-black form-control box" type="text" name="WebAnalyticsItaliaID"
                       value="{$webAnalyticsItaliaId|wash()}">
            </div>

            <div class="Form-field block pb-5">
                <label class="Form-label" for="googleid">Codice Google Analytics</label>
                <input id="googleid" class="Form-input u-color-black form-control" type="text" name="GoogleID"
                       placeholder="Codice Google Analytics" value="{$googleId|wash()}">
            </div>

            <div class="Form-field block">
                <label class="Form-label" for="googleTagManagerId">Codice Google Tag Manager</label>
                <input id="googleTagManagerId" class="Form-input u-color-black form-control" type="text" name="GoogleTagManagerID"
                       placeholder="Codice Tag Manager" value="{$googleTagManagerID|wash()}">
            </div>

            <div class="Form-field block">
                <label class="Form-label" for="googleSiteVerificationID">Codice Google Site Verification (richiesto per utilizzare la Google Search Console)</label>
                <input id="googleSiteVerificationID" class="Form-input u-color-black form-control" type="text" name="GoogleSiteVerificationID"
                       placeholder="Codice Google Site Verification" value="{$googleSiteVerificationID|wash()}">
            </div>

            <div class="Form-field block">
                <label class="Form-label" for="RobotsText">Contenuto robots.txt {if $isRobotsTextDefault}(default){/if}</label>
                <textarea id="RobotsText" class="form-control" rows="20" type="text" name="RobotsText">{$robotsText|wash()}</textarea>
            </div>

            <div class="Form-field block pb-5">
                <label class="Form-label" for="metaAuthor">Meta Author</label>
                <input id="metaAuthor" class="Form-input u-color-black form-control box" type="text" name="MetaAuthor"
                       value="{$metaAuthor|wash()}">
            </div>
            <div class="Form-field block pb-5">
                <label class="Form-label" for="metaCopyright">Meta Copyright</label>
                <input id="metaCopyright" class="Form-input u-color-black form-control box" type="text" name="MetaCopyright"
                       value="{$metaCopyright|wash()}">
            </div>
            <div class="Form-field block pb-5">
                <label class="Form-label" for="metaDescription">Meta Description</label>
                <input id="metaDescription" class="Form-input u-color-black form-control box" type="text" name="MetaDescription"
                       value="{$metaDescription|wash()}">
            </div>
            <div class="Form-field block pb-5">
                <label class="Form-label" for="metaKeywords">Meta Keywords</label>
                <input id="metaKeywords" class="Form-input u-color-black form-control box" type="text" name="MetaKeywords"
                       value="{$metaKeywords|wash()}">
            </div>

            <p class="text-right">
                <input type="submit" class="defaultbutton btn btn-success btn-xl" name="StoreSeo" value="Salva"/>
            </p>
        </fieldset>
    </form>

</div>
