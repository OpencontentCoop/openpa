<?php

$cli = eZCLI::instance();
$cli->setUseStyles( true );
$cli->setIsQuiet( $isQuiet );

$ini = eZINI::instance();

/** @var CjwNewsletterUser[] $users */
$users = CjwNewsletterUser::fetchObjectList(CjwNewsletterUser::definition(), null, [], ['created' => 'desc']);
foreach ($users as $user) {
    $email = $user->attribute('email');
    $isBounced = OpenPASMTPTransport::isBounced($email);
    if ($isBounced !== false) {
        $cli->output($email);
        $blacklistItemObject = CjwNewsletterBlacklistItem::fetchByEmail($email);
        if (!is_object($blacklistItemObject)) {
            $blacklistItemObject = CjwNewsletterBlacklistItem::create($email, $isBounced);
        }
        $blacklistItemObject->store();
    }
}