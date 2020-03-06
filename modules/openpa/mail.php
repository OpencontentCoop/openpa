<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$ini = eZINI::instance();

$transportType = trim($ini->variable('MailSettings', 'Transport'));
$optionArray = array(
    'iniFile' => 'site.ini',
    'iniSection' => 'MailSettings',
    'iniVariable' => 'TransportAlias',
    'handlerIndex' => strtolower($transportType)
);
$options = new ezpExtensionOptions($optionArray);
$transportClass = eZExtension::getHandlerClass($options);
$canOverride = $transportClass instanceof OpenPASMTPTransport;

$senders = array(
    'current' => OpenPASMTPTransport::getEmailSenderAddress(),
    'db' => false,
    'ini' => false,
);
$emailSenderAddress = $ini->variable('MailSettings', 'EmailSender');
if (eZMail::validate($emailSenderAddress)) {
    $senders['ini'] = $emailSenderAddress;
} else {
    $emailSenderAddress = $ini->variable('MailSettings', 'AdminEmail');
    if (eZMail::validate($emailSenderAddress)) {
        $senders['ini'] = $emailSenderAddress;
    }
}
$siteData = eZSiteData::fetchByName('email_sender');
if ($siteData instanceof eZSiteData) {
    $emailSenderAddress = trim($siteData->attribute('value'));
    if (eZMail::validate($emailSenderAddress)) {
        $senders['db'] = $emailSenderAddress;
    }
}

$debugs = array(
    'current' => OpenPASMTPTransport::isDebugSendingEnabled(),
    'db' => false,
    'ini' => $ini->variable('MailSettings', 'DebugSending') == 'enabled',
);
$siteData = eZSiteData::fetchByName('email_debug_sending');
if ($siteData instanceof eZSiteData) {
    $debugs['db'] = (int)$siteData->attribute('value') === 1;
}

$receivers = array(
    'current' => OpenPASMTPTransport::getDebugReceiverEmail(),
    'db' => false,
    'ini' => $ini->variable('MailSettings', 'DebugReceiverEmail'),
);
$siteData = eZSiteData::fetchByName('email_debug_receiver');
if ($siteData instanceof eZSiteData) {
    $receivers['db'] = $siteData->attribute('value');
}

$errors = [];
if ($http->hasPostVariable('SendTestMail') && $canOverride) {
    $mail = new eZMail();
    $mail->setSubject('Test invio mail da ' . eZINI::instance()->variable('SiteSettings', 'SiteName'));
    $mail->setReceiver($receivers['current']);
    if (!eZMailTransport::send($mail)){
        $errors[] = 'Mail non inviata: ' . OpenPASMTPTransport::getLastError();
    }

} elseif ($http->hasPostVariable('StoreMailSettings') && $canOverride) {
    $actions = [
        'sender' => false,
        'receiver' => false,
        'debug' => false,
    ];

    if ($http->hasPostVariable('Sender') && $http->postVariable('Sender') != '') {
        $newSender = trim($http->postVariable('Sender'));
        if (!eZMail::validate($newSender)) {
            $errors[] = "Mail $newSender non valida";
        } else {
            if ($newSender != $senders['ini'] && $newSender != $senders['current']) {
                $actions['sender'] = $newSender;
            }
        }
    }

    if ($http->hasPostVariable('DebugSendField') && !$debugs['ini']) {
        $actions['debug'] = $http->hasPostVariable('DebugSend');
    }

    if ($http->hasPostVariable('DebugReceiver') && $http->postVariable('DebugReceiver') != '') {
        $newReceiver = trim($http->postVariable('DebugReceiver'));
        if (!eZMail::validate($newReceiver)) {
            $errors[] = "Mail $newReceiver non valida";
        } else {
            if ($newReceiver != $senders['ini'] && $newReceiver != $senders['current']) {
                $actions['receiver'] = $newReceiver;
            }
        }
    }

    $siteData = eZSiteData::fetchByName('email_sender');
    if ($siteData instanceof eZSiteData) {
        if ($actions['sender']) {
            $siteData->setAttribute('value', $actions['sender']);
            $siteData->store();
        } else {
            $siteData->remove();
        }
    } elseif ($actions['sender']) {
        $siteData = new eZSiteData([
            'name' => 'email_sender',
            'value' => $actions['sender']
        ]);
        $siteData->store();
    }

    $siteData = eZSiteData::fetchByName('email_debug_sending');
    if ($siteData instanceof eZSiteData) {
        if ($actions['debug']) {
            $siteData->setAttribute('value', $actions['debug']);
            $siteData->store();
        } else {
            $siteData->remove();
        }
    } elseif ($actions['debug']) {
        $siteData = new eZSiteData([
            'name' => 'email_debug_sending',
            'value' => $actions['debug']
        ]);
        $siteData->store();
    }

    $siteData = eZSiteData::fetchByName('email_debug_receiver');
    if ($siteData instanceof eZSiteData) {
        if ($actions['receiver']) {
            $siteData->setAttribute('value', $actions['receiver']);
        } else {
            $siteData->remove();
        }
    } elseif ($actions['receiver']) {
        $siteData = new eZSiteData([
            'name' => 'email_debug_receiver',
            'value' => $actions['receiver']
        ]);
        $siteData->store();
    }

    $module->redirectTo('openpa/mail');
    return;
}

$tpl->setVariable('errors', $errors);

$tpl->setVariable('can_override', $canOverride);
$tpl->setVariable('transport', $transportType);

$tpl->setVariable('senders', $senders);
eZDebug::writeDebug($senders, 'senders');

$tpl->setVariable('debugs', $debugs);
eZDebug::writeDebug($debugs, 'debugs');

$tpl->setVariable('receivers', $receivers);
eZDebug::writeDebug($receivers, 'receivers');

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/mail.tpl');
$Result['path'] = array(array('text' => 'Impostazioni Mail', 'url' => false));
