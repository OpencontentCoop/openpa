<?php

/** @var eZModule $Module */
$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$ini = eZINI::instance();
$db = eZDB::instance();
$Result = array();

$currentUser = eZUser::currentUser();

$invalidForm = false;
$errors = array();
$showCaptcha = false;
$name = $email = $password = $needCheckMail = false;


$tpl->setVariable( 'name', $name );
$tpl->setVariable( 'email', $email );

if ( $http->hasPostVariable( 'RedirectURI' ) ) $http->setSessionVariable( "RedirectURI", $http->postVariable( 'RedirectURI' ) );
$redirect = $http->hasSessionVariable( "RedirectURI" ) ? $http->sessionVariable( "RedirectURI" ) : '/';
$tpl->setVariable( 'redirect', $redirect );

#if ( $http->hasSessionVariable( "RegisterUserID" ) ) echo '<pre>'.$http->sessionVariable( "RegisterUserID" ).'</pre>';

if ( $http->hasPostVariable( 'RegisterButton' ) )
{
    if ( $http->hasSessionVariable( "RegisterUserID" ) )
    {
        $showCaptcha = true;
    }
    else
    {
        $name = false;
        if ( $http->hasPostVariable( 'Name' ) )
        {
            $name = $http->postVariable( 'Name' );
        }
        $email = false;
        if ( $http->hasPostVariable( 'EmailAddress' ) )
        {
            $email = $http->postVariable( 'EmailAddress' );
            if ( !eZMail::validate( $email ) )
            {
                $invalidForm = true;
                $errors[] = ezpI18n::tr( 'openpa/signup',  'Indirizzo email non valido'  );
            }
            if ( eZUser::fetchByEmail( $email ) )
            {
                $invalidForm = true;
                $errors[] = ezpI18n::tr( 'openpa/signup', 'Email già in uso' );
            }
        }
        $password = false;
        if ( $http->hasPostVariable( 'Password' ) )
        {
            $password = $http->postVariable( 'Password' );
            if ( !eZUser::validatePassword( $password ) )
            {
                $invalidForm = true;
                $password = false;
                $minPasswordLength = $ini->variable( 'UserSettings', 'MinPasswordLength' );
                $errors[] = ezpI18n::tr( 'openpa/signup', 'La password deve essere lunga almeno %1 caratteri', null, array( $minPasswordLength ) );
            }
            if ( strtolower( $password ) == 'password' )
            {
                $invalidForm = true;
                $password = false;
                $errors[] = ezpI18n::tr( 'openpa/signup', 'La password non può essere "password".' );
            }
        }

        $tpl->setVariable( 'name', $name );
        $tpl->setVariable( 'email', $email );

        if ( !$name || !$email || !$password )
        {
            $invalidForm = true;
            $errors[] = ezpI18n::tr( 'openpa/signup', 'Inserire tutti i dati richiesti' );
            $showCaptcha = true;
        }
    }


    if ( !$invalidForm && !$http->hasSessionVariable( "RegisterUserID" ) && $name && $email && $password )
    {
        $showCaptcha = true;

        $db->begin();
        $defaultUserPlacement = (int)$ini->variable( "UserSettings", "DefaultUserPlacement" );
        $sql = "SELECT count(*) as count FROM ezcontentobject_tree WHERE node_id = $defaultUserPlacement";
        $rows = $db->arrayQuery( $sql );
        $count = $rows[0]['count'];
        if ( $count < 1 )
        {
            $errors[] = ezpI18n::tr( 'openpa/signup', 'Il nodo (%1) specificato in [UserSettings].DefaultUserPlacement setting in site.ini non esiste!', null, array( $defaultUserPlacement ) );
            $invalidForm = true;
        }
        else
        {
            $userClassID = $ini->variable( "UserSettings", "UserClassID" );
            $class = eZContentClass::fetch( $userClassID );
            $userCreatorID = $ini->variable( "UserSettings", "UserCreatorID" );
            $defaultSectionID = $ini->variable( "UserSettings", "DefaultSectionID" );
            $contentObject = $class->instantiate( $userCreatorID, $defaultSectionID );
            $objectID = $contentObject->attribute( 'id' );

            $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $objectID,
                                                               'contentobject_version' => 1,
                                                               'parent_node' => $defaultUserPlacement,
                                                               'is_main' => 1 ) );
            $nodeAssignment->store();

            $handler = OpenPAObjectHandler::instanceFromContentObject( $contentObject );
            if ( $handler->hasAttribute( 'first_name' ) && $handler->hasAttribute( 'user_account' ) )
            {
                $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->fromString( $name );
                $handler->attribute( 'first_name' )->attribute( 'contentobject_attribute' )->store();

                $user = eZUser::create( $objectID );
                $login = $email;
                eZDebugSetting::writeDebug( 'kernel-user', $password, "password" );
                eZDebugSetting::writeDebug( 'kernel-user', $login, "login" );
                eZDebugSetting::writeDebug( 'kernel-user', $email, "email" );
                eZDebugSetting::writeDebug( 'kernel-user', $objectID, "contentObjectID" );

                $user->setInformation( $objectID, $login, $email, $password, $password );
                $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->setContent( $user );
                $handler->attribute( 'user_account' )->attribute( 'contentobject_attribute' )->store();
            }
            else
            {
                return $Module->handleError(  eZError::KERNEL_ACCESS_DENIED,  false, array(),  array( 'OpenPaErrorCode', 1 ) );
            }

            eZUserOperationCollection::setSettings( $objectID, 0, 0 );
            $http->setSessionVariable( "RegisterUserID", $objectID );
        }
        $db->commit();
    }
}
elseif ( $http->hasPostVariable( 'CaptchaButton' ) && $http->hasSessionVariable( "RegisterUserID" ) )
{
    // if captch ok
    //publish user $http->sessionVariable( "RegisterUserID" )
    //remove session
    // else

    require_once 'extension/openpa/lib/recaptchalib.php';
    $ezcommentsIni = eZINI::instance( 'ezcomments.ini' );
    $privateKey = $ezcommentsIni->variable( 'RecaptchaSetting' , 'PrivateKey' );
    if( $http->hasPostVariable( 'recaptcha_challenge_field' ) &&
        $http->hasPostVariable( 'recaptcha_response_field' ) )
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $challengeField = $http->postVariable( 'recaptcha_challenge_field' );
        $responseField = $http->postVariable( 'recaptcha_response_field' );
        $capchaResponse = recaptcha_check_answer( $privateKey, $ip, $challengeField, $responseField );
        if( !$capchaResponse->is_valid )
        {
            $showCaptcha = true;
            $errors[] = ezpI18n::tr( 'openpa/signup', 'Il codice inserito non è corretto.' );
        }
    }
    else
    {
        $showCaptcha = true;
        $errors[] = ezpI18n::tr( 'openpa/signup', 'Errore nella configurazione del Captcha.' );
    }

    if ( !$showCaptcha )
    {
        $object = eZContentObject::fetch( $http->sessionVariable( "RegisterUserID" ) );
        if ( $object instanceof eZContentObject )
        {
            eZUserOperationCollection::setSettings( $object->attribute( 'id' ), 0, 0 );

            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id' => $object->attribute( 'id' ), 'version' => 1 ) );
            eZUserOperationCollection::setSettings( $object->attribute( 'id' ), 0, 0 );
            if ( ( array_key_exists( 'status', $operationResult ) && $operationResult['status'] != eZModuleOperationInfo::STATUS_CONTINUE ) )
            {
                eZDebug::writeDebug( $operationResult, __FILE__ );
                switch ( $operationResult['status'] )
                {
                    case eZModuleOperationInfo::STATUS_REPEAT:
                    {
                        eZContentOperationCollection::setVersionStatus(
                            $object->attribute( 'id' ),
                            1,
                            eZContentObjectVersion::STATUS_REPEAT
                        );
                    }
                        break;
                    case eZModuleOperationInfo::STATUS_HALTED:
                    {
                        if ( isset( $operationResult['redirect_url'] ) )
                        {
                            $Module->redirectTo( $operationResult['redirect_url'] );

                            return;
                        }
                        else if ( isset( $operationResult['result'] ) )
                        {
                            $result = $operationResult['result'];
                            $resultContent = false;
                            if ( is_array( $result ) )
                            {
                                if ( isset( $result['content'] ) )
                                {
                                    $resultContent = $result['content'];
                                }
                                if ( isset( $result['path'] ) )
                                {
                                    $Result['path'] = $result['path'];
                                }
                            }
                            else
                            {
                                $resultContent = $result;
                            }
                        }
                    }
                        break;
                    case eZModuleOperationInfo::STATUS_CANCELLED:
                    {
                        $Result = array();
                        $Result['content'] = "Content publish cancelled";
                    }
                        break;
                }
            }
            else
            {
                $http->removeSessionVariable( 'RegisterUserID' );
                /** @var eZUser $user */
                $user = eZUser::fetch( $object->attribute( 'id' ) );

                if ( $user === null )
                    return $Module->handleError( eZError::KERNEL_NOT_FOUND, 'kernel' );

                $userSetting = eZUserSetting::fetch( $object->attribute( 'id' ) );
                if ( $userSetting instanceof eZUserSetting && $user instanceof eZUser )
                {
                    $hash = md5( mt_rand() . time() . $user->id() );
                    $accountKey = eZUserAccountKey::createNew( $user->id(), $hash, time() );
                    $accountKey->store();
                }
                else
                {
                    throw new Exception( "UserSettings not found for user #" . $user->id() );
                }

                $tpl->setVariable( 'hash', $hash );

                $userSetting = eZUserSetting::fetch( $object->attribute( 'id' ) );
                if ( $userSetting instanceof eZUserSetting && $user instanceof eZUser )
                {
                    $hash = md5( mt_rand() . time() . $user->id() );
                    $accountKey = eZUserAccountKey::createNew( $user->id(), $hash, time() );
                    $accountKey->store();
                }
                else
                {
                    throw new Exception( "UserSettings not found for user #" . $user->id() );
                }

                $tpl->setVariable( 'redirect', str_replace( '/', ':', $redirect ) );
                $tpl->setVariable( 'hash', $hash );
                $tpl->setVariable( 'user', $user );
                $body = $tpl->fetch( 'design:smartlogin/mail/registrationinfo.tpl' );

                $emailSender = $ini->variable( 'MailSettings', 'EmailSender' );
                if ( $tpl->hasVariable( 'email_sender' ) )
                    $emailSender = $tpl->variable( 'email_sender' );
                else if ( !$emailSender )
                    $emailSender = $ini->variable( 'MailSettings', 'AdminEmail' );

                if ( $tpl->hasVariable( 'subject' ) )
                    $subject = $tpl->variable( 'subject' );
                else
                    $subject = ezpI18n::tr( 'kernel/user/register', 'Informazioni di registrazione' );

                $tpl->setVariable( 'title', $subject );
                $tpl->setVariable( 'content', $body );
                $templateResult = $tpl->fetch( 'design:smartlogin/mail/mail_pagelayout.tpl' );

                $mail = new eZMail();
                $mail->setSender( $emailSender );
                $receiver = $user->attribute( 'email' );
                $mail->setReceiver( $receiver );
                $mail->setSubject( $subject );
                $mail->setBody( $templateResult );
                $mail->setContentType( 'text/html' );
                $mailResult = eZMailTransport::send( $mail );

                $needCheckMail = true;
            }
        }
    }
}
elseif ( !$needCheckMail )
{
    $Module->redirectTo( $redirect );
    $http->removeSessionVariable( 'RedirectURI' );
}

$tpl->setVariable( 'check_mail', $needCheckMail );
$tpl->setVariable( 'show_captcha', $showCaptcha );
$tpl->setVariable( 'invalid_form', $invalidForm );
$tpl->setVariable( 'errors', $errors );
$tpl->setVariable( 'current_user', $currentUser );
$tpl->setVariable( 'persistent_variable', array() );

$Result['persistent_variable'] = $tpl->variable( 'persistent_variable' );
if ( !isset( $Result['content'] ) )
    $Result['content'] = $tpl->fetch( 'design:smartlogin/register.tpl' );
$Result['node_id'] = 0;

$contentInfoArray = array( 'url_alias' => 'smartlogin/signup', 'class_identifier' => false );
$contentInfoArray['persistent_variable'] = false;
if ( $tpl->variable( 'persistent_variable' ) !== false )
{
    $contentInfoArray['persistent_variable'] = $tpl->variable( 'persistent_variable' );
}
$Result['content_info'] = $contentInfoArray;
$Result['path'] = array();