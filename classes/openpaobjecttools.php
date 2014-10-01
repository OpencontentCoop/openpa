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
    
    /**
     * @param eZContentObject $object
     * @param bool $allVersions
     * @param int $newParentNodeID
     * @throws Exception
     * @return eZContentObject
     */
    public static function copyObject( eZContentObject $object, $allVersions = true, $newParentNodeID = null )
    {
        if ( !$object instanceof eZContentObject )
            throw new InvalidArgumentException( 'Object not found' );
        
        if ( !$newParentNodeID )
            $newParentNodeID = $object->attribute( 'main_parent_node_id' );
            
    
        // check if we can create node under the specified parent node
        if( ( $newParentNode = eZContentObjectTreeNode::fetch( $newParentNodeID ) ) === null )
        {
            throw new InvalidArgumentException( 'Parent node not found' );
        }
    
        $classID = $object->attribute('contentclass_id');
    
        if ( !$newParentNode->attribute( 'object' )->checkAccess( 'create', $classID ) )
        {
            $objectID = $object->attribute( 'id' );
            throw new Exception( "Cannot copy object $objectID to node $newParentNodeID, the current user does not have create permission for class ID $classID " );            
        }
    
        $db = eZDB::instance();
        $db->begin();
        $newObject = $object->copy( $allVersions );
        // We should reset section that will be updated in updateSectionID().
        // If sectionID is 0 then the object has been newly created
        $newObject->setAttribute( 'section_id', $object->attribute( 'section_id' ) );
        $newObject->store();
    
        $curVersion        = $newObject->attribute( 'current_version' );
        $curVersionObject  = $newObject->attribute( 'current' );
        $newObjAssignments = $curVersionObject->attribute( 'node_assignments' );
        unset( $curVersionObject );
    
        // remove old node assignments
        foreach( $newObjAssignments as $assignment )
        {
            /** @var eZNodeAssignment $assignment */
            $assignment->purge();
        }
    
        // and create a new one
        $nodeAssignment = eZNodeAssignment::create( array(
                                                         'contentobject_id' => $newObject->attribute( 'id' ),
                                                         'contentobject_version' => $curVersion,
                                                         'parent_node' => $newParentNodeID,
                                                         'is_main' => 1
                                                         ) );
        $nodeAssignment->store();
    
        $db->commit();
        return $newObject;
    }
}