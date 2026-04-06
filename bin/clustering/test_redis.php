<?php
require 'autoload.php';

use Predis\Client;

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Test redis"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));

$redisClient = OpenPADFSFileHandlerDFSRedis::build()->getRedisClient();
$redisClient->set('test-redis', 'Test Redis ok');
$test = $redisClient->get('test-redis');
$cli->output($test);

$redisClient->del('test-redis');
$test = $redisClient->get('test-redis');
if (!empty($test)){
    $cli->error('Fail deleting item');
}

$script->shutdown();