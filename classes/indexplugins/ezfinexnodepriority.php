<?php

if ( interface_exists( 'ezfIndexPlugin' ) )
{
    class ezfIndexNodePriority implements ezfIndexPlugin
    {
        public function modify( eZContentObject $contentObject, &$docList )
        {
            $nodes = $contentObject->assignedNodes();
            $version = $contentObject->currentVersion();
            if( $version === false )
            {
                return;
            }
            $availableLanguages = $version->translationList( false, false );
            
            $priority = 0;
            foreach( $nodes as $node )
            {
                $priority += $node->attribute( 'priority' );
            }
            
            foreach ( $availableLanguages as $languageCode ) 
            {
                $docList[$languageCode]->addField( 'extra_priority_si', $priority );
            }
        }
    }
}

?>
