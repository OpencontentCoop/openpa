<?php
require 'autoload.php';

$script = eZScript::instance([
    'description' => ("Check is address is suppressed from aws\n\n"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true,
]);

$script->startup();

$options = $script->getOptions(
    '[email:][region:]',
    '',
    [
        'email' => 'email address',
        'region' => 'aws region',
    ]
);
$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();

$email = $options['email'];
if (empty($email) || !eZMail::validate($email)) {
    $cli->error("Invalid email address");
    $script->shutdown(1);
}

$region = $options['region'] ?? 'eu-west-1';
$cli->output("Checking email $email in $region");
$isSuppressed = OpenPASMTPTransport::isSesSuppressedDestination($email, $region);

if (!$isSuppressed) {
    $cli->output("Valid email address");
} else {
    $cli->output("Suppressed email address: " . $isSuppressed);
}

$script->shutdown();