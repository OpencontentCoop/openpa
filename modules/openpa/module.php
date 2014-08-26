<?php


$Module = array( 'name' => 'OpenPa',
                 'variable_params' => true );

$ViewList = array();

$ViewList['roles'] = array( 'functions' => array( 'manage_roles' ),
                           'script' => 'roles.php',
                           'default_navigation_part' => 'ociniguinavigationpart',
                           'params' => array(),
                           'unordered_params' => array() );

$ViewList['class'] = array( 'functions' => array( 'class' ),
                           'script' => 'class.php',
                           'ui_context' => 'edit',
                           'default_navigation_part' => 'ezsetupnavigationpart',
                           'single_post_actions' => array( 'SyncButton' => 'Sync',
                                                           'InstallButton' => 'Install' ),
                           'params' => array( 'ID' ),
                           'unordered_params' => array() );

$ViewList['classlist'] = array( 'functions' => array( 'class' ),
                           'script' => 'classlist.php',
                           'ui_context' => 'edit',
                           'default_navigation_part' => 'ezsetupnavigationpart',                            
                           'params' => array( ),
                           'unordered_params' => array() );

$ViewList['classdefinition'] = array( 'functions' => array( 'classdefinition' ),
                                      'script' => 'classdefinition.php',
                                      'ui_context' => 'edit',
                                      'default_navigation_part' => 'ezsetupnavigationpart',
                                      'params' => array( 'ID' ),
                                      'unordered_params' => array() );

$ViewList['relations'] = array( 'functions' => array( 'classdefinition' ),
                                      'script' => 'relations.php',                                      
                                      'params' => array( 'ID' ),
                                      'unordered_params' => array() );

$ViewList['classes'] = array( 'functions' => array( 'classdefinition' ),
                                'script' => 'classes.php',
                                'params' => array( 'Identifier' ),
                                'unordered_params' => array() );

$ViewList['addlocationto'] = array( 'functions' => array( 'editor_tools' ),
                                      'script' => 'addlocationto.php',                                      
                                      'params' => array( 'ContentObjectID' ),
                                      'unordered_params' => array() );

//$ViewList['manage_header'] = array( 'functions' => array( 'manage_header' ),
//                           'script' => 'manage_header.php',
//                           'default_navigation_part' => 'ociniguinavigationpart',
//                           'params' => array(),
//                           'unordered_params' => array() );

$ViewList['calendar'] = array( 'functions' => array( 'calendar' ),
                           'script' => 'calendar.php',
                           'params' => array( 'NodeID' ) );

$ViewList['refreshmenu'] = array( 'functions' => array( 'editor_tools' ),
                           'script' => 'refreshmenu.php',
                           'params' => array( 'ID', 'SiteAccess', 'File' ) );

$ViewList['add'] = array( 'functions' => array( 'editor_tools' ),
                           'script' => 'add.php',
                           'params' => array( 'Class' ),
                           'unordered_params' => array() );

$ViewList['object'] = array( 'functions' => array( 'object' ),
                             'script' => 'object.php',
                             'params' => array( 'ObjectID' ),
                             'unordered_params' => array() );
                           
$FunctionList['classdefinition'] = array();
$FunctionList['class'] = array();
$FunctionList['manage_roles'] = array();
//$FunctionList['manage_header'] = array();
$FunctionList['calendar'] = array();
$FunctionList['editor_tools'] = array();
$FunctionList['object'] = array();



?>
