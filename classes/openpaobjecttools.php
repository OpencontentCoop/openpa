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
        return self::syncObjectFormRemoteApiNode( $data->getApiNode() );
           
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
            if ( !$object instanceof eZContentObject )
            {
                throw new Exception( "Oggetto {$data->metadata['objectName']} non trovato" );
            }
            $handler = OpenPAObjectHandler::instanceFromContentObject( $object );            
            OpenPALog::notice( ' (' . $object->attribute( 'id' ) . ') ', false );
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
            return $object;
        }
        catch( Exception $e )
        {
            OpenPALog::error( ' ...errore: ' .  $e->getMessage() );
            return false;
        }     
    }
    
    /**
     * @param eZContentObject $object
     * @param bool $allVersions
     * @param int $newParentNodeID
     * @throws Exception
     * @return eZContentObject
     */
    public static function copyObject(eZContentObject $object, $allVersions = false, $newParentNodeID = null): eZContentObject
    {
        $db = eZDB::instance();
        $db->setErrorHandling(eZDB::ERROR_HANDLING_EXCEPTIONS);

        if (!$object instanceof eZContentObject) {
            throw new InvalidArgumentException('Object not found');
        }

        if (!$newParentNodeID) {
            $newParentNodeID = $object->attribute('main_parent_node_id');
        }

        // check if we can create node under the specified parent node
        if (($newParentNode = eZContentObjectTreeNode::fetch($newParentNodeID)) === null) {
            throw new InvalidArgumentException('Parent node not found');
        }

        $classID = $object->attribute('contentclass_id');

        if (!$newParentNode->attribute('object')->checkAccess('create', $classID)) {
            $objectID = $object->attribute('id');
            throw new Exception(
                "Cannot copy object $objectID to node $newParentNodeID, the current user does not have create permission for class ID $classID "
            );
        }

        $db = eZDB::instance();
        $db->begin();
        try {
            $newObject = self::doCopyObject($object, eZLocale::currentLocaleCode());
        } catch (eZDBException $e) {
            $db->rollback();
            throw new InvalidArgumentException($e->getMessage());
        }
        // We should reset section that will be updated in updateSectionID().
        // If sectionID is 0 then the object has been newly created
        $newObject->setAttribute('section_id', $object->attribute('section_id'));
        $newObject->store();

        $curVersion = $newObject->attribute('current_version');
        $curVersionObject = $newObject->attribute('current');
        $newObjAssignments = $curVersionObject->attribute('node_assignments');
        unset($curVersionObject);

        // remove old node assignments
        foreach ($newObjAssignments as $assignment) {
            /** @var eZNodeAssignment $assignment */
            $assignment->purge();
        }

        // and create a new one
        $nodeAssignment = eZNodeAssignment::create([
            'contentobject_id' => $newObject->attribute('id'),
            'contentobject_version' => $curVersion,
            'parent_node' => $newParentNodeID,
            'is_main' => 1,
        ]);
        $nodeAssignment->store();

        // fix images onPublish
        $dataMap = $object->attribute('data_map');
        $newDataMap = $newObject->attribute('data_map');
        foreach ($newDataMap as $identifier => $attribute) {
            if ($attribute instanceof eZContentObjectAttribute) {
                if ($attribute->attribute('data_type_string') == eZIdentifierType::DATA_TYPE_STRING) {
                    $attribute->setAttribute('data_int', 0);
                    $attribute->setAttribute('data_text', '');
                    $attribute->store();
                    $attribute->dataType()->assignValue($attribute->contentClassAttribute(), $attribute);
                }
                if ($attribute->attribute('data_type_string') == 'ezimage') {
                    $attribute->setAttribute("data_text", '');
                    if (isset($dataMap[$identifier])) {
                        $string = $dataMap[$identifier]->toString();
                        $delimiterPos = strpos($string, '|');
                        if ($delimiterPos === false) {
                            $filepath = $string;
                            $alternativeText = '';
                        } else {
                            $filepath = substr($string, 0, $delimiterPos);
                            $alternativeText = substr($string, $delimiterPos + 1);
                        }
                        $tempFilename = basename($filepath);
                        $tempDirectory = eZSys::cacheDirectory() . '/' . eZINI::instance('image.ini')->variable(
                                'FileSettings',
                                'TemporaryDir'
                            );

                        $tempFile = eZFile::create(
                            $tempFilename,
                            $tempDirectory,
                            eZClusterFileHandler::instance($filepath)->fetchContents()
                        );

                        $attribute->fromString("$tempDirectory/$tempFilename|$alternativeText");

                        @unlink("$tempDirectory/$tempFilename");
                    }
                    $attribute->store();
                }
            }
        }

        $db->commit();
        return $newObject;
    }

    private static function doCopyObject(eZContentObject $object, string $languageCode = 'ita-IT'): eZContentObject
    {
        $user = eZUser::currentUser();
        $userID = $user->attribute('contentobject_id');

        $contentObject = clone $object;
        $translationList = $contentObject->translationStringList();

        if ($languageCode && !in_array($languageCode, $translationList)) {
            throw new InvalidArgumentException("Language code $languageCode not found in translation list");
        }

        $contentObject->setAttribute('current_version', 1);
        $contentObject->setAttribute('owner_id', $userID);

        $contentObject->setAttribute('remote_id', eZRemoteIdUtility::generate('object'));

        $db = eZDB::instance();
        $db->begin();
        $contentObject->store();

        $originalObjectID = $object->attribute('id');
        $contentObjectID = $contentObject->attribute('id');

        $db->query(
            "INSERT INTO ezcobj_state_link (contentobject_state_id, contentobject_id)
                     SELECT contentobject_state_id, $contentObjectID FROM ezcobj_state_link WHERE contentobject_id = $originalObjectID"
        );

        $contentObject->setName($object->attribute('name'));

        $versionList = [];
        $versionList[1] = $object->currentVersion();

        foreach ($versionList as $versionNumber => $currentContentObjectVersion) {
            $currentVersionNumber = $currentContentObjectVersion->attribute('version');
            $contentObject->setName($currentContentObjectVersion->name(), $versionNumber);
            $contentObject->setName(
                $currentContentObjectVersion->name(false, $languageCode),
                $versionNumber,
                $languageCode
            );

            $contentObjectVersion = $object->copyVersion(
                $contentObject,
                $currentContentObjectVersion,
                $versionNumber,
                $contentObject->attribute('id'),
                false,
                $languageCode,
                $languageCode
            );

            if ($currentVersionNumber == $object->attribute('current_version')) {
                $parentMap = [];
                $copiedNodeAssignmentList = $contentObjectVersion->attribute('node_assignments');
                foreach ($copiedNodeAssignmentList as $copiedNodeAssignment) {
                    $parentMap[$copiedNodeAssignment->attribute('parent_node')] = $copiedNodeAssignment;
                }
                // Create node-assignment from all current published nodes
                $nodes = $object->assignedNodes();
                foreach ($nodes as $node) {
                    $remoteID = eZRemoteIdUtility::generate('object');
                    // Remove assignments which conflicts with existing nodes, but keep remote_id
                    if (isset($parentMap[$node->attribute('parent_node_id')])) {
                        $copiedNodeAssignment = $parentMap[$node->attribute('parent_node_id')];
                        unset($parentMap[$node->attribute('parent_node_id')]);
                        $remoteID = $copiedNodeAssignment->attribute('remote_id');
                        $copiedNodeAssignment->purge();
                    }
                    $contentObjectVersion->assignToNode(
                        $node->attribute('parent_node_id'),
                        $node->attribute('is_main'),
                        0,
                        $node->attribute('sort_field'),
                        $node->attribute('sort_order'),
                        $remoteID
                    );
                }
            }
        }

        $contentObject->setAttribute( 'status', eZContentObject::STATUS_DRAFT );
        $contentObject->store();

        $db->commit();
        return $contentObject;
    }
}