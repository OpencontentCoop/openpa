<?php

/** @var eZModule $module */
$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();
$id = $Params['ID'];

$sectionTools = new OpenPASectionTools();
$settings = $sectionTools->getSettings();
$classes = array_keys($settings['rootNodeIdList']);
sort($classes);

$messages = array();
$error = false;
$isEdit = false;
$currentEditClass = false;
$isAddSettings = false;

if (is_numeric($id) && (int)$id > 0) {
    $node = eZContentObjectTreeNode::fetch((int)$id);
    if ($node instanceof eZContentObjectTreeNode) {
        try {
            $sectionTools->setLog(true);
            $sectionTools->test($node);
            $result = $sectionTools->changeSection($node);
            $messages = $sectionTools->getMessages();
            if ($result) {
                $messages[] = "Cambio sezione effettuato";
            } else {
                $messages[] = "Cambio sezione NON effettuato";
            }
        }catch (Exception $e){
            $error = $e->getMessage();
        }
    }
} elseif ($id == 'sync' && $http->hasGetVariable('remote')) {
    $remoteRequest = $http->variable('remote');
    $remoteRequestUrl = rtrim($remoteRequest, '/') . '/openpa/changesectiondefinition';
    $remoteData = eZHTTPTool::getDataByURL($remoteRequestUrl);
    if (!empty($remoteData)) {
        OpenPASectionTools::storeBackup();
        $sectionTools->store(json_decode($remoteData, true));
        $module->redirectTo('openpa/changesectionsettings');
        return;
    } else {
        $error = "Dati remoti non trovati in $remoteRequestUrl";
    }
}

if ($http->hasPostVariable('Abort')) {
    $module->redirectTo('openpa/changesectionsettings');
    return;
}

if ($http->hasPostVariable('EditSetting')) {
    $isEdit = true;
    if ($http->hasPostVariable('EditSetting')) {
        $currentEditClass = $http->postVariable('EditSetting');
    }
}

if ($http->hasPostVariable('ResetRules')) {
    OpenPASectionTools::resetRulesFromBackendIni();
    $module->redirectTo('openpa/changesectionsettings');
    return;
}

if ($http->hasPostVariable('RestoreRules')) {
    OpenPASectionTools::restoreBackup();
    $module->redirectTo('openpa/changesectionsettings');
    return;
}

if ($http->hasPostVariable('StoreSetting')) {

    $classIdentifier = trim($http->postVariable('classIdentifier'));
    $rootNodeId = trim($http->postVariable('rootNodeId'));
    $dataTimeAttributeIdentifier = trim($http->postVariable('dataTimeAttributeIdentifier'));
    $sectionId = trim($http->postVariable('sectionId'));
    $secondsExpire = trim($http->postVariable('secondsExpire'));
    $overrideValue = trim($http->postVariable('overrideValue'));
    $ignore = trim($http->postVariable('ignore'));

    try {
        OpenPASectionTools::validateSetting($classIdentifier, $rootNodeId, $dataTimeAttributeIdentifier, $sectionId, $secondsExpire, $overrideValue, $ignore);
        OpenPASectionTools::storeBackup();

        $sectionTools->setSetting($classIdentifier, $rootNodeId, $dataTimeAttributeIdentifier, $sectionId, $secondsExpire, $overrideValue, $ignore);
        $sectionTools->store();

        $module->redirectTo('openpa/changesectionsettings');
        return;

    } catch (Exception $e) {
        $error = $e->getMessage();
        $isEdit = true;
        $currentEditClass = $classIdentifier;
        $isAddSettings = $http->hasPostVariable('new');
        if (!in_array($classIdentifier, $classes))
            $classes[] = $classIdentifier;
        $settings['rootNodeIdList'][$classIdentifier] = $rootNodeId;
        $settings['dataTimeAttributeIdentifierList'][$classIdentifier] = $dataTimeAttributeIdentifier;
        $settings['sectionIdList'][$classIdentifier] = $sectionId;
        $settings['secondsExpire'][$classIdentifier] = $secondsExpire;
        $settings['overrideValue'][$classIdentifier] = $overrideValue;
        $settings['ignore'][$classIdentifier] = $ignore;
    }
}

if ($http->hasPostVariable('RemoveSetting')) {
    OpenPASectionTools::storeBackup();

    $classIdentifier = trim($http->postVariable('RemoveSetting'));
    $sectionTools->removeSetting($classIdentifier);
    $sectionTools->store();

    $module->redirectTo('openpa/changesectionsettings');
    return;
}


if ($http->hasPostVariable('AddSetting')) {
    $classes[] = 'New';
    $isEdit = true;
    $isAddSettings = true;
    $currentEditClass = 'New';
}

$tpl->setVariable('messages', $messages);
$tpl->setVariable('error', $error);
$tpl->setVariable('has_backup', OpenPASectionTools::hasRulesBackup());

$tpl->setVariable('settings', $settings);
$tpl->setVariable('classes', $classes);
$tpl->setVariable('default_section', $sectionTools->getDefaultSectionId());
$tpl->setVariable('default_expire', $sectionTools->getDefaultSecondExpire());
$tpl->setVariable('is_edit', $isEdit);
$tpl->setVariable('is_add_settings', $isAddSettings);
$tpl->setVariable('current_edit_class', $currentEditClass);


$pageTitle = eZINI::instance('menu.ini')->variable('Leftmenu_setup', 'LinkNames')['openpa_changesectionsettings'];
$tpl->setVariable('page_title', $pageTitle);

$Result = array();
$Result['content'] = $tpl->fetch('design:openpa/changesectionsettings.tpl');
$Result['path'] = array(array('text' => $pageTitle, 'url' => false));

$contentInfoArray = array();
$contentInfoArray['persistent_variable'] = array(
    'show_path' => false
);
if (is_array($tpl->variable('persistent_variable'))) {
    $contentInfoArray['persistent_variable'] = array_merge($contentInfoArray['persistent_variable'], $tpl->variable('persistent_variable'));
}
$Result['content_info'] = $contentInfoArray;