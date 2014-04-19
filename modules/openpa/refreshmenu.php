<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();

$id = $Params['ID'];
$siteAccess = $Params['SiteAccess'];
$file = $Params['File'];

$redirectURI = $http->getVariable( 'RedirectURI', $http->sessionVariable( 'LastAccessesURI', '/' ) );

OpenPAMenuTool::refreshMenu( $id, $siteAccess, $file );
eZCache::clearByTag( 'template' );

$module->redirectTo( $redirectURI );