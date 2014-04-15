<?php

$params = array( 'Limitation'  => array(),
                 'LoadDataMap' => false,
                 'ClassFilterType' => 'include',
                 'ClassFilterArray' => array( 'frontpage' )
                 );
$root = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
$frontpages = eZContentObjectTreeNode::subTreeByNodeID( $params, $root );
if ( !$isQuiet )
{    
    $cli->output( "Cleaning frontpage content cache for " . count( $frontpages ) . " objects" );
}

foreach( $frontpages as $frontpage )
{
    eZContentCacheManager::clearContentCache( $frontpage->attribute( 'contentobject_id' ) );
}

?>
