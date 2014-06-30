<?php

// Privilegi ruolo anonimo

$cli->output( 'Aggiungo policy user/login al ruolo Anonymous' );

$frontend = false;
$siteaccessList = (array) eZINI::instance()->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' );
foreach( eZSiteAccess::siteAccessList() as $sa )
{
    if ( in_array( $sa['name'], $siteaccessList ) && strpos( $sa['name'], 'frontend' ) !== false )
    {
        $frontend = $sa;
    }
}
$anonymousRole = eZRole::fetchByName( 'Anonymous' );
if ( $anonymousRole instanceof eZRole && $frontend )
{
    $do = true;
    foreach( $anonymousRole->attribute( 'policies' ) as $policy )
    {
        if ( $policy->attribute( 'module_name' ) == 'user' &&
             $policy->attribute( 'function_name' ) == 'login' )
        {
            foreach( $policy->attribute('limitations') as $limitation )
            {
                if ( $limitation->attribute('values_as_string') == $frontend['id'] )
                {
                    $do = false;
                    break;
                }
            }
        }
    }
    if ( $do )
    {
        $anonymousRole->appendPolicy( 'user', 'login', array( 'SiteAccess' => array( $frontend['id'] ) ) );
        $anonymousRole->store();
    }
}
else
{
    $cli->error( 'Non trovo il ruolo "Anonymous"' );
}
