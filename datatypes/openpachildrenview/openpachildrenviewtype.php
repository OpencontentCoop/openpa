<?php

class OpenPAChildrenViewType extends eZDataType
{
    const DATA_TYPE_STRING = "openpachildrenview";

    private static $availableViews;

    function OpenPAChildrenViewType()
    {
        $this->eZDataType(
            self::DATA_TYPE_STRING,
            ezpI18n::tr('kernel/classes/datatypes', "Visualizzazione figli", 'Datatype name'),
            array('serialize_supported' => true)
        );
    }

    public static function getAvailableViews()
    {
        if (self::$availableViews === null){

            $availableViews = (array)eZINI::instance('openpachildrenview.ini')->variable('ChildrenView', 'AvailableViews');

            self::$availableViews = array();
            foreach( $availableViews as $index => $view )
            {
                $availableView = array(
                    'identifier' => $view,
                    'template' => self::getViewTemplatePath($view),
                    'name' => str_replace( '_', ' ', ucwords($view)),
                    'edit_template' => null,
                    'hide_menu' => false
                );

                if (eZINI::instance('openpachildrenview.ini')->hasGroup('ChildrenView_'.$view)){
                    $availableView = array_merge(
                        $availableView,
                        (array)eZINI::instance('openpachildrenview.ini')->group('ChildrenView_'.$view)
                    );
                }

                self::$availableViews[$view] = $availableView;
            }
        }
        return self::$availableViews;
    }

    public static function getViewTemplatePath($viewIdentifier)
    {
        return "design:parts/children/{$viewIdentifier}.tpl";
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $contentClassAttribute
     *
     * @return int
     */
    function validateClassAttributeHTTPInput($http, $base, $contentClassAttribute)
    {
        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentClassAttribute $classAttribute
     *
     * @return bool
     */
    function fetchClassAttributeHTTPInput($http, $base, $classAttribute)
    {
        $attributeContent = $this->classAttributeContent($classAttribute);
        $classAttributeID = $classAttribute->attribute('id');
        $isMultipleSelection = false;

        if ($http->hasPostVariable($base . "_openpachildrenview_ismultiple_value_" . $classAttributeID)) {
            if ($http->postVariable($base . "_openpachildrenview_ismultiple_value_" . $classAttributeID) != 0) {
                $isMultipleSelection = true;
            }
        }

        $currentOptions = $attributeContent['options'];
        $currentViews = $attributeContent['views'];
        $hasPostData = false;

        if ($http->hasPostVariable($base . "_openpachildrenview_activeoption_button_" . $classAttributeID)) {
            if ($http->hasPostVariable($base . "_openpachildrenview_option_active_array_" . $classAttributeID)) {
                $activeArray = $http->postVariable($base . "_openpachildrenview_option_active_array_" . $classAttributeID);
                $currentOptions = array();
                foreach($currentViews as $view){
                    foreach($activeArray as $id => $identifier){
                        if ($view['identifier'] == $identifier){
                            $currentOptions[] = array(
                                'id' => $id,
                                'name' => $view['name']
                            );
                        }
                    }
                }
                $hasPostData = true;
            }
        }

        if ($hasPostData) {

            // Serialize XML
            $doc = new DOMDocument('1.0', 'utf-8');
            $root = $doc->createElement("openpachildrenview");
            $doc->appendChild($root);

            $options = $doc->createElement("options");

            $root->appendChild($options);
            foreach ($currentOptions as $optionArray) {
                unset( $optionNode );
                $optionNode = $doc->createElement("option");
                $optionNode->setAttribute('id', $optionArray['id']);
                $optionNode->setAttribute('name', $optionArray['name']);

                $options->appendChild($optionNode);
            }

            $xml = $doc->saveXML();

            $classAttribute->setAttribute("data_text5", $xml);

            if ($isMultipleSelection == true) {
                $classAttribute->setAttribute("data_int1", 1);
            } else {
                $classAttribute->setAttribute("data_int1", 0);
            }
        }

        return true;
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return int
     */
    function validateObjectAttributeHTTPInput($http, $base, $contentObjectAttribute)
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();

        if ($http->hasPostVariable($base . '_openpachildrenview_selected_array_' . $contentObjectAttribute->attribute('id'))) {
            $data = $http->postVariable($base . '_openpachildrenview_selected_array_' . $contentObjectAttribute->attribute('id'));

            if ($data == "") {
                if (!$classAttribute->attribute('is_information_collector')
                    && $contentObjectAttribute->validateIsRequired()
                ) {
                    $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes',
                        'Input required.'));

                    return eZInputValidator::STATE_INVALID;
                }
            }
        } else if (!$classAttribute->attribute('is_information_collector') && $contentObjectAttribute->validateIsRequired()) {
            $contentObjectAttribute->setValidationError(ezpI18n::tr('kernel/classes/datatypes', 'Input required.'));

            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * @param eZHTTPTool $http
     * @param string $base
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    function fetchObjectAttributeHTTPInput($http, $base, $contentObjectAttribute)
    {
        if ($http->hasPostVariable($base . '_openpachildrenview_selected_array_' . $contentObjectAttribute->attribute('id'))) {

            $content = new OpenPAChildrenViewContent();

            $selectOptions = $http->postVariable($base . '_openpachildrenview_selected_array_' . $contentObjectAttribute->attribute('id'));

            $content->setSelectedOptions($selectOptions);

            if ($http->hasPostVariable($base . '_openpachildrenview_extra_' . $contentObjectAttribute->attribute('id'))) {
                $extraConfigs = $http->postVariable($base . '_openpachildrenview_extra_' . $contentObjectAttribute->attribute('id'));
                $content->setExtraConfigs($extraConfigs);
            }

            $contentObjectAttribute->setAttribute('data_text', (string)$content);

            return true;
        }

        return false;
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param int $currentVersion
     * @param eZContentObjectAttribute $originalContentObjectAttribute
     */
    function initializeObjectAttribute($contentObjectAttribute, $currentVersion, $originalContentObjectAttribute)
    {
        if ($currentVersion != false) {
            $idString = $originalContentObjectAttribute->attribute("data_text");
            $contentObjectAttribute->setAttribute("data_text", $idString);
            $contentObjectAttribute->store();
        }
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return OpenPAChildrenViewContent
     */
    function objectAttributeContent($contentObjectAttribute)
    {
        $content = OpenPAChildrenViewContent::instance($contentObjectAttribute);

        return $content;
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     *
     * @return array
     */
    function classAttributeContent($classAttribute)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $xmlString = $classAttribute->attribute('data_text5');
        $optionArray = array();
        $activeIdList = array();
        $maxId = 0;
        if ($xmlString != '') {
            $success = $dom->loadXML($xmlString);
            if ($success) {
                $options = $dom->getElementsByTagName('option');

                /** @var DOMElement $optionNode */
                foreach ($options as $optionNode) {
                    $optionArray[] = array(
                        'id' => $optionNode->getAttribute('id'),
                        'name' => $optionNode->getAttribute('name')
                    );
                    $activeIdList[] = $optionNode->getAttribute('id');
                    if ($maxId < $optionNode->getAttribute('id')){
                        $maxId = $optionNode->getAttribute('id');
                    }
                }
            }
        }

        $availableViews = array();
        foreach (self::getAvailableViews() as $view){

            foreach($optionArray as $option){
                if ($option['name'] == $view['name']){
                    $view['id'] = $option['id'];
                }
            }
            if (!isset($view['id'])){
                $maxId++;
                $view['id'] = $maxId;
            }
            $availableViews[] = $view;
        }

        $attrValue = array(
            'options' => $optionArray,
            'active_list' => $activeIdList,
            'is_multiselect' => $classAttribute->attribute('data_int1'),
            'views' => $availableViews
        );

        return $attrValue;
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return string
     */
    function metaData($contentObjectAttribute)
    {
        $selected = $this->objectAttributeContent($contentObjectAttribute)->getOptions();
        $classContent = $this->classAttributeContent($contentObjectAttribute->attribute('contentclass_attribute'));
        $return = '';
        if (count($selected) == 0) {
            return '';
        }

        $count = 0;
        $optionArray = $classContent['options'];
        foreach ($selected as $id) {
            if ($count++ != 0) {
                $return .= ' ';
            }
            foreach ($optionArray as $option) {
                $optionID = $option['id'];
                if ($optionID == $id) {
                    $return .= $option['name'];
                }
            }
        }

        return $return;
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return string
     */
    function toString($contentObjectAttribute)
    {
        $selected = $this->objectAttributeContent($contentObjectAttribute)->getOptions();
        $classContent = $this->classAttributeContent($contentObjectAttribute->attribute('contentclass_attribute'));

        if (count($selected)) {
            $returnData = array();
            $optionArray = $classContent['options'];
            foreach ($selected as $id) {
                foreach ($optionArray as $option) {
                    $optionID = $option['id'];
                    if ($optionID == $id) {
                        $returnData[] = $option['name'];
                    }
                }
            }

            return eZStringUtils::implodeStr($returnData, '|');
        }

        return '';
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string $string
     *
     * @return bool
     */
    function fromString($contentObjectAttribute, $string)
    {
        if ($string == '') {
            return true;
        }
        $selectedNames = eZStringUtils::explodeStr($string, '|');
        $selectedIDList = array();
        $classContent = $this->classAttributeContent($contentObjectAttribute->attribute('contentclass_attribute'));
        $optionArray = $classContent['options'];
        foreach ($selectedNames as $name) {
            foreach ($optionArray as $option) {
                $optionName = $option['name'];
                if ($optionName == $name) {
                    $selectedIDList[] = $option['id'];
                }
            }
        }

        $content = new OpenPAChildrenViewContent();
        $content->setSelectedOptions($selectedIDList);

        $contentObjectAttribute->setAttribute('data_text', (string)$content);

        return true;
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @param string|null $name
     *
     * @return string
     */
    function title($contentObjectAttribute, $name = null)
    {
        $selected = $this->objectAttributeContent($contentObjectAttribute)->getOptions();
        $classContent = $this->classAttributeContent($contentObjectAttribute->attribute('contentclass_attribute'));
        $return = '';
        if (count($selected)) {
            $selectedNames = array();
            foreach ($classContent['options'] as $option) {
                if (in_array($option['id'], $selected)) {
                    $selectedNames[] = $option['name'];
                }
            }
            $return = implode(', ', $selectedNames);
        }

        return $return;
    }

    /**
     * @param eZContentObjectAttribute $contentObjectAttribute
     *
     * @return bool
     */
    function hasObjectAttributeContent($contentObjectAttribute)
    {
        /** @var OpenPAChildrenViewContent $selected */
        $selected = $this->objectAttributeContent($contentObjectAttribute)->getOptions();

        return count($selected) > 0;
    }

    function isIndexable()
    {
        return true;
    }

    function isInformationCollector()
    {
        return false;
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     * @param DOMElement $attributeNode
     * @param DOMDocument $attributeParametersNode
     */
    function serializeContentClassAttribute($classAttribute, $attributeNode, $attributeParametersNode)
    {
        $isMultipleSelection = $classAttribute->attribute('data_int1');
        $xmlString = $classAttribute->attribute('data_text5');

        $selectionDom = new DOMDocument('1.0', 'utf-8');
        $success = $selectionDom->loadXML($xmlString);
        $domRoot = $selectionDom->documentElement;
        $options = $domRoot->getElementsByTagName('options')->item(0);

        $dom = $attributeParametersNode->ownerDocument;

        $importedOptionsNode = $dom->importNode($options, true);
        $attributeParametersNode->appendChild($importedOptionsNode);
        $isMultiSelectNode = $dom->createElement('is-multiselect');
        $isMultiSelectNode->appendChild($dom->createTextNode($isMultipleSelection));
        $attributeParametersNode->appendChild($isMultiSelectNode);
    }

    /**
     * @param eZContentClassAttribute $classAttribute
     * @param DOMElement $attributeNode
     * @param DOMDocument $attributeParametersNode
     */
    function unserializeContentClassAttribute($classAttribute, $attributeNode, $attributeParametersNode)
    {
        $options = $attributeParametersNode->getElementsByTagName('options')->item(0);

        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement('openpachildrenview');
        $doc->appendChild($root);

        $importedOptions = $doc->importNode($options, true);
        $root->appendChild($importedOptions);

        $xml = $doc->saveXML();
        $classAttribute->setAttribute('data_text5', $xml);

        if ($attributeParametersNode->getElementsByTagName('is-multiselect')->item(0)->textContent == 0) {
            $classAttribute->setAttribute('data_int1', 0);
        } else {
            $classAttribute->setAttribute('data_int1', 1);
        }
    }

    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }
}

eZDataType::register(OpenPAChildrenViewType::DATA_TYPE_STRING, "OpenPAChildrenViewType");
