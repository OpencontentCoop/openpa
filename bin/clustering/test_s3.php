<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Test s3"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$s3Backend = OpenPADFSFileHandlerDFSAWSS3Public::build();
$s3client = $s3Backend->getS3Client();
$bucket = $s3Backend->getBucket();

$cli->output("Test bucket $bucket");

$key = 'test';
$filePath = eZSys::cacheDirectory() . '/' . 'test-s3.txt';
$testContent = 'Test aws s3 connection';

eZFile::create(basename($filePath), dirname($filePath), $testContent);
try {
    $s3client->putObject(
        array(
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $filePath
        )
    );

    $object = $s3client->getObject(
        array(
            'Bucket' => $bucket,
            'Key' => $key
        )
    );
    $content = (string)$object['Body'];
    if ($content == $testContent) {
        $cli->output('Test S3 ok');
    }

    $s3client->deleteObject(
        array(
            'Bucket' => $bucket,
            'Key' => $key,
        )
    );
}catch (Exception $e){
    if ($e instanceof \Aws\Exception\AwsException)
        $cli->error($e->getAwsErrorMessage());
    else
        $cli->error($e->getMessage());
}
unlink($filePath);

$script->shutdown();