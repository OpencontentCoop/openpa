<?php

/**
 * Class OpenPASMTPTransport
 * Clone di eZSMTPTransport con funzionalità di log
 *
 * @see eZSMTPTransport
 */
class OpenPASMTPTransport extends eZMailTransport
{
    private $caller;

    private static $emailSenderAddress;

    private static $isDebugSendingEnabled;

    private static $debugReceiverEmail;

    private static $lastError;

    function sendMail(eZMail $mail)
    {
        $this->setCaller();

        $ini = eZINI::instance();
        $parameters = array();
        $parameters['host'] = $ini->variable('MailSettings', 'TransportServer');
        $parameters['helo'] = $ini->variable('MailSettings', 'SenderHost');
        $parameters['port'] = $ini->variable('MailSettings', 'TransportPort');
        $parameters['connectionType'] = $ini->variable('MailSettings', 'TransportConnectionType');
        $user = $ini->variable('MailSettings', 'TransportUser');
        $password = $ini->variable('MailSettings', 'TransportPassword');
        if ($user and
            $password
        ) {
            $parameters['auth'] = true;
            $parameters['user'] = $user;
            $parameters['pass'] = $password;
        }

        /* If email sender hasn't been specified or is empty
         * we substitute it with either MailSettings.EmailSender or AdminEmail.
         */
        if (!$mail->senderText()) {
            $emailSender = self::getEmailSenderAddress();
            if ($emailSender) {
                $mail->setSenderText($emailSender);
            }
        } else {
            $sender = $mail->Mail->from->email;
            $isValid = $sender == self::getEmailSenderAddress();
            if (!$isValid && $ini->hasVariable('MailSettings', 'VerifiedEmailSender')) {
                $verifiedSenders = (array)$ini->variable('MailSettings', 'VerifiedEmailSender');
                $isValid = in_array($sender, $verifiedSenders);
            }
            if (!$isValid) {
                $mail->Mail->from->email = self::getEmailSender();
                $mail->Mail->from->name = $sender;
            }
        }

        $excludeHeaders = $ini->variable('MailSettings', 'ExcludeHeaders');
        if (count($excludeHeaders) > 0) {
            $mail->Mail->appendExcludeHeaders($excludeHeaders);
        }

        $options = new ezcMailSmtpTransportOptions();
        if ($parameters['connectionType']) {
            $options->connectionType = $parameters['connectionType'];
        }
        $smtp = new ezcMailSmtpTransport(
            $parameters['host'],
            $user,
            $password,
            $parameters['port'],
            $options
        );

        /* @see eZMailNotificationTransport::send#49 workaround */
        if (empty($mail->Mail->to) && !empty($mail->Mail->bcc)) {
            $mail->Mail->to = array(array_shift($mail->Mail->bcc));
        }

        // If in debug mode, send to debug email address and nothing else
        if (self::isDebugSendingEnabled()) {
            $mail->Mail->to = array(new ezcMailAddress(self::getDebugReceiverEmail()));
            $mail->Mail->cc = array();
            $mail->Mail->bcc = array();
        }

        // send() from ezcMailSmtpTransport doesn't return anything (it uses exceptions in case
        // something goes bad)
        try {

            $this->validateReceivers($mail->Mail);
            $smtp->send($mail->Mail);

        } catch (ezcMailException $e) {

            eZDebug::writeError($e->getMessage(), __METHOD__);
            $this->logError($mail, $e->getMessage());
            self::$lastError = $e->getMessage();

            return false;
        }
        $this->logSuccess($mail);

        // return true in case of no exceptions
        return true;
    }

    public static function isDebugSendingEnabled()
    {
        if (self::$isDebugSendingEnabled === null) {
            self::$isDebugSendingEnabled = false;
            $ini = eZINI::instance();
            if ($ini->variable('MailSettings', 'DebugSending') == 'enabled') {
                self::$isDebugSendingEnabled = true;
            } else {
                $siteData = eZSiteData::fetchByName('email_debug_sending');
                if ($siteData instanceof eZSiteData) {
                    self::$isDebugSendingEnabled = (int)$siteData->attribute('value') === 1;
                }
            }
        }

        return self::$isDebugSendingEnabled;
    }

