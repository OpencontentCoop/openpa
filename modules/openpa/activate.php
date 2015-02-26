<?php
/** @var eZModule $Module */
$Module = $Params['Module'];
$http = eZHTTPTool::instance();

$hash = trim( $http->hasPostVariable( 'Hash' ) ? $http->postVariable( 'Hash' ) : $Params['Hash'] );
$mainNodeID = (int)$http->hasPostVariable( 'MainNodeID' ) ? $http->postVariable( 'MainNodeID' ) : $Params['MainNodeID'];
$redirect = trim( $http->hasPostVariable( 'RedirectURI' ) ? $http->postVariable( 'RedirectURI' ) : $Params['RedirectURI']
);
$redirect = str_replace( ':', '/', $redirect );

// Prepend or append the hash string with a salt, and md5 the resulting hash
// Example: use is login name as salt, and a 'secret password' as hash sent to the user
if ( $http->hasPostVariable( 'HashSaltPrepend' ) )
{
    $hash = md5( trim( $http->postVariable( 'HashSaltPrepend' ) ) . $hash );
}
else if ( $http->hasPostVariable( 'HashSaltAppend' ) )
{
    $hash = md5( $hash . trim( $http->postVariable( 'HashSaltAppend' ) ) );
}


// Check if key exists
$accountActivated = false;
$alreadyActive = false;
$isPending = false;
/** @var eZUserAccountKey $accountKey */
$accountKey = $hash ? eZUserAccountKey::fetchByKey( $hash ) : false;

if ( $accountKey )
{
    $accountActivated = true;
    $userID = $accountKey->attribute( 'user_id' );

    $userContentObject = eZContentObject::fetch( $userID );
    if ( !$userContentObject instanceof eZContentObject )
    {
        return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
    }

    if ( $userContentObject->attribute( 'main_node_id' ) != $mainNodeID )
    {
        return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
    }

    // Enable user account
    eZUserOperationCollection::activation( $userID, $hash, true );

    if ( $publishResult['status'] === eZModuleOperationInfo::STATUS_HALTED )
    {
        $isPending = true;
    }
    else
    {
        /** @var eZUser $user */
        $user = eZUser::fetch( $userID );

        if ( $user === null )
        {
            return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );
        }

        $user->loginCurrent();
    }
}
elseif ( $mainNodeID )
{
    $userContentObject = eZContentObject::fetchByNodeID( $mainNodeID );
    if ( $userContentObject instanceof eZContentObject )
    {
        /** @var eZUserSetting $userSetting */
        $userSetting = eZUserSetting::fetch( $userContentObject->attribute( 'id' ) );

        if ( $userSetting !== null && $userSetting->attribute( 'is_enabled' ) )
        {
            $alreadyActive = true;
        }
    }
}

// Template handling

if ( $alreadyActive || $accountActivated )
{
    $Module->redirectTo( $redirect );
    return;
}

$tpl = eZTemplate::factory();

$tpl->setVariable( 'module', $Module );
$tpl->setVariable( 'account_activated', $accountActivated );
$tpl->setVariable( 'already_active', $alreadyActive );
$tpl->setVariable( 'is_pending', $isPending );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:smartlogin/activate.tpl' );
$Result['path'] = array(
    array(
        'text' => ezpI18n::tr( 'kernel/user', 'User' ),
        'url' => false
    ),
    array(
        'text' => ezpI18n::tr( 'kernel/user', 'Activate' ),
        'url' => false
    )
);

?>
