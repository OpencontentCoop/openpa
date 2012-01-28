<?php
$eZTemplateOperatorArray[] = array( 'script' => 'extension/openpa/autoloads/openpaoperator.php',
                                    'class' => 'OpenPAOperator',
                                    'operator_names' => array( 'openpaini', 'get_main_style', 'has_main_style', 'is_area_tematica', 'get_area_tematica_style', 'is_dipendente', 'openpa_shorten', 'has_abstract', 'abstract' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/openpa/autoloads/slugizeoperator.php',
                                    'class' => 'SlugizeOperator',
                                    'operator_names' => array( 'slugize' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/openpa/autoloads/cookieoperator.php',
                                    'class' => 'CookieOperator',
                                    'operator_names' => array( 'cookieset', 'cookieget', 'check_and_set_cookies' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/openpa/autoloads/checkbrowseroperator.php',
                                    'class' => 'CheckbrowserOperator',
                                    'operator_names' => array( 'checkbrowser', 'is_deprecated_browser' ) );

$eZTemplateOperatorArray[] = array( 'script' => 'extension/openpa/autoloads/arraysortoperator.php',
                                    'class' => 'ArraySortOperator',
                                    'operator_names' => array( 'sort', 'rsort', 'asort', 'arsort', 'ksort', 'krsort', 'natsort', 'natcasesort' ) );

?>
