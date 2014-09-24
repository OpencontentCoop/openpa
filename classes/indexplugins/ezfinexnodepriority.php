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
            foreach ( $availableLanguages as $languageCode )
            {
                foreach( $nodes as $node )
                {
                    $docList[$languageCode]->addField('extra_priority___s', $node->attribute( 'priority' ) );
                }
            }
        }
    }
}

?>
