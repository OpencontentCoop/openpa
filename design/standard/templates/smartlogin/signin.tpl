<div class="text-center">
    <h3>
        {'Sei già iscritto?'|i18n('openpa/signin')}<br /><strong>{'Accedi subito!'|i18n('openpa/signin')}</strong>
    </h3>
    <form name="loginform" method="post" action={'/user/login/'|ezurl}>

        <label for="Username">{'Indirizzo Email'|i18n('openpa/signin')}</label>
        <input id="Username" class="halfbox form-control" type="text" name="Login">

        <label for="Pwd">{'Password'|i18n('openpa/signin')}</label>
        <input id="Pwd" class="halfbox form-control" type="password" name="Password">

        <p><a href={'/user/forgotpassword'|ezurl}>{'Forgot your password?'|i18n( 'design/ezwebin/user/login' )}</a></p>

        <button name="LoginButton" type="submit" class="button defaultbutton btn btn-primary btn-lg">{'Accedi'|i18n('openpa/signin')}</button>

        {if is_set( $redirect_uri )}
            <input type="hidden" name="RedirectURI" value="{$redirect_uri}" />
        {/if}
    </form>

</div>
