<?php

class OpenPASubsiteTools
{    
    public static function isInSubsite( $item )
    {
        if ( $item instanceof eZContentObject )
        {
            $nodes = $item->attribute( 'assigned_nodes' );
            foreach( $nodes as $node )
            {
                if ( self::isNodeInCurrentSiteaccess( $node ) )
                {
                    return $item;
                }
            }
        }
        elseif( $item instanceof eZContentObjectTreeNode )
        {
            if ( self::isNodeInCurrentSiteaccess( $item ) )
            {
                return $item;
            }
        }
        return false;
    }
    
    public static function isNodeInCurrentSiteaccess( $node )
    {
        if ( !$node instanceof eZContentObjectTreeNode )
        {
            return true;
        }
        $currentSiteaccess = eZSiteAccess::current();
        $pathPrefixExclude = eZINI::instance()->variable( 'SiteAccessSettings', 'PathPrefixExclude' );
        $aliasArray = explode( '/', $node->attribute( 'url_alias' ) );
        
        foreach( $pathPrefixExclude as $ppe )
        {
            if ( strtolower( $aliasArray[0] ) == $ppe )
            {
                return true;
            }
        }
        
        $pathArray = $node->attribute( 'path_array' );
        $contentIni = eZINI::instance( 'content.ini' );
        $rootNodeArray = array(
            'RootNode',
            'UserRootNode',
            'MediaRootNode'                
        );
        
        foreach ( $rootNodeArray as $rootNodeID )
        {
            $rootNode = $contentIni->variable( 'NodeSettings', $rootNodeID );
            if ( in_array( $rootNode, $pathArray ) ) {
                return true;
            }
        }
        eZDebug::writeError( 'Il nodo ' . $node->attribute( 'name' ) . ' non si trova nel siteaccess ' . $currentSiteaccess['name'] , __METHOD__ );
        return false;
    }
    
    public static function currentSiteaccessUri()
    {
        return '/';
    }
    
    public static function redirectUri()
    {
        return '/error/view/kernel/1';
    }
    
    public static function responseCode()
    {
        return '404';
    }
    
}