    public static function getDebugReceiverEmail()
    {
        if (self::$debugReceiverEmail === null){
            $siteData = eZSiteData::fetchByName('email_debug_receiver');
            if ($siteData instanceof eZSiteData) {
                self::$debugReceiverEmail = $siteData->attribute('value');
            }else{
                self::$debugReceiverEmail = eZINI::instance()->variable('MailSettings', 'DebugReceiverEmail');
            }
        }

        return self::$debugReceiverEmail;
    }

    public static function getEmailSenderAddress()
    {
        if (self::$emailSenderAddress === null) {

            $emailSenderAddress = false;
            $siteData = eZSiteData::fetchByName('email_sender');
            if ($siteData instanceof eZSiteData) {
                $emailSenderAddress = trim($siteData->attribute('value'));
            }

            if (!eZMail::validate($emailSenderAddress)) {
                $ini = eZINI::instance();
                $emailSender = $ini->variable('MailSettings', 'EmailSender');
                if (!$emailSender) {
                    $emailSender = $ini->variable('MailSettings', 'AdminEmail');
                }

                eZMail::extractEmail($emailSender, $emailSenderAddress, $emailSenderName);
            }

            if (eZMail::validate($emailSenderAddress)) {
                self::$emailSenderAddress = $emailSenderAddress;
            }
        }

        return self::$emailSenderAddress;
    }

    public static function getLastError()
    {
        return self::$lastError;
    }

    private function validateReceivers(ezcMail $mail)
    {
        $ini = eZINI::instance();

        $blackListDomains = array();
        if ($ini->hasVariable('MailSettings', 'BlackListEmailDomains')) {
            $blackListDomains = (array)$ini->variable('MailSettings', 'BlackListEmailDomains');
        }
        $blackListDomainSuffixes = array();
        if ($ini->hasVariable('MailSettings', 'BlackListEmailDomainSuffixes')) {
            $blackListDomainSuffixes = (array)$ini->variable('MailSettings', 'BlackListEmailDomainSuffixes');
        }

        /** @var ezcMailAddress[] $toList */
        $toList = array_merge($mail->to, $mail->cc, $mail->bcc);
        foreach ($toList as $address) {
            $domain = substr(strrchr($address->email, "@"), 1);
            $suffix = substr(strrchr($address->email, "."), 1);
            if (in_array($domain, $blackListDomains)) {
                throw new ezcMailException("Receiver domain <{$domain}> is in black list");
            }

            if (in_array($suffix, $blackListDomainSuffixes)) {
                throw new ezcMailException("Receiver domain suffix <{$suffix}> is in black list");
            }
        }
    }

    private function logSuccess(eZMail $mail)
    {
        $message = $this->getLog($mail);
        eZLog::write($message, 'mail.log');
    }

    private function logError(eZMail $mail, $errorMessage)
    {
        $message = $this->getLog($mail);
        eZLog::write($message . ' ' . $errorMessage, 'mail_error.log');
    }

    private function getLog(eZMail $mail)
    {
        $current = OpenPABase::getCurrentSiteaccessIdentifier();
        $caller = $this->getCaller();
        $from = $mail->Mail->from;
        $toList = array_merge($mail->Mail->to, $mail->Mail->cc, $mail->Mail->bcc);
        $to = implode(', ', $toList);
        $subject = $mail->Mail->subject;

        return "[$current] Subject: $subject From: $from To: $to [$caller]";
    }

    private function setCaller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
        $register = array();
        $startRegister = false;
        foreach ($trace as $item) {
            if (isset($item['class']) && $item['class'] == 'eZMailTransport') {
                $startRegister = true;
                continue;
            }
            if ($startRegister) {
                if (!isset($item['class'])){
                    $item['class'] = '';
                }
                if (!isset($item['type'])){
                    $item['type'] = '';
                }
                $register[] = $item['file'] . '#' . $item['line'] . '(' . $item['class'] . $item['type'] . $item['function'] . ')';
            }
        }

        $this->caller = implode(' ', $register);
    }

    private function getCaller()
    {
        return $this->caller;
    }
}
