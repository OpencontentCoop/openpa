<?php

// Modifica della Homepage

$cli->output( 'Modifico il titolo dell\'homepage con i valori del site.ini [SiteSettings]SiteName' );

$rootNode = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
if ( $rootNode instanceof eZContentObjectTreeNode )
{
    if ( $rootNode->attribute( 'class_identifier' ) == 'homepage' )
    {
        $contentObject = $rootNode->attribute( 'object' );
        $attributeList = array(
            'name' => eZINI::instance()->variable( 'SiteSettings', 'SiteName' )
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