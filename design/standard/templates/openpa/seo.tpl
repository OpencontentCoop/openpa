<div class="global-view-full">
  
  <h1>Impostazioni SEO</h1>
  
  {if is_set($message)}
    <div class="message-error">
      <p>{$message}</p>
    </div>
  {/if}
  
  <form method="post" action="{'openpa/seo'|ezurl(no)}" class="form">

    <div class="Form-field">
      <label class="Form-label" for="googleid">Codice Google Analytics</label>
      <input id="googleid" class="Form-input u-color-black form-control" type="text" name="GoogleID" placeholder="Codice Google Analytics" value="{$googleId}">
    </div>


    <div class="Form-field checkbox">
    <label class="Form-label Form-label--block" for="robots">
      <input class="Form-input" id="robots" name="Robots" value="" type="checkbox" {if $robots|eq('enabled')}checked="checked"{/if}>
      <span class="Form-fieldIcon" role="presentation"></span> Permetti l'accesso ai motori di ricerca
    </label>
    </div>

    <input type="submit" class="defaultbutton btn btn-success" name="StoreSeo" value="Salva" />
  </form>

</div>
