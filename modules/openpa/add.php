<?php
/** @var eZModule $module */
$module = $Params['Module'];
$http = eZHTTPTool::instance();
$contentClassIdentifier = $Params['Class'];

$class = eZContentClass::fetchByIdentifier( $contentClassIdentifier );
if ( !$class instanceof eZContentClass )
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );

$queryString = '';
if ( $_SERVER['QUERY_STRING'] )
{
    $queryStringParts =  explode( '/', $_SERVER['QUERY_STRING'] );    
    $queryString = '?' . array_pop( $queryStringParts );
}
    
if ( $http->hasGetVariable( 'parent' ) )
{
    $parent = $http->getVariable( 'parent', false );
    $node = eZContentObjectTreeNode::fetch( intval( $parent ) );
    if ( $node instanceof eZContentObjectTreeNode && $class->attribute( 'id' ) && $node->canCreate() )
    {
        $languageCode = eZINI::instance()->variable( 'RegionalSettings', 'Locale' );    
        $object = eZContentObject::createWithNodeAssignment( $node,
                                                             $class->attribute( 'id' ),
                                                             $languageCode,
                                                             false );
        if ( $object )
        {                
            $module->redirectTo( 'content/edit/' . $object->attribute( 'id' ) . '/' . $object->attribute( 'current_version' ) . $queryString );
            return;
        }
        else
            return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
    }
    else
        return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}
elseif ( $http->hasGetVariable( 'from' ) )
{
    $from = $http->getVariable( 'from', false );
    $object = eZContentObject::fetch( intval( $from ) );
    try
    {
        $copy = OpenPAObjectTools::copyObject( $object );
        $module->redirectTo( 'content/edit/' . $copy->attribute( 'id' ) . '/' . $copy->attribute( 'current_version' ) . $queryString );
        return;
    }
    catch( InvalidArgumentException $e )
    {
        return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
    catch( Exception $e )
    {
        return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
    }
}
else
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    
?>