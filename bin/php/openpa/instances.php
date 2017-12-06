#!/usr/bin/env php
<?php
set_time_limit(0);
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
        'description' => ( "OpenContent Instances" ),
        'use-session' => false,
        'use-modules' => false,
        'use-debug' => true,
        'use-extensions' => true
    )
);


$options = $script->getOptions(
    '[read][regenerate][instance:][filename:][check][current]',
    '',
    array(
        'instance' => 'Esegue solo per l\'istanza selezionata',
        'filename' => 'Nome del file (default: instances.yml)',
        'read' => 'Espone il file instances.yml',
        'regenerate' => 'Rigenera il file instances.yml',
        'check' => 'Confronta il generatore con il file instances.yml',
    )
);
$script->initialize();
$script->startup();

try {
    $instance = $options['instance'] ? $options['instance'] : null;
    if ($options['current']) {
        $instance = OpenPAInstance::current()->getIdentifier();
    }

    $filename = $options['filename'] ? $options['filename'] : 'instances.yml';
    $generator = new OpenPAInstanceGenerator($filename, $options['verbose']);

    if ($options['read']) {
        print_r($generator->read($instance));

    } elseif ($options['regenerate']) {
        $generator->refresh($instance);

    } elseif ($options['check']) {

        if ($instance) {
            $generator->checkInstance($instance);
        } else {
            $siteaccess = OpenPABase::getInstances();
            $arguments = OpenPABase::getOpenPAScriptArguments();

            foreach ($siteaccess as $sa) {
                $command = "php extension/openpa/bin/php/openpa/instances.php -s$sa --filename=$filename --check --current";
                if ($options['verbose']){
                    print "Eseguo: $command \n";
                }
                system($command);
            }
        }

    }
    $script->shutdown();

} catch (Exception $e) {
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown($errCode, $e->getMessage());
}
