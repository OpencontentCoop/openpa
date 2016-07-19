<div class="global-view-full">
  
  <h1>Codice Google Analytics</h1>
  
  {if is_set($message)}
    <div class="message-error">
      <p>{$message}</p>
    </div>
  {/if}
  
  <form method="post" action="{'openpa/seo'|ezurl(no)}" class="form">
    <input type="text" class="halfbox form-control" name="GoogleID" placeholder="Codice Google Analytics" value="{$googleId}" />
    <input type="submit" class="defaultbutton btn btn-success" name="StoreGoogleID" value="Salva" />
  </form>
</div>