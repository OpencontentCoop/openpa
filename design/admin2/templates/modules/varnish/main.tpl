<div class="ui-varnish-main">
    <h1>Purge Varnish Cache</h1>
    
    {if count( $purged_urls )}
        <h2>Purged URLs</h2>
        <p style="color: red; background-color: #eee; padding: 7px; border: 1px solid black;">
            {foreach $purged_urls as $url}
                {$url|wash()}<br />
            {/foreach}
        </p>
    {/if}

    <div>
        <form method="post" action={'/varnish/main'|ezurl()}>

            <p>
                Specify purge conditions. Examples:
            </p>
            <ul>
                {if ezini('PurgeUrlBuilder', 'BuilderClass', 'mugo_varnish.ini')|eq('OpenpaMugoVarnishBuilder')}
                    <li>obj.http.X-Ban-Url ~ ^/.* && obj.http.X-Ban-Host ~ {ezini('SiteSettings', 'SiteURL')|explode('www.')|implode('')|explode('/')[0]|explode('.')|implode('\.')}</li>
                {elseif ezini('PurgeUrlBuilder', 'BuilderClass', 'mugo_varnish.ini')|eq('OpenpaMugoVarnishBuilderByInstance')}
                    <li>obj.http.X-Ban-Url ~ ^/.* && obj.http.X-Instance ~ {openpa_instance_identifier()}</li>
                {/if}
            </ul>
            <textarea name="urllist" style="width: 100%; height: 200px;"></textarea>

            <div style="float: right">
                <input class="defaultbutton" type="submit" value="Submit" />
            </div>
        </form>
    </div>
</div>