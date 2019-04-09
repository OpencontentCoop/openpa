<?php

$stateTools = new OpenPAStateTools();
$rules = $stateTools->getRuleApplications();
$ruleDefinitions = $stateTools->getRuleDefinitions();


header('Content-Type: application/json');
$data = array(
    'ruleDefinitions' => $ruleDefinitions,
    'ruleApplications' => $rules,
);
echo json_encode($data);
eZExecution::cleanExit();