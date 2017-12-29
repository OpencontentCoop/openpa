<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA set recaptca keys" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();
$options = $script->getOptions(
    "[public:][private:]",
    "",
    array(
        "public" => "public key",
        "private" => "private key",
    )
);
$script->initialize();
try
{
    $recaptcha = new OpenPARecaptcha();
    $recaptcha->store($options['public'], $options['private']);
    $cli->warning('Recaptch stored for ' . OpenPABase::getCurrentSiteaccessIdentifier());
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
