<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();

$id = !empty( $Params['ID'] ) ? $Params['ID'] : null;
$siteAccess = !empty( $Params['SiteAccess'] ) ? $Params['SiteAccess'] : 'current';

$redirectURI = $http->getVariable( 'RedirectURI', $http->sessionVariable( 'LastAccessesURI', '/' ) );

OpenPAMenuTool::refreshMenu( $id, $siteAccess );
eZCache::clearByTag( 'template' );
eZCache::clearByTag( 'content' );

$module->redirectTo( $redirectURI );