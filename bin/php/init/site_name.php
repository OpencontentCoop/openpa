<?php

// Modifica della Homepage

$siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
$rootNode = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
if ( $rootNode instanceof eZContentObjectTreeNode && $rootNode->attribute( 'name' ) != $siteName )
{
    if ( $rootNode->attribute( 'class_identifier' ) == 'homepage' )
    {
        $cli->output( 'Modifico il titolo dell\'homepage con i valori del site.ini [SiteSettings]SiteName' );
        $contentObject = $rootNode->attribute( 'object' );
        $attributeList = array(
            'name' => $siteName
        );
        if ( $rootNode->attribute( 'name' ) != $attributeList['name'] )
        {
            $params = array();
            $params['attributes'] = $attributeList;
            $result = eZContentFunctions::updateAndPublishObject( $contentObject, $params );
        }
    }
    else
    {
        $cli->error( 'La homepage non è di classe "homepage"' );
    }
}