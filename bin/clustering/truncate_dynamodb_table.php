<?php
require 'autoload.php';

use Aws\DynamoDb\Exception\DynamoDbException;

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Truncate DynamoDb"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

try {
    $dynamoHandler = OpenPADFSFileHandlerDFSAWSDynamoDb::build();
    $dynamoHandler->deleteTable();
    $dynamoHandler->initTable();
}catch (DynamoDbException $e){
    $cli->error($e->getAwsErrorMessage());
} catch (Exception $e) {
    $cli->error($e->getMessage());
}

$script->shutdown();