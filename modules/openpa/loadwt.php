<?php

$currentNodeId = $Params['CurrentNodeId'];
$data = '';
if($currentNodeId && eZUser::currentUser()->isRegistered()){
    $tpl = eZTemplate::factory();
    $tpl->setVariable('current_node_id', $currentNodeId);
    $tpl->setVariable('current_user', eZUser::currentUser());
    $data = $tpl->fetch('design:parts/website_toolbar.tpl');
}

echo $data;

if (isset($_GET['debug'])){
    eZDisplayDebug();
}
eZExecution::cleanExit();
