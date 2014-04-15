<?php
class OpenPAObjectTools
{
    public static function syncObjectFormRemoteApiChildNode( OpenPAApiChildNode $data )
    {
        $class = eZContentClass::fetchByIdentifier( $data->classIdentifier );
        if ( !$class instanceof eZContentClass )
        {
            throw new Exception( "La classe {$data->classIdentifier} non esiste in questa istanza" );
        }
        self::syncObjectFormRemoteApiNode( $data->getApiNode() );
           
    }
    
    public static function syncObjectFormRemoteApiNode( OpenPAApiNode $data, $object = null, $localRemoteIdPrefix = null )
    {
        OpenPALog::notice( $data->metadata['objectName'] . ' (' . $data->metadata['objectRemoteId'] . ')', false );
        if ( !$object )
        {
            $object = eZContentObject::fetchByRemoteID( $data->metadata['objectRemoteId'] );
        }
        
        try
        {            
            $handler = OpenPAObjectHandler::instanceFromContentObject( $object );            
            if ( $data->updateContentObject( $object ) )
            {                    
                if ( $localRemoteIdPrefix !== null )
                {
                    if ( $data->updateLocalRemoteId( $object, $localRemoteIdPrefix ) )
                    {
                        OpenPALog::notice( ' ...aggiornato remoteId ', false );
                    }
                }
                $handler->flush();
                OpenPALog::notice( ' ...sincronizzato' );
            }            
        }
        catch( Exception $e )
        {
            OpenPALog::error( ' ...non trovato!' );            
        }     
    }
}