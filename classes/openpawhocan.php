<?php

class OpenPAWhoCan
{
    protected $contentObject;
    protected $user;
    protected $users;
    protected $functionName;
    protected $assignedNodes;
    protected $mainNode;
    protected $parentNodes;
    protected $can;
    protected $onlyForUser;

    public function __construct( eZContentObject $object, $functionName, eZUser $user = null )
    {
        $this->contentObject = $object;
        $this->functionName = $functionName;
        if ( $user instanceof eZUser )
        {
            $this->onlyForUser = $user;
        }
    }

    public function run()
    {
        if ( $this->onlyForUser !== null )
        {
            $this->setUser( $this->onlyForUser );
            if ( $this->checkAccess( $this->functionName ) > 0 )
            {
                $this->can = true;
            }
        }
        else
        {
            $users = $this->getUsers();
            foreach( $users as $user )
            {
                $this->setUser( $user );
                if ( $this->checkAccess( $this->functionName ) > 0 )
                {
                    $this->can[] = $user->attribute( 'login' );
                }
            }
        }
        return $this->can;
    }
    
    protected function assignedNodes()
    {
        if ( $this->assignedNodes == null )
        {
            $this->assignedNodes = $this->contentObject->attribute( 'assigned_nodes' );
        }
        return $this->assignedNodes;
    }
    
    protected function mainNode()
    {
        if ( $this->mainNode == null )
        {
            $this->mainNode = $this->contentObject->attribute( 'main_node' );
        }
        return $this->mainNode;
    }
    
    public function parentNodes()
    {
        if ( $this->parentNodes == null )
        {
            $this->parentNodes = $this->contentObject->attribute( 'parent_nodes' );
        }
        return $this->parentNodes;        
    }
    
    protected function getUserIDs()
    {
        $objects = array();
        $userClasses = eZUser::fetchUserClassList();            
        foreach( $userClasses as $userClass )
        {
            $conditions = array( 'contentclass_id' => $userClass['id'] );
            $objects = array_merge(
                $objects,
                eZPersistentObject::fetchObjectList(
                    eZContentObject::definition(),
                    array( 'id' ),
                    $conditions,
                    null,
                    null,
                    false )
            );
        }         
        $userIds = array();
        foreach( $objects as $object )
        {
            $userIds[] = $object['id'];
        }
        return $userIds;
    }
    
    protected function getUsers()
    {
        if ( $this->users === null )
        {            
            $this->users = eZPersistentObject::fetchObjectList(
                eZUser::definition(),
                null,
                array( 'contentobject_id' => array( $this->getUserIDs() ) ),
                null,
                null,
                true
            );        
        }
        return $this->users;
    }

    protected function setUser( eZUser $user )
    {
        $this->user = $user;
    }

