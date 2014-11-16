<?php
$Module = array( 'name' => 'apps' );

$ViewList = array();

$ViewList['dashboard'] = array(
	'script'					=>	'dashboard.php',
	'params'					=> 	array(),
	'unordered_params'			=> 	array(),
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array(),
	'default_navigation_part'   => 'ezappsnavigationpart',
	'functions'					=> array( 'dashboard' )
);

$FunctionList['dashboard'] = array();

