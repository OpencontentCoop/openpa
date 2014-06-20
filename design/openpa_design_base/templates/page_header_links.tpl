{include uri='design:page_header_languages.tpl'}

<h2 class="hide">Menu di utilit&agrave;</h2>
<ul>
    
{if fetch( 'user', 'has_access_to', hash( 'module', 'content', 'function', 'dashboard' ) )}
    <li id="dashboard"><a href={"/content/dashboard/"|ezurl} title="Pannello strumenti">Pannello strumenti</a></li>
{/if}

{if and($current_user.is_logged_in, $current_user.login|ne('utente'))}
	{if $pagedesign.data_map.my_profile_label.has_content}
	<li id="myprofile"><a href={"/user/edit/"|ezurl} title="Visualizza il profilo utente">Il mio profilo</a></li>
	{/if}
	{if $pagedesign.data_map.logout_label.has_content}
	<li id="logout"><a href={"/user/logout"|ezurl} title="{$pagedesign.data_map.logout_label.data_text|wash}">{$pagedesign.data_map.logout_label.data_text|wash} ( {$current_user.contentobject.name|wash} )</a></li>
	{/if}
{else}
	{*if is_set($pagedesign.data_map.register_user_label)}
		{if and( $pagedesign.data_map.register_user_label.has_content, ezmodule( 'user/register' ) )}
		<li id="registeruser"><a href={"/user/register"|ezurl} title="{$pagedesign.data_map.register_user_label.data_text|wash}">{$pagedesign.data_map.register_user_label.data_text|wash}</a></li>
		{/if}
	{/if*}
	{if is_set($pagedesign.data_map.login_label)}
		{if $pagedesign.data_map.login_label.has_content}
		<li id="login"><a href={concat("/user/login?url=",$module_result.uri)|ezurl} title="{$pagedesign.data_map.login_label.data_text|wash}">{$pagedesign.data_map.login_label.data_text|wash}</a></li>
		{/if}
	{/if}
{/if}

	{*if $pagedesign.can_edit}
		<li id="sitesettings">
		<a href={concat( "/content/edit/", $pagedesign.id, "/a" )|ezurl} title="{$pagedesign.data_map.site_settings_label.data_text|wash}">
			{$pagedesign.data_map.site_settings_label.data_text|wash}
		</a>
		</li>
	{/if*}
	<li id="print" class="no-js-hide">
		<a href="javascript:window.print()" title="Stampa la pagina corrente">Stampa</a>
	</li>

{if is_area_tematica()}
	{def $area_tematica_links = fetch( 'content', 'related_objects', hash('object_id',is_area_tematica().contentobject_id, 'attribute_identifier', 'area_tematica/link'))}
    {if $area_tematica_links|count()}
        {foreach $area_tematica_links as $link}
        <li>
            {if $link.main_node.class_identifier|eq('link')}
                    <a href={$link.main_node.data_map.location.content|ezurl()} title="{$link.name|wash()}">{$link.name|wash()}</a>
            {else}
                <a href={$link.main_node.url_alias|ezurl()}>{$link.name}</a>
            {/if}
        </li>
        {/foreach}
    {/if}
{else}
	{if openpaini( 'LinkSpeciali', 'NodoContattaci' )}
    {def $link_contatti = fetch('content','node',hash('node_id', openpaini('LinkSpeciali', 'NodoContattaci') ))}
	<li id="contatti" class="no-js-hide">
		<a href={$link_contatti.url_alias|ezurl()} title="Trova il modo migliore per contattarci">Contatti</a>
	</li>
    {/if}
	
	{def $homepage = fetch( 'openpa', 'homepage' )}
	{if and( is_set( $homepage.data_map.facebook ), $homepage.data_map.facebook.has_content )}
	<li>	  		
		<a href="{$homepage.data_map.facebook.content|wash( xhtml )}" title="{$homepage.data_map.facebook.data_text|wash( xhtml )}">
		  <img src={'icons/facebook.jpg'|ezimage} alt="Facebook" />
		</a>
	</li>
	{/if}
	{if and( is_set( $homepage.data_map.twitter ), $homepage.data_map.twitter.has_content )}
	<li>	  		
		<a href="{$homepage.data_map.twitter.content|wash( xhtml )}" title="{$homepage.data_map.twitter.data_text|wash( xhtml )}">
		  <img src={'icons/twitter.png'|ezimage} alt="Twitter" />
		</a>
	</li>
	{/if}
	
{/if}
</ul>	
