<?php
$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/openpaoperator.php',
    'class' => 'OpenPAOperator',
    'operator_names' => array(
        'openpaini',
        'get_main_style',
        'has_main_style',
        'is_area_tematica',
        'get_area_tematica_style',
        'is_dipendente',
        'openpa_shorten',
        'has_abstract',
        'abstract',
        'rss_list',
        'materia_make_tree',
        'access_style',
        'unique',
        'find_first_parent',
        'current_object_id',
        'fix_dimension',
        'object_state_list',
        'solr_field',
        'solr_meta_field',
        'solr_subfield',
        'solr_meta_subfield',
        'search_exclude_class_facets',
        'search_exclude_classes',
        'search_query',
        'strReplace',
        'organigramma',
        'trasparenza_root_node_id',
        'openpa_instance_identifier',
        'openpa_recaptcha_public_key',
        'openpa_no_index_if_needed',
    )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/openpamenuoperator.php',
    'class' => 'OpenPAMenuOperator',
    'operator_names' => array( 'top_menu_cached', 'left_menu_cached', 'tree_menu' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/slugizeoperator.php',
    'class' => 'SlugizeOperator',
    'operator_names' => array( 'slugize' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/cookieoperator.php',
    'class' => 'CookieOperator',
    'operator_names' => array( 'cookieset', 'cookieget', 'check_and_set_cookies' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/checkbrowseroperator.php',
    'class' => 'CheckbrowserOperator',
    'operator_names' => array( 'checkbrowser', 'is_deprecated_browser' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/arraysortoperator.php',
    'class' => 'ArraySortOperator',
    'operator_names' => array(
        'sort',
        'rsort',
        'asort',
        'arsort',
        'ksort',
        'krsort',
        'natsort',
        'natcasesort'
    )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/findgloballayout.php',
    'class' => 'FindGlobalLayoutOperator',
    'operator_names' => array( 'find_global_layout' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/printtools.php',
    'class' => 'PrintToolsOperator',
    'operator_names' => array( 'query_string' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/openpapagedata.php',
    'class' => 'OpenPAPageData',
    'operator_names' => array( 'openpapagedata', 'fill_contacts_matrix', 'contacts_matrix_fields', 'parse_contacts_matrix', 'openpacontext' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/openpahandlersoperator.php',
    'class' => 'OpenPAHandlerOperators',
    'operator_names' => array( 'object_handler' )
);

$eZTemplateOperatorArray[] = array(
    'script' => 'extension/openpa/autoloads/appsoperator.php',
    'class' => 'AppsOperators',
    'operator_names' => array( 'apps_root' )
);
