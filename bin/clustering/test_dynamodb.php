<?php
require 'autoload.php';

use Aws\DynamoDb\Exception\DynamoDbException;

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Test DynamoDb"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

try {

    $dynamoHandler = OpenPADFSFileHandlerDFSAWSDynamoDb::build();
    $dynamoHandler->set('test-dynamodb', 'Test set/get DynamoDb ok');
    $test = $dynamoHandler->get('test-dynamodb');
    if (empty($test) || $test != 'Test set/get DynamoDb ok') {
        $cli->error('Fail set/get item');
    } else {
        $cli->output($test);
    }

    $dynamoHandler->del('test-dynamodb');
    $test = $dynamoHandler->get('test-dynamodb');
    if (!empty($test)) {
        $cli->error('Fail deleting item');
    }

    $dynamoHandler->set('test-dynamodb', 'Test rename DynamoDb ok');
    $dynamoHandler->rename('test-dynamodb', 'test-rename-dynamodb');
    $test = $dynamoHandler->get('test-rename-dynamodb');
    if (empty($test) || $test != 'Test rename DynamoDb ok') {
        $cli->error('Fail rename item');
    } else {
        $cli->output($test);
    }
    $dynamoHandler->del('test-rename-dynamodb');

    $data = '';
    $size = 1024 * 1024 * 10; // 10mb
    $chunk = 1024;
    while ($size > 0) {
        $data .= str_pad('', min($chunk, $size), rand(1, 9));
        $size -= $chunk;
    }

    $dynamoHandler->set('test-large-file', $data);
    $test = $dynamoHandler->get('test-large-file');
    if (empty($test) || $test != $data) {
        $cli->error('Fail set/get large item');
    } else {
        $test = $dynamoHandler->size('test-large-file');
        if ($test != (1024 * 1024 * 10)) {
            $cli->error('Fail set/get large item on retrieving size');
        } else {
            $megabytes = round($test / 1024 / 1024, 2);
            $cli->output("Test get/set {$megabytes}MB item ok");
        }
    }

    $dynamoHandler->del('test-large-file');
}catch (DynamoDbException $e){
    $cli->error($e->getAwsErrorMessage());
}catch (Exception $e){
    $cli->error($e->getMessage());
}

$script->shutdown();