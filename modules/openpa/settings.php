<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();

$action = $Params['Action'];
$value = $Params['Value'];

//@todo usare qualcosa come settingHandler
switch( $action )
{
    case 'hide_in_topmenu':
        $current = OpenPAINI::variable( 'TopMenu', 'NascondiNodi', array() );
        $current[] = $value;
        $current = array_unique( $current );        
        OpenPAINI::set( 'TopMenu', 'NascondiNodi', $current );
        $object = eZContentObject::fetchByNodeID( $value, false );
        eZContentCacheManager::clearContentCache( $object['id'] );
        break;
    
    case 'show_in_topmenu':
        $current = OpenPAINI::variable( 'TopMenu', 'NascondiNodi', array() );
        $current = array_diff( $current, array( $value ) );
        if ( empty( $current ) ) $current = array( 0 );
        OpenPAINI::set( 'TopMenu', 'NascondiNodi', $current );
        $object = eZContentObject::fetchByNodeID( $value, false );
        eZContentCacheManager::clearContentCache( $object['id'] );
        break;
    
    case 'hide_in_sidemenu':
        $current = OpenPAINI::variable( 'SideMenu', 'NascondiNodi', array() );
        $current[] = $value;
        $current = array_unique( $current );        
        OpenPAINI::set( 'SideMenu', 'NascondiNodi', $current );
        $object = eZContentObject::fetchByNodeID( $value, false );
        eZContentCacheManager::clearContentCache( $object['id'] );
        break;
    
    case 'show_in_sidemenu':
        $current = OpenPAINI::variable( 'SideMenu', 'NascondiNodi', array() );
        $current = array_diff( $current, array( $value ) );
        if ( empty( $current ) ) $current = array( 0 );
        OpenPAINI::set( 'SideMenu', 'NascondiNodi', $current );
        $object = eZContentObject::fetchByNodeID( $value, false );
        eZContentCacheManager::clearContentCache( $object['id'] );
        break;
}

$redirectURI = $http->getVariable( 'RedirectURI', $http->sessionVariable( 'LastAccessesURI', '/' ) );

$module->redirectTo( $redirectURI );