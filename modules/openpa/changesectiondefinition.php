<?php

$sectionTools = new OpenPASectionTools();

header('Content-Type: application/json');
$data = $sectionTools->getSettings();
echo json_encode($data);
eZExecution::cleanExit();