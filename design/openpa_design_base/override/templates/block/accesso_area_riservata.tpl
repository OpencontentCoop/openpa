{if $block.custom_attributes.parent_node_id}

    {def $area = fetch( 'content', 'node', hash( 'node_id', $block.custom_attributes.parent_node_id ) )
         $current_user = fetch( 'user', 'current_user' )}
    
    <div class="block-type-singolo block-{$block.view} square-box-gray">
        <h3 class="block-title">{$block.name|wash()}</h3>
    
        {if and( $current_user.is_logged_in, fetch( 'content', 'access', hash( 'access', 'read', 'contentobject', $area )) )}
            
            <div class="block">
                <p><a class="defaultbutton" href={$area.url_alias|ezurl()} title="{$area.name|wash()}">Accedi all'area riservata</a></p>
            </div>
            
        {else}
        
            <p>{$block.custom_attributes.testo}</p>
            
            <form method="post" action={"/user/login/"|ezurl} name="loginform">
                <label for="id-{$block.id}-login">{"Username"|i18n("design/ezwebin/user/login",'User name')}</label><div class="labelbreak"></div>
                <input class="halfbox" type="text" size="10" name="Login" id="id-{$block.id}-login" value="" tabindex="1" />
            
                <label for="id-{$block.id}-password">{"Password"|i18n("design/ezwebin/user/login")}</label><div class="labelbreak"></div>
                <input class="halfbox" type="password" size="10" name="Password" id="id-{$block.id}-password" value="" tabindex="2" />
                
                <input class="defaultbutton" type="submit" name="LoginButton" value="{'Login'|i18n('design/ezwebin/user/login','Button')}" tabindex="3" />
                
                <input type="hidden" name="RedirectURI" value="{$area.url_alias}" />
            </form>
        
        {/if}
    
    </div>

{else}
    <div class="warning">Area non definita</div>
{/if}