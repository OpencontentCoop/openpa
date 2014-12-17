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
                if ( $docList[$languageCode] instanceof eZSolrDoc )
                {
                    if ( $docList[$languageCode]->Doc instanceof DOMDocument )
                    {
                        $xpath = new DomXpath( $docList[$languageCode]->Doc );
                        if( $xpath->evaluate( '//field[@name="'.ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY.'"]')->length == 0 )
                        {
                            $docList[$languageCode]->addField(ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY, $priority );
                        }
                    }
                    elseif ( is_array( $docList[$languageCode]->Doc ) && !isset( $docList[$languageCode]->Doc[ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY] ))
                    {                        
                        $docList[$languageCode]->addField(ObjectHandlerServiceContentVirtual::SORT_FIELD_PRIORITY, $priority );
                    }
                }                
            }
        }
    }
}

?>
