<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$id = $Params['ID'];

$stateTools = new OpenPAStateTools();
$rules = $stateTools->getRuleApplications();
$ruleDefinitions = $stateTools->getRuleDefinitions();

$messages = array();
$isAddDefinition = false;
$isAddRule = false;
$isEdit = false;
$currentEditDefinition = false;
$currentEditRule = false;
$error = false;

if (is_numeric($id) && (int)$id > 0) {
    $object = eZContentObject::fetch((int)$id);
    if ($object instanceof eZContentObject) {
        $stateTools->setLog(true);
        $stateTools->changeState($object);
        $messages = $stateTools->getMessages();
    }
} elseif ($id == 'sync' && $http->hasGetVariable('remote')) {
    $remoteRequest = $http->variable('remote');
    $remoteRequestUrl = rtrim($remoteRequest, '/') . '/openpa/changestatedefinition';
    $remoteData = eZHTTPTool::getDataByURL($remoteRequestUrl);
    if (!empty($remoteData)) {
        OpenPAStateTools::storeRulesBackup();
        $stateTools->store(json_decode($remoteData, true));
        $module->redirectTo('openpa/changestatesettings');
        return;
    } else {
        $error = "Dati remoti non trovati in $remoteRequestUrl";
    }
}

if ($http->hasPostVariable('Abort')) {
    $module->redirectTo('openpa/changestatesettings');
    return;
}

if ($http->hasPostVariable('EditDefinition') || $http->hasPostVariable('EditRule')) {
    $isEdit = true;
    if ($http->hasPostVariable('EditDefinition')) {
        $currentEditDefinition = $http->postVariable('EditDefinition');
    } elseif ($http->hasPostVariable('EditRule')) {
        $currentEditRule = $http->postVariable('EditRule');
    }
}

if ($http->hasPostVariable('ResetRules')) {
    OpenPAStateTools::resetRulesFromBackendIni();
    $module->redirectTo('openpa/changestatesettings');
    return;
}

if ($http->hasPostVariable('RestoreRules')) {
    OpenPAStateTools::restoreRulesBackup();
    $module->redirectTo('openpa/changestatesettings');
    return;
}

if ($http->hasPostVariable('StoreDefinition')) {

    $definition = array(
        'CurrentState' => trim($http->postVariable('CurrentState')),
        'DestinationState' => trim($http->postVariable('DestinationState')),
        'Conditions' => array_map('trim', explode("\n", $http->postVariable('Conditions')))
    );

    $definitionIdentifier = eZCharTransform::instance()->transformByGroup($http->postVariable('Identifier'), 'identifier');

    try {
        OpenPAStateTools::validateRuleDefinition($definition);
        OpenPAStateTools::storeRulesBackup();

        $stateTools->setRuleDefinition($definitionIdentifier, $definition);
        $stateTools->store();

        $module->redirectTo('openpa/changestatesettings');
        return;

    } catch (Exception $e) {
        $error = $e->getMessage();

        $ruleDefinitions[$definitionIdentifier] = $definition;
        $isEdit = true;
        $isAddDefinition = true;
        $isAddRule = false;
        $currentEditDefinition = $definitionIdentifier;
        $currentEditRule = false;
    }

} elseif ($http->hasPostVariable('StoreRule')) {

    $ruleList = array_map('trim', explode("\n", $http->postVariable('RuleList')));
    $ruleClassIdentifier = trim($http->postVariable('ClassIdentifier'));

    try {
        if (empty($ruleList)) {
            throw new Exception("Regole non trovate");
        }

        foreach ($ruleList as $rule) {
            if (!isset($ruleDefinitions[$rule])) {
                throw new Exception("Regola $rule non trovata");
            }
            OpenPAStateTools::validateRuleApplication($ruleDefinitions[$rule], $ruleClassIdentifier);
        }
        OpenPAStateTools::storeRulesBackup();
        $stateTools->setRuleApplication($ruleClassIdentifier, $ruleList);
        $stateTools->store();

        $module->redirectTo('openpa/changestatesettings');
        return;

    } catch (Exception $e) {
        $error = $e->getMessage();

        $rules[$ruleClassIdentifier] = $ruleList;
        $isEdit = true;
        $isAddDefinition = false;
        $isAddRule = true;
        $currentEditDefinition = false;
        $currentEditRule = $ruleClassIdentifier;
    }
}

if ($http->hasPostVariable('RemoveDefinition')) {
    $definitionIdentifier = trim($http->postVariable('RemoveDefinition'));

    OpenPAStateTools::storeRulesBackup();
    $stateTools->removeRuleDefinition($definitionIdentifier);
    $stateTools->store();

    $module->redirectTo('openpa/changestatesettings');
    return;

} elseif ($http->hasPostVariable('RemoveRule')) {
    $ruleClassIdentifier = trim($http->postVariable('RemoveRule'));

    OpenPAStateTools::storeRulesBackup();
    $stateTools->removeRuleApplication($ruleClassIdentifier);
    $stateTools->store();

    $module->redirectTo('openpa/changestatesettings');
    return;
}

if ($http->hasPostVariable('AddDefinition')) {
    $ruleDefinitions['NewDefinition'] = array(
        'CurrentState' => '',
        'DestinationState' => '',
        'Conditions' => array(),
    );
    $isEdit = true;
    $isAddDefinition = true;
    $isAddRule = false;
    $currentEditDefinition = 'NewDefinition';
    $currentEditRule = false;

} elseif ($http->hasPostVariable('AddRule')) {
    $rules['NewRule'] = array();
    $isEdit = true;
    $isAddDefinition = false;
    $isAddRule = true;
    $currentEditDefinition = false;
    $currentEditRule = 'NewRule';
}

$tpl->setVariable('messages', $messages);
$tpl->setVariable('error', $error);
$tpl->setVariable('is_edit', $isEdit);
$tpl->setVariable('is_add_definition', $isAddDefinition);
$tpl->setVariable('is_add_rule', $isAddRule);
$tpl->setVariable('current_edit_definition', $currentEditDefinition);
$tpl->setVariable('current_edit_rule', $currentEditRule);
$tpl->setVariable('has_backup', OpenPAStateTools::hasRulesBackup());

$tpl->setVariable('definitions', $ruleDefinitions);
$tpl->setVariable('rules', $rules);

$pageTitle = eZINI::instance('menu.ini')->variable('Leftmenu_setup', 'LinkNames')['openpa_changestatesettings'];
$tpl->setVariable('page_title', $pageTitle);

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/changestatesettings.tpl');
$Result['path'] = array(array('text' => $pageTitle, 'url' => false));

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = array(
    'show_path' => false
);
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $tpl->variable('persistent_variable'));
}
$Result['content_info'] = $contentInfoArray;