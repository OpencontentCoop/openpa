<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA Init\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    // Modifica della Homepage
    $rootNode = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
    if ( $rootNode instanceof eZContentObjectTreeNode )
    {
        if ( $rootNode->attribute( 'class_identifier' ) == 'homepage' )
        {
            $contentObject = $rootNode->attribute( 'object' );
            $attributeList = array(
                'name' => eZINI::instance()->variable( 'SiteSettings', 'SiteName' )
            );
            $params = array();
            $params['attributes'] = $attributeList;
            $result = eZContentFunctions::updateAndPublishObject( $contentObject, $params );
            if ( $result )
            {
                $cli->output( 'Modifico homepage' );
            }
        }
    }
    
    // Privilegi ruolo anonimo
    $frontend = false;
    $siteaccessList = eZINI::instance()->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' );
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
        $anonymousRole->appendPolicy( 'user', 'login', array( 'SiteAccess' => array( $frontend['id'] ) ) );
        $anonymousRole->store();
        $cli->output( 'Aggiungo policy user/login anonymous' );
    }
    
    // Schedula indicizzazione
    $cli->output( 'Schedulo indicizzazione' );
    $def = eZContentObject::definition();
    $conds = array( 'status' => eZContentObject::STATUS_PUBLISHED );    
    $count = eZPersistentObject::count( $def, $conds, 'id' );
    $length = 50;
    $limit = array( 'offset' => 0 , 'length' => $length );
    $time = time() - $count;
    do
    {        
        eZContentObject::clearCache();
        $objects = eZPersistentObject::fetchObjectList( $def, $fieldFilters, $conds, null, $limit );
        foreach ( $objects as $object )
        {            
            $time++;
            $rowPending = array(
                'action'        => 'index_object',
                'created'       => $time,
                'param'         => $object->attribute( 'id' )
            );
            
            $pendingItem = new eZPendingActions( $rowPending );
            $pendingItem->store();
        }
        $limit['offset'] += $length;
    
    } while ( count( $objects ) == $length );


    $cli->output( 'Svuoto tutte le cache' );
    eZContentCacheManager::clearAllContentCache( true );
    
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
