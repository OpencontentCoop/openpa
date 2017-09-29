<?php

require 'autoload.php';

$script = eZScript::instance(array(
    'description' => ( "OpenPA Change state\n\n" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true
));

$script->startup();

$options = $script->getOptions('[id:][class:][dump-rules]',
    '',
    array(
        'id' => "Object Id",
        'class' => "Class identifier",
        'dump-rules' => "Export active rules"
    )
);

$script->initialize();
$script->setUseDebugAccumulators(true);

$cli = eZCLI::instance();
$cli->setUseStyles(true);
$cli->setIsQuiet($isQuiet);


/** @var eZUser $user */
$user = eZUser::fetchByName('admin');
if ($user) {
    eZUser::setCurrentlyLoggedInUser($user, $user->attribute('contentobject_id'));
} else {
    throw new InvalidArgumentException("Non esiste un utente admin");
}


try {
    $stateTools = new OpenPAStateTools();
    if (!$options['quiet']) {
        $stateTools->setLog(true);
    }

    if ($options['id']) {
        $stateTools->changeState($options['id']);
    } elseif ($options['class']) {
        if ($options['class'] == '*') {
            $stateTools->changeAll();
        } else {
            $stateTools->changeByClassIdentifier($options['class']);
        }
    } elseif ($options['dump-rules']) {
        print_r($stateTools->getRules());
    }
} catch (Exception $e) {
    $cli->error($e->getMessage());
}
$script->shutdown();

