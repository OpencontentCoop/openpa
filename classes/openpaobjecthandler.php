<?php
class OpenPAObjectHandler
{
    protected $contentObject;
    
    public static function instanceFromContentObject( eZContentObject $object = null )
    {
        //@todo caricare la classe estesa specifica per l'oggetto di riferimento
        if ( $object instanceof eZContentObject )
        {
            return new OpenPAObjectHandler( $object );    
        }
        return new OpenPAObjectHandler();
    }
    
    protected function __construct( $object = false )
    {
        $this->contentObject = $object;
    }
    
    public function flush()
    {
        if ( $this->contentObject instanceof eZContentObject )
        {
            $objectID = $this->contentObject->attribute( 'id' );
            
            $eZSolr = eZSearch::getEngine();
            $eZSolr->addObject( $this->contentObject, false );
            $eZSolr->commit();
            
            eZContentCacheManager::clearContentCacheIfNeeded( $objectID );
            
            $this->contentObject->resetDataMap();
            eZContentObject::clearCache( array( $objectID ) );            
        }
    }
}