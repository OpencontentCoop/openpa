<div class="global-view-full u-margin-bottom-l">

    <h1>Impostazioni Tema</h1>

    {if is_set($message)}
        <div class="message-error">
            <p>{$message}</p>
        </div>
    {/if}

    {if count($theme_list)}
    <form method="post" action="{'openpa/theme'|ezurl(no)}" class="form">

        <div class="Form-field">
            <label class="Form-label" for="theme">Tema grafico</label>
            <select id="theme" class="Form-input u-color-black form-control" name="Theme" placeholder="">
                {foreach $theme_list as $item}
                    <option value="{$item|wash()}"{if $theme|eq($item)} selected="selected"{/if}>{$item|wash()}</option>
                {/foreach}
            </select>
        </div>


        <input type="submit" class="defaultbutton btn btn-success" name="StoreTheme" value="Salva" />
    </form>
    {/if}

</div>
