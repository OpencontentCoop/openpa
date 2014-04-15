#!/usr/bin/env php
<?php
set_time_limit ( 0 );
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array(  'description' => ( "Clean SqliImport Token" ),
                                      'use-session' => false,
                                      'use-modules' => true,
                                      'use-extensions' => true ) );


$options = $script->getOptions();
$script->initialize();

$script->startup();

$script->initialize();

SQLIImportToken::cleanImportToken();

$script->shutdown();
?>