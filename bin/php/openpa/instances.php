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
    '[read][regenerate][instance:][filename:]',
    '',
    array(
        'instance' => 'Esegue solo per l\'istanza selezionata',
        'filename' => 'Nome del file (default: instances.yml)',
        'read' => 'Espone il file instances.yml',
        'regenerate' => 'Rigenera il file instances.yml'
    )
);
$script->initialize();
$script->startup();

$errors = array();

$filename = $options['filename'] ? $options['filename'] : 'instances.yml';
$generator = new OpenPAInstanceGenerator($filename,$options['verbose']);
if ( $options['read'] )
    print_r( $generator->read( $options['instance'] ) );
elseif ( $options['regenerate'] )
    $generator->refresh( $options['instance'] );

$script->shutdown();
?>