<?php
require 'autoload.php';


$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Truncate ezdfs cache"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$postgresqlBackend = new eZDFSFileHandlerPostgresqlBackend;
$postgresqlBackend->_connect();
$stmt = $postgresqlBackend->db->query('TRUNCATE ezdfsfile_cache;');
if ($stmt == false) {
    $cli->error(pg_result_error_field($stmt, PGSQL_DIAG_SQLSTATE) . ': ' . pg_result_error_field($stmt, PGSQL_DIAG_MESSAGE_PRIMARY));
}

$script->shutdown();