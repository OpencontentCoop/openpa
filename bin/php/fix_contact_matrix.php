<?php

require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array('description' => ("Importazione di strutture"),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true));

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators(true);


$classAttributes = eZContentClassAttribute::fetchObjectList(
    eZContentClassAttribute::definition(),
    null,
    ['identifier' => 'contacts', 'data_type_string' => eZMatrixType::DATA_TYPE_STRING]
);
$classAttributeIdList = [];
foreach ($classAttributes as $classAttribute) {
    $classAttributeIdList[] = $classAttribute->attribute('id');
}

$attributes = eZContentObjectAttribute::fetchObjectList(
    eZContentObjectAttribute::definition(),
    null,
    ['contentclassattribute_id' => [$classAttributeIdList], 'data_type_string' => eZMatrixType::DATA_TYPE_STRING]
);

foreach ($attributes as $attribute) {
    $xml = $attribute->attribute('data_text');
    $dom = new DOMDocument('1.0', 'utf-8');
    $success = $dom->loadXML($xml);
    if ($success) {
        $cellNodes = $dom->getElementsByTagName("c");
        $rowsNode = $dom->getElementsByTagName("rows")->item(0);
        $numRows = $rowsNode->getAttribute('number');
        $rowCount = intval($cellNodes->length / 2);
        if ($numRows != $rowCount && $rowCount > 0) {
            $cli->warning($attribute->attribute('id') . '#' . $attribute->attribute('version') . ' ' . $numRows . ' -> ' . $rowCount);
            eZLog::write($attribute->attribute('id') . '#' . $attribute->attribute('version') . ' ' . $numRows . ' -> ' . $rowCount, 'fix_contact_matrix.log');
            $rowsNode->setAttribute('number', $rowCount);
            $xml = $dom->saveXML();
            $matrix = new eZMatrix('');
            $matrix->decodeXML($xml);
            $attribute->setAttribute('data_text', $matrix->xmlString());
            $attribute->store();
        }
    }

}

$script->shutdown();