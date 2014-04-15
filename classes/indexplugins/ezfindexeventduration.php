<?php

if ( interface_exists( 'ezfIndexPlugin' ) )
{
    class ezfIndexEventDuration implements ezfIndexPlugin
    {
        public function modify( eZContentObject $contentObject, &$docList )
        {
            $isEvent = $from = $to = false;
            $attributes = $contentObject->fetchAttributesByIdentifier( array( 'from_time', 'to_time' ) );
            foreach( $attributes as $attribute )
            {
                if ( $attribute instanceof eZContentObjectAttribute )
                {
                    if ( $attribute->attribute( 'contentclass_attribute_identifier' ) == 'from_time' && $attribute->hasContent() )
                    {
                        $from = $attribute->toString();
                    }
                    if ( $attribute->attribute( 'contentclass_attribute_identifier' ) == 'to_time' && $attribute->hasContent()  )
                    {
                        $to = $attribute->toString();
                    }
                }
            }
            
            if ( $from && $to )
            {
                $isEvent = true;            
            }
            
            if ( $isEvent )
            {            
                $duration = $to - $from;
                $version = $contentObject->currentVersion();
                if( $version === false )
                {
                    return;
                }
                $availableLanguages = $version->translationList( false, false );
                foreach ( $availableLanguages as $languageCode )
                {
                    $docList[$languageCode]->addField('extra_event_duration_s', $duration );
                }
            }
    
        }
    }
}

?>