    protected function checkAccess( $functionName, $originalClassID = false, $parentClassID = false, $returnAccessList = false, $language = false )
    {
        if ( !$this->user instanceof eZUser )
        {
            return 0;
        }
        $classID = $originalClassID;
        $userID = $this->user->attribute( 'contentobject_id' );
        $origFunctionName = $functionName;
    
        // Fetch the ID of the language if we get a string with a language code
        // e.g. 'eng-GB'
        $originalLanguage = $language;
        if ( is_string( $language ) && strlen( $language ) > 0 )
        {
            $language = eZContentLanguage::idByLocale( $language );
        }
        else
        {
            $language = false;
        }
    
        // This will be filled in with the available languages of the object
        // if a Language check is performed.
        $languageList = false;
    
        // The 'move' function simply reuses 'edit' for generic access
        // but adds another top-level check below
        // The original function is still available in $origFunctionName
        if ( $functionName == 'move' )
            $functionName = 'edit';
    
        $accessResult = $this->user->hasAccessTo( 'content' , $functionName );
        $accessWord = $accessResult['accessWord'];
    
        if ( $origFunctionName == 'remove' or
             $origFunctionName == 'move' )
        {
            $mainNode = $this->mainNode();
            // We do not allow these actions on objects placed at top-level
            // - remove
            // - move
            if ( $mainNode and $mainNode->attribute( 'parent_node_id' ) <= 1 )
            {
                return 0;
            }
        }
    
        if ( $classID === false )
        {
            $classID = $this->contentObject->attribute( 'contentclass_id' );
        }
        if ( $accessWord == 'yes' )
        {
            return 1;
        }
        else if ( $accessWord == 'no' )
        {
            if ( $functionName == 'edit' )
            {
                // Check if we have 'create' access under the main parent
                if ( $this->contentObject->attribute( 'current_version' ) == 1 && !$this->contentObject->attribute( 'status' ) )
                {
                    $mainNode = eZNodeAssignment::fetchForObject( $this->contentObject->attribute( 'id' ), $this->contentObject->attribute( 'current_version' ) );
                    $parentObj = $mainNode[0]->attribute( 'parent_contentobject' );
                    $result = $parentObj->checkAccess( 'create', $this->contentObject->attribute( 'contentclass_id' ),
                        $parentObj->attribute( 'contentclass_id' ), false, $originalLanguage );
                    return $result;
                }
                else
                {
                    return 0;
                }
            }
    
            if ( $returnAccessList === false )
            {
                return 0;
            }
            else
            {
                return $accessResult['accessList'];
            }
        }
        else
        {
            $policies  =& $accessResult['policies'];
            $access = 'denied';
            foreach ( array_keys( $policies ) as $pkey  )
            {
                $limitationArray =& $policies[ $pkey ];
                if ( $access == 'allowed' )
                {
                    break;
                }
    
                $limitationList = array();
                if ( isset( $limitationArray['Subtree' ] ) )
                {
                    $checkedSubtree = false;
                }
                else
                {
                    $checkedSubtree = true;
                    $accessSubtree = false;
                }
                if ( isset( $limitationArray['Node'] ) )
                {
                    $checkedNode = false;
                }
                else
                {
                    $checkedNode = true;
                    $accessNode = false;
                }
                foreach ( array_keys( $limitationArray ) as $key  )
                {
                    $access = 'denied';
                    switch( $key )
                    {
                        case 'Class':
                        {
                            if ( $functionName == 'create' and
                                 !$originalClassID )
                            {
                                $access = 'allowed';
                            }
                            else if ( $functionName == 'create' and
                                      in_array( $classID, $limitationArray[$key] ) )
                            {
                                $access = 'allowed';
                            }
                            else if ( $functionName != 'create' and
                                      in_array( $this->contentObject->attribute( 'contentclass_id' ), $limitationArray[$key] )  )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'ParentClass':
                        {
    
                            if (  in_array( $this->contentObject->attribute( 'contentclass_id' ), $limitationArray[$key]  ) )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'ParentDepth':
                        {
                            $assignedNodes = $this->assignedNodes();
                            if ( count( $assignedNodes ) > 0 )
                            {
                                foreach ( $assignedNodes as  $assignedNode )
                                {
                                    $depth = $assignedNode->attribute( 'depth' );
                                    if ( in_array( $depth, $limitationArray[$key] ) )
                                    {
                                        $access = 'allowed';
                                        break;
                                    }
                                }
                            }
    
                            if ( $access != 'allowed' )
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'Section':
                        case 'User_Section':
                        {
                            if ( in_array( $this->contentObject->attribute( 'section_id' ), $limitationArray[$key]  ) )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'Language':
                        {
                            $languageMask = 0;
                            // If we don't have a language list yet we need to fetch it
                            // and optionally filter out based on $language.
    
                            if ( $functionName == 'create' )
                            {
                                // If the function is 'create' we do not use the language_mask for matching.
                                if ( $language !== false )
                                {
                                    $languageMask = $language;
                                }
                                else
                                {
                                    // If the create is used and no language specified then
                                    // we need to match against all possible languages (which
                                    // is all bits set, ie. -1).
                                    $languageMask = -1;
                                }
                            }
                            else
                            {
                                if ( $language !== false )
                                {
                                    if ( $languageList === false )
                                    {
                                        $languageMask = (int)$this->contentObject->attribute( 'language_mask' );
                                        // We are restricting language check to just one language
                                        $languageMask &= (int)$language;
                                        // If the resulting mask is 0 it means that the user is trying to
                                        // edit a language which does not exist, ie. translating.
                                        // The mask will then become the language trying to edit.
                                        if ( $languageMask == 0 )
                                        {
                                            $languageMask = $language;
                                        }
                                    }
                                }
                                else
                                {
                                    $languageMask = -1;
                                }
                            }
                            // Fetch limit mask for limitation list
                            $limitMask = eZContentLanguage::maskByLocale( $limitationArray[$key] );
                            if ( ( $languageMask & $limitMask ) != 0 )
                            {
                                $access = 'allowed';
                            }
                            else
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'Owner':
                        case 'ParentOwner':
                        {
                            // if limitation value == 2, anonymous limited to current session.
                            if ( in_array( 2, $limitationArray[$key] ) &&
                                 $this->user->isAnonymous() )
                            {
                                $createdObjectIDList = eZPreferences::value( 'ObjectCreationIDList' );
                                if ( $createdObjectIDList &&
                                     in_array( $this->contentObject->ID, unserialize( $createdObjectIDList ) ) )
                                {
                                    $access = 'allowed';
                                }
                            }
                            else if ( $this->contentObject->attribute( 'owner_id' ) == $userID || $this->contentObject->ID == $userID )
                            {
                                $access = 'allowed';
                            }
                            if ( $access != 'allowed' )
                            {
                                $access = 'denied';
                                $limitationList = array ( 'Limitation' => $key, 'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'Group':
                        case 'ParentGroup':
                        {
                            $access = $this->contentObject->checkGroupLimitationAccess( $limitationArray[$key], $userID );
    
                            if ( $access != 'allowed' )
                            {
                                $access = 'denied';
                                $limitationList = array ( 'Limitation' => $key,
                                                          'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        case 'State':
                        {
                            if ( count( array_intersect( $limitationArray[$key], $this->contentObject->attribute( 'state_id_array' ) ) ) == 0 )
                            {
                                $access = 'denied';
                                $limitationList = array ( 'Limitation' => $key,
                                                          'Required' => $limitationArray[$key] );
                            }
                            else
                            {
                                $access = 'allowed';
                            }
                        } break;
    
                        case 'Node':
                        {
                            $accessNode = false;
                            $mainNodeID = $this->mainNode()->attribute( 'node_id' );
                            foreach ( $limitationArray[$key] as $nodeID )
                            {
                                $node = eZContentObjectTreeNode::fetch( $nodeID, false, false );
                                $limitationNodeID = $node['main_node_id'];
                                if ( $mainNodeID == $limitationNodeID )
                                {
                                    $access = 'allowed';
                                    $accessNode = true;
                                    break;
                                }
                            }
                            if ( $access != 'allowed' && $checkedSubtree && !$accessSubtree )
                            {
                                $access = 'denied';
                                // ??? TODO: if there is a limitation on Subtree, return two limitations?
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                            else
                            {
                                $access = 'allowed';
                            }
                            $checkedNode = true;
                        } break;
    
                        case 'Subtree':
                        {
                            $accessSubtree = false;
                            $assignedNodes = $this->assignedNodes();
                            if ( count( $assignedNodes ) != 0 )
                            {
                                foreach (  $assignedNodes as  $assignedNode )
                                {
                                    $path = $assignedNode->attribute( 'path_string' );
                                    $subtreeArray = $limitationArray[$key];
                                    foreach ( $subtreeArray as $subtreeString )
                                    {
                                        if ( strstr( $path, $subtreeString ) )
                                        {
                                            $access = 'allowed';
                                            $accessSubtree = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $parentNodes = $this->parentNodes();
                                if ( count( $parentNodes ) == 0 )
                                {
                                    if ( $this->contentObject->attribute( 'owner_id' ) == $userID || $this->contentObject->ID == $userID )
                                    {
                                        $access = 'allowed';
                                        $accessSubtree = true;
                                    }
                                }
                                else
                                {
                                    foreach ( $parentNodes as $parentNode )
                                    {
                                        $parentNode = eZContentObjectTreeNode::fetch( $parentNode, false, false );
                                        $path = $parentNode['path_string'];
    
                                        $subtreeArray = $limitationArray[$key];
                                        foreach ( $subtreeArray as $subtreeString )
                                        {
                                            if ( strstr( $path, $subtreeString ) )
                                            {
                                                $access = 'allowed';
                                                $accessSubtree = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if ( $access != 'allowed' && $checkedNode && !$accessNode )
                            {
                                $access = 'denied';
                                // ??? TODO: if there is a limitation on Node, return two limitations?
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                            else
                            {
                                $access = 'allowed';
                            }
                            $checkedSubtree = true;
                        } break;
    
                        case 'User_Subtree':
                        {
                            $assignedNodes = $this->assignedNodes();
                            if ( count( $assignedNodes ) != 0 )
                            {
                                foreach (  $assignedNodes as  $assignedNode )
                                {
                                    $path = $assignedNode->attribute( 'path_string' );
                                    $subtreeArray = $limitationArray[$key];
                                    foreach ( $subtreeArray as $subtreeString )
                                    {
                                        if ( strstr( $path, $subtreeString ) )
                                        {
                                            $access = 'allowed';
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $parentNodes = $this->parentNodes();
                                if ( count( $parentNodes ) == 0 )
                                {
                                    if ( $this->contentObject->attribute( 'owner_id' ) == $userID || $this->contentObject->ID == $userID )
                                    {
                                        $access = 'allowed';
                                    }
                                }
                                else
                                {
                                    foreach ( $parentNodes as $parentNode )
                                    {
                                        $parentNode = eZContentObjectTreeNode::fetch( $parentNode, false, false );
                                        $path = $parentNode['path_string'];
    
                                        $subtreeArray = $limitationArray[$key];
                                        foreach ( $subtreeArray as $subtreeString )
                                        {
                                            if ( strstr( $path, $subtreeString ) )
                                            {
                                                $access = 'allowed';
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if ( $access != 'allowed' )
                            {
                                $access = 'denied';
                                $limitationList = array( 'Limitation' => $key,
                                                         'Required' => $limitationArray[$key] );
                            }
                        } break;
    
                        default:
                            {
                            if ( strncmp( $key, 'StateGroup_', 11 ) === 0 )
                            {
                                if ( count( array_intersect( $limitationArray[$key],
                                            $this->contentObject->attribute( 'state_id_array' ) ) ) == 0 )
                                {
                                    $access = 'denied';
                                    $limitationList = array ( 'Limitation' => $key,
                                                              'Required' => $limitationArray[$key] );
                                }
                                else
                                {
                                    $access = 'allowed';
                                }
                            }
                            }
                    }
                    if ( $access == 'denied' )
                    {
                        break;
                    }
                }
    
                $policyList[] = array( 'PolicyID' => $pkey,
                                       'LimitationList' => $limitationList );
            }
    
            if ( $access == 'denied' )
            {
                if ( $functionName == 'edit' )
                {
                    // Check if we have 'create' access under the main parent
                    if ( $this->contentObject->attribute( 'current_version' ) == 1 && !$this->contentObject->attribute( 'status' ) )
                    {
                        $mainNode = eZNodeAssignment::fetchForObject( $this->contentObject->attribute( 'id' ), $this->contentObject->attribute( 'current_version' ) );
                        $parentObj = $mainNode[0]->attribute( 'parent_contentobject' );
                        $result = $parentObj->checkAccess( 'create', $this->contentObject->attribute( 'contentclass_id' ),
                            $parentObj->attribute( 'contentclass_id' ), false, $originalLanguage );
                        if ( $result )
                        {
                            $access = 'allowed';
                        }
                        return $result;
                    }
                }
            }
    
            if ( $access == 'denied' )
            {
                if ( $returnAccessList === false )
                {
                    return 0;
                }
                else
                {
                    //return array( 'FunctionRequired' => array ( 'Module' => 'content',
                    //                                            'Function' => $origFunctionName,
                    //                                            'ClassID' => $classID,
                    //                                            'MainNodeID' => $this->mainNode()->attribute( 'node_id' ) ),
                    //              'PolicyList' => $policyList );
                    return 0;
                }
            }
            else
            {
                return 1;
            }
        }
    }
}    