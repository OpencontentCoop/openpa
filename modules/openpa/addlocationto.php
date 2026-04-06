<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$objectID = (int)$Params['ContentObjectID'];
$selectedNodeID = (int)$Params['SelectedNodeID'];

$object = eZContentObject::fetch( $objectID );
if ( !$object instanceof eZContentObject )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$node = $object->attribute( 'main_node' );
$nodeID = $node->attribute( 'node_id' );

$user = eZUser::currentUser();
if ( !$object->checkAccess( 'edit' ) &&
     !$user->attribute( 'has_manage_locations' ) )
{
    return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
}

if ( $selectedNodeID > 0 )
{
    $selectedNodeIDArray = [$selectedNodeID];
}
else
{
    $selectedNodeIDArray = $http->postVariable( 'SelectedNodeIDArray' );
    if ( !is_array( $selectedNodeIDArray ) ) {
        $selectedNodeIDArray = [];
    }
}

if ( eZOperationHandler::operationIsAvailable( 'content_addlocation' ) )
{
    $operationResult = eZOperationHandler::execute( 'content',
                                                    'addlocation', array( 'node_id'              => $nodeID,
                                                                          'object_id'            => $objectID,
                                                                          'select_node_id_array' => $selectedNodeIDArray ),
                                                    null,
                                                    true );
}
else
{
    eZContentOperationCollection::addAssignment( $nodeID, $objectID, $selectedNodeIDArray );
}

$module->redirectTo( $node->attribute( 'url_alias' ) );