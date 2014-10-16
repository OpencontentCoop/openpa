<?php
/** @var eZModule $module */
$module = $Params['Module'];
$objectID = $Params['ObjectID'];

$node = false;
$redirect = '/';

if ( $objectID )
{
    $node = eZContentObjectTreeNode::fetchByContentObjectID( $objectID );
    if ( $node[0] instanceof eZContentObjectTreeNode )
    {
        $redirect = $node[0]->attribute( 'url_alias' );        
    }
    else
    {
        $object = eZContentObject::fetchByRemoteID( $objectID );
        if ( $object instanceof eZContentObject )
        {
            $node = $object->attribute( 'main_node' );
            if ( $node instanceof eZContentObjectTreeNode )
            {
                $redirect = $node->attribute( 'url_alias' );        
            }
        }
    }
}

return $module->redirectTo( $redirect );