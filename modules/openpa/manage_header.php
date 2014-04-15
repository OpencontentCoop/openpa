<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

if ( strpos( $GLOBALS['eZCurrentAccess']['name'], '_backend' ) !== false )
{
    echo 'Accedi al modulo da frontend, altrimenti non posso riconoscere le immagini attualmente impostate';
    eZExecution::cleanExit();
}

$bases = eZTemplateDesignResource::allDesignBases();
$triedFiles = array();


$remoteHeader = OpenPaFunctionCollection::$remoteHeader;
$headerObject = eZContentObject::fetchByRemoteID( $remoteHeader );    
if ( !$headerObject instanceof eZContentObject )
{    
    $oldHeader = eZTemplateDesignResource::fileMatch( $bases, 'images', 'header/banner.jpg', $triedFiles );    
    $attributeList = array(
        'name' => 'Immagine testata',
        'image' => isset( $oldHeader['path'] ) ? $oldHeader['path'] : false
    );
    $admin = eZUser::fetchByName( 'admin' );
    $params = array();
    $params['creator_id'] = $admin->attribute( 'contentobject_id' );
    $params['remote_id'] = $remoteHeader;
    $params['class_identifier'] = 'image';
    $params['parent_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' );
    $params['attributes'] = $attributeList;
    $headerObject = eZContentFunctions::createAndPublishObject( $params );
}
$tpl->setVariable( 'header', $headerObject->attribute( 'main_node' ) );

$remoteLogo = OpenPaFunctionCollection::$remoteLogo;
$logoObject = eZContentObject::fetchByRemoteID( $remoteLogo );    
if ( !$logoObject instanceof eZContentObject )
{    
    $oldLogo = eZTemplateDesignResource::fileMatch( $bases, 'images', 'logo/logo.png', $triedFiles );
    $attributeList = array(
        'name' => 'Logo',        
        'image' => isset( $oldLogo['path'] ) ? $oldLogo['path'] : false
    );
    $admin = eZUser::fetchByName( 'admin' );
    $params = array();
    $params['creator_id'] = $admin->attribute( 'contentobject_id' );
    $params['remote_id'] = $remoteLogo;
    $params['class_identifier'] = 'image';
    $params['parent_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' );
    $params['attributes'] = $attributeList;
    $logoObject = eZContentFunctions::createAndPublishObject( $params );
}
$tpl->setVariable( 'logo', $logoObject->attribute( 'main_node' ) );


$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/testata.tpl' );
$Result['path'] = array( array( 'text' => 'Gestione testata' ,
                                'url' => false ) );
