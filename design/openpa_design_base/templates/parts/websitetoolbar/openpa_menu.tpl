{if fetch( 'user', 'has_access_to', hash( 'module', 'openpa', 'function', 'editor_tools' ) )}
    <a href={"/openpa/refreshmenu/"|ezurl} title="Aggiorna i menu"><img src={"websitetoolbar/ezwt-icon-menu.gif"|ezimage} alt="Aggiorna i menu" /></a>
{/if}
