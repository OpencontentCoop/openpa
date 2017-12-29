<div class="global-view-full">
  
  <h1>Impostazioni recaptcha</h1>
  
  {if is_set($message)}
    <div class="message-error">
      <p>{$message}</p>
    </div>
  {/if}
  
  <form method="post" action="{'openpa/recaptcha'|ezurl(no)}" class="form">

    <div class="Form-field">
      <label class="Form-label" for="recaptcha">Chiave pubblica</label>
      <input id="recaptcha" class="Form-input u-color-black form-control" type="text" name="GoogleRecaptchaPublic" placeholder="Chiave pubblica" value="{$public}">
    </div>

    <div class="Form-field">
      <label class="Form-label" for="recaptcha2">Chiave privata</label>
      <input id="recaptcha2" class="Form-input u-color-black form-control" type="text" name="GoogleRecaptchaPrivate" placeholder="Chiave privata" value="{$private}">
    </div>

    <input type="submit" class="defaultbutton btn btn-success" name="StoreSeo" value="Salva" />
  </form>

</div>
