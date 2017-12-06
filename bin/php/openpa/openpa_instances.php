#!/usr/bin/env php
<?php
set_time_limit( 0 );
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(
    array(
         'description' => ( "OpenPa Instances" ),
         'use-session' => false,
         'use-modules' => true,
         'use-debug' => true,
         'use-extensions' => true
    )
);


$options = $script->getOptions(
    '[check][wiki][regenerate][fixvh]',
    '',
    array(
         'check' => 'Confronta i valori live con il file instances.yml',
         'regenerate' => 'Rigenera il file instances.yml',
         'wiki' => 'Stampa a video i valori in formato wiki table',
         'fixvh' => 'Stampa a video i valori per cui è necessario correggere il virtual host'
    )
);
$script->initialize();
$script->startup();

$cli->error("Script deprecato: usare extension/openpa/bin/php/openpa/instances.php");
$script->shutdown();

