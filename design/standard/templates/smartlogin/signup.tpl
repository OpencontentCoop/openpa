<div class="text-center">
    <h3>
        <strong>{'Non sei ancora iscritto?'|i18n('openpa/signup')} <br /> </strong> {'Bastano 5 secondi per registrarsi!'|i18n('openpa/signup')}
    </h3>
    <form name="signupform" method="post" action={'/openpa/signup/'|ezurl}>

        <label for="Name">{'Nome e cognome'|i18n('openpa/signup')}</label>
        <input id="Name" name="Name"  class="halfbox form-control" required="" type="text" value="{if is_set($name)}{$name}{/if}" />

        <label for="Emailaddress">{'Indirizzo Email'|i18n('openpa/signup')}</label>
        <input id="Emailaddress" name="EmailAddress" class="halfbox form-control" required="" type="text" value="{if is_set($email)}{$email}{/if}" />

        <label for="Password">{'Password'|i18n('openpa/signup')}</label>
        <input id="Password" name="Password" class="halfbox form-control" required="" type="password">

        <p>
            {"Confermi di aver letto la nostra normativa sulla privacy"|i18n('openpa/signup')}
        </p>

        <button name="RegisterButton" type="submit" class="button defaultbutton btn btn-success btn-lg">{'Iscriviti'|i18n('openpa/signup')}</button>

        {if is_set( $redirect_uri )}
            <input type="hidden" name="RedirectURI" value="{$redirect_uri}" />
        {/if}

    </form>
</div>