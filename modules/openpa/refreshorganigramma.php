<?php

/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$id = !empty( $Params['ID'] ) ? $Params['ID'] : null;
$siteAccess = !empty( $Params['SiteAccess'] ) ? $Params['SiteAccess'] : 'current';

$redirectURI = $http->getVariable( 'RedirectURI', $http->sessionVariable( 'LastAccessesURI', '/' ) );

OpenPAOrganigrammaTools::clearCache();
OpenPAOrganigrammaTools::instance();
if ($id) {
    eZContentCacheManager::clearContentCache($id);
}
$module->redirectTo( $redirectURI );
