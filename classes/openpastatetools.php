<?php

class OpenPAStateTools
{
    const REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER = 'change_register';

    /**
     * array
     */
    private $rules = array();


    /**
     * array
     */
    private $ruleDefinitions = array();

    /**
     * array
     */
    private $ruleApplications = array();


    /**
     * bool
     */
    private $log;

    /**
     * @var eZContentObject
     */
    private $currentObject;

    private static $isLoaded;

    private $wait = 0;

    private $messages = array();

    public function __construct()
    {
        $this->loadValidRules();
        $this->log = false;
    }

    public function changeAll()
    {
        $classIdentifiers = array_keys($this->rules);
        foreach ($classIdentifiers as $classIdentifier) {
            $this->changeByClassIdentifier($classIdentifier);
        }
    }

    public function changeByClassIdentifier($classIdentifier)
    {
        if ($this->log) {
            $this->output("ChangeState for class: {$classIdentifier}");
            if ($this->wait > 0) {
                $this->output("Sleeping {$this->wait} seconds");
            }
        }

        if (isset($this->rules[$classIdentifier])) {
            /** @var eZContentObjectTreeNode[] $nodeArray */
            $nodeArray = eZContentObjectTreeNode::subTreeByNodeID(array(
                'ClassFilterType' => 'include',
                'ClassFilterArray' => array($classIdentifier),
                'LoadDataMap' => false,
                'Limitation' => array()
            ), 1
            );

            $count = count($nodeArray);
            if ($count > 0) {
                $index = 0;
                foreach ($nodeArray as $currentNode) {
                    $index++;
                    if ($this->log) $this->output("$index/$count ", false);
                    $this->changeState($currentNode->object());
                    if ($this->log) $this->output();
                    if ($this->wait > 0) {
                        sleep($this->wait);
                    }
                }

                if ($this->log) {
                    $memoryMax = memory_get_peak_usage(); // Result is in bytes
                    $memoryMax = round($memoryMax / 1024 / 1024, 2); // Convert in Megabytes
                    $this->output('Memory: ' . $memoryMax . 'M');
                    $this->output();
                }
            } else {
                $this->output("Nessun contenuto");
                $this->output();
            }
        } elseif ($this->log) {
            $this->error("No rules found for class {$classIdentifier}");
        }
    }

    /**
     * @param int|eZContentObject $currentObject
     */
    public function changeState($currentObject)
    {
        if (is_numeric($currentObject)) {
            $currentObject = eZContentObject::fetch($currentObject);
        }
        if ($currentObject instanceof eZContentObject) {
            $this->currentObject = $currentObject;
            $this->changeCurrentObjectState();
        }
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function getRuleDefinitions()
    {
        return $this->ruleDefinitions;
    }

    /**
     * @return array
     */
    public function getRuleApplications()
    {
        return $this->ruleApplications;
    }

    /**
     * @param bool $bool
     */
    public function setLog($bool)
    {
        $this->log = $bool;
    }

    public function setWait($int)
    {
        $this->wait = (int)$int;
    }

    public function setRuleDefinition($definitionIdentifier, $definition)
    {
        $this->ruleDefinitions[$definitionIdentifier] = $definition;
    }

    public function removeRuleDefinition($definitionIdentifier)
    {
        unset($this->ruleDefinitions[$definitionIdentifier]);
    }

    public function setRuleApplication($ruleClassIdentifier, $ruleList)
    {
        $this->ruleApplications[$ruleClassIdentifier] = $ruleList;
    }

    public function removeRuleApplication($ruleClassIdentifier)
    {
        unset($this->ruleApplications[$ruleClassIdentifier]);
    }

    public function store($data = false)
    {
        if (!$data) {
            $data = array(
                'ruleDefinitions' => $this->ruleDefinitions,
                'ruleApplications' => $this->getRuleApplications(),
            );
        }
        $siteData = eZSiteData::fetchByName('changestatesettings');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changestatesettings',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', json_encode($data));
        $siteData->store();

        self::$isLoaded = null;
        $this->loadValidRules();
    }

    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $ruleSettings
     * @throws Exception
     */
    public static function validateRuleDefinition($ruleSettings)
    {
        self::getState($ruleSettings['CurrentState']);
        self::getState($ruleSettings['DestinationState']);
        if (!empty((array)$ruleSettings['Conditions'])) {
            foreach ($ruleSettings['Conditions'] as $conditionSetting) {
                self::parseCondition($conditionSetting);
            }
        } else {
            throw new Exception('Condizioni non trovate');
        }

    }

    /**
     * @param array $ruleSettings
     * @param string $classIdentifier
     * @throws Exception
     */
    public static function validateRuleApplication($ruleSettings, $classIdentifier)
    {
        $class = eZContentClass::fetchByIdentifier($classIdentifier);
        if (!$class instanceof eZContentClass) {
            throw new Exception("Classe $classIdentifier non trovata");
        }

        $dataMap = $class->attribute('data_map');

        self::validateRuleDefinition($ruleSettings);

        foreach ($ruleSettings['Conditions'] as $conditionSetting) {
            $condition = self::parseCondition($conditionSetting);
            $attribute = $condition['attribute'];
            if (!isset($dataMap[$attribute])) {
                throw new Exception("Attributo $attribute non trovato per la classe $classIdentifier");
            }
        }
    }

    public static function restoreRulesBackup()
    {
        $backupData = eZSiteData::fetchByName('changestatesettings_backup');
        if ($backupData instanceof eZSiteData) {
            $data = $backupData->attribute('value');

            $siteData = eZSiteData::fetchByName('changestatesettings');
            if (!$siteData instanceof eZSiteData) {
                $siteData = new eZSiteData([
                    'name' => 'changestatesettings',
                    'value' => ''
                ]);
            }
            $siteData->setAttribute('value', $data);
            $siteData->store();

            $backupData->remove();
        }
    }

    public static function hasRulesBackup()
    {
        return eZSiteData::fetchByName('changestatesettings_backup') instanceof eZSiteData;
    }

    public static function storeRulesBackup()
    {
        $data = '';
        $currentData = eZSiteData::fetchByName('changestatesettings');
        if ($currentData instanceof eZSiteData) {
            $data = $currentData->attribute('value');
        }

        $siteData = eZSiteData::fetchByName('changestatesettings_backup');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changestatesettings_backup',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', $data);
        $siteData->store();
    }

    public static function resetRulesFromBackendIni()
    {
        $siteData = eZSiteData::fetchByName('changestatesettings');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changestatesettings',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', json_encode(self::getRulesFromBackendIni()));
        $siteData->store();
    }

    /**
     * @return array
     */
    private static function getRulesFromBackendIni()
    {
        $_ruleDefinitions = $_rules = array();

        $backendSiteAccess = OpenPABase::getBackendSiteaccessName();
        $backendOpenpaIni = eZSiteAccess::getIni($backendSiteAccess, 'openpa.ini');

        $rules = $backendOpenpaIni->variable('ChangeState', 'Rules');

        foreach ($rules as $rule) {

            @list($classIdentifier, $ruleIdentifiers) = explode('|', $rule);

            $ruleIdentifiers = explode(',', $ruleIdentifiers);

            foreach ($ruleIdentifiers as $ruleIdentifier) {

                if ($backendOpenpaIni->hasGroup('ChangeStateRule-' . $ruleIdentifier)) {
                    $ruleSettings = $backendOpenpaIni->group('ChangeStateRule-' . $ruleIdentifier);

                    $_ruleDefinitions[$ruleIdentifier] = $ruleSettings;

                    if (is_array($ruleSettings)) {
                        try {
                            self::validateRuleApplication($ruleSettings, $classIdentifier);
                            if (!isset($_rules[$classIdentifier])) {
                                $_rules[$classIdentifier] = array();
                            }
                            $_rules[$classIdentifier][] = $ruleIdentifier;

                        } catch (Exception $e) {
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }
                    }
                }
            }
        }

        return array(
            'ruleDefinitions' => $_ruleDefinitions,
            'ruleApplications' => $_rules,
        );
    }

    private function loadValidRules()
    {
        if (self::$isLoaded === null) {

            $siteData = eZSiteData::fetchByName('changestatesettings');
            if (!$siteData instanceof eZSiteData) {
                $siteData = new eZSiteData([
                    'name' => 'changestatesettings',
                    'value' => json_encode(self::getRulesFromBackendIni())
                ]);
                $siteData->store();
            }

            $siteDataValue = json_decode($siteData->attribute('value'), true);

            $this->ruleDefinitions = $siteDataValue['ruleDefinitions'];

            $rules = $siteDataValue['ruleApplications'];

            foreach ($rules as $classIdentifier => $ruleSettings) {
                foreach ($ruleSettings as $ruleIdentifier) {
                    if (isset($this->ruleDefinitions[$ruleIdentifier])) {
                        try {
                            self::validateRuleApplication($this->ruleDefinitions[$ruleIdentifier], $classIdentifier);
                            $this->rules[$classIdentifier][$ruleIdentifier] = $this->ruleDefinitions[$ruleIdentifier];
                            $this->ruleApplications[$classIdentifier][] = $ruleIdentifier;
                        } catch (Exception $e) {
                            eZDebug::writeError($e->getMessage(), __METHOD__);
                        }

                    }
                }
            }

            self::$isLoaded = true;
        }
    }

    /**
     * @param $identifier
     * @return bool|eZContentObjectState|null
     * @throws Exception
     */
    private static function getState($identifier)
    {
        @list($groupIdentifier, $stateIdentifier) = explode('.', $identifier);

        $stateObject = null;
        $stateGroup = eZContentObjectStateGroup::fetchByIdentifier($groupIdentifier);
        if ($stateGroup instanceof eZContentObjectStateGroup) {
            $stateObject = $stateGroup->stateByIdentifier($stateIdentifier);
        }

        if (!$stateObject instanceof eZContentObjectState) {
            throw new Exception("Stato $identifier non trovato");
        }

        return $stateObject;
    }

    private function changeCurrentObjectState()
    {
        if ($this->log) $this->notice("{$this->currentObject->attribute( 'class_identifier' )} Oggetto #{$this->currentObject->attribute( 'id' )} - {$this->currentObject->attribute( 'name' )}");
        if (isset($this->rules[$this->currentObject->attribute('class_identifier')])) {
            foreach ($this->rules[$this->currentObject->attribute('class_identifier')] as $ruleIdentifier => $ruleSettings) {
                $this->runCurrentChangeObjectStateRule($ruleIdentifier, $ruleSettings);
            }
        }
    }

    private function runCurrentChangeObjectStateRule($ruleIdentifier, $ruleSettings)
    {
        // stato corrente
        $isInRuleCurrentState = true;
        $currentStateIdentifier = $ruleSettings['CurrentState'];
        if ($this->log) $this->notice("[$ruleIdentifier]");
        try {
            $currentState = self::getState($currentStateIdentifier);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            if ($this->log) $this->error(" - " . $e->getMessage());
            return false;
        }
        if (!in_array($currentState->attribute('id'), $this->currentObject->attribute('state_id_array'))) {
            $realCurrentStateIdentifiers = implode(', ', $this->currentObject->attribute('state_identifier_array'));
            if ($this->log) $this->notice(" - L'oggetto non è in stato corrente $currentStateIdentifier ma in $realCurrentStateIdentifiers");
            //$isInRuleCurrentState = false;
            return false;
        }

        // stato futuro
        $isInRuleDestinationState = false;
        $destinationStateIdentifier = $ruleSettings['DestinationState'];
        try {
            $destinationState = self::getState($destinationStateIdentifier);
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            if ($this->log) $this->error(" - " . $e->getMessage());
            return false;
        }
        if (in_array($destinationState->attribute('id'), $this->currentObject->attribute('state_id_array'))) {
            if ($this->log) $this->notice(" - L'oggetto è già in stato destinazione $destinationStateIdentifier");
            //$isInRuleDestinationState = true;
            return false;
        }

        // condizioni
        $passValidations = true;
        foreach ($ruleSettings['Conditions'] as $condition) {
            if ($this->log) $this->notice(" - $condition ", false);
            $pass = $this->verifyConditionForCurrentObject($condition);
            if ($this->log) $this->notice('Condizione: ' . var_export($pass, 1));
            if (!$pass) {
                $passValidations = false;
            }
        }

        if (!$passValidations) {
            return false;
        }

        $toState = null;
        if ($passValidations && $isInRuleCurrentState && !$isInRuleDestinationState) {
            $toState = $destinationState;
        } /*elseif (!$passValidations && !$isInRuleCurrentState && $isInRuleDestinationState) {
            $toState = $currentState;
        }*/

        if ($toState instanceof eZContentObjectState) {
            $this->currentObject->assignState($toState);
            $relatedChanges = $this->changeCurrentObjectRelatedFilesState($toState);
            $this->registerChangeState($toState, $ruleIdentifier, $relatedChanges);
            $this->flushCurrentObject();
            if ($this->log) $this->warning(" - Aggiornamento stato a {$toState->attribute('identifier')}");
            return true;
        }
        return false;
    }

    private function changeCurrentObjectRelatedFilesState(eZContentObjectState $state)
    {
        $relatedChanges = [];
        $dataMap = $this->currentObject->dataMap();
        foreach ($dataMap as $attribute){
            if (in_array($attribute->attribute('data_type_string'), [eZObjectRelationListType::DATA_TYPE_STRING, eZObjectRelationType::DATA_TYPE_STRING])){
                $idList = explode('-', $attribute->toString());
                $objectList = OpenPABase::fetchObjects($idList);
                foreach ($objectList as $object){
                    if (in_array($object->attribute('class_identifier'), ['file', 'file_pdf'])){
                        $object->assignState($state);
                        $this->flushObject($object);
                        if ($this->log) $this->warning(" - Aggiornamento stato a {$state->attribute('identifier')} per oggetto correlato #" . $object->attribute('id'));
                        $relatedChanges[] = $object->attribute('id');
                    }
                }
            }
        }

        return $relatedChanges;
    }

    private function registerChangeState(eZContentObjectState $toState, $ruleIdentifier, $relatedChanges = array())
    {
        $message = 'Object #' . $this->currentObject->attribute('id') . ' - Rule #' . $ruleIdentifier . ' - State #' . $toState->attribute('id');
        if (count($relatedChanges)){
            $message .= ' - Related objects' . implode('-', $relatedChanges);
        }
        eZLog::write($message, 'change_state.log', eZSys::varDirectory() . '/log');

        $dataMap = $this->currentObject->dataMap();
        if (isset($dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER])) {
            $value = $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->toString();
            $time = strftime("%b %d %Y %H:%M:%S", strtotime("now"));
            $message = "[$ruleIdentifier] Cambio stato in #" . $toState->attribute('identifier');
            if (count($relatedChanges)){
                $message .= ' (oggetti correlati' . implode('-', $relatedChanges) . ')';
            }
            $value .= "[$time][change_state] $message \n";
            $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->fromString($value);
            $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->store();
        }
    }

    private function flushCurrentObject()
    {
        $this->flushObject($this->currentObject);
    }

    private function flushObject(eZContentObject $object)
    {
        eZContentObject::clearCache(array($object->attribute('id')));
        $object = eZContentObject::fetch($object->attribute('id'));
        eZContentOperationCollection::registerSearchObject($object->attribute('id'));
        eZContentCacheManager::clearContentCacheIfNeeded($object->attribute('id'));
    }


    private function verifyConditionForCurrentObject($conditionSetting)
    {
        try {
            $parameters = self::parseCondition($conditionSetting);
            $attribute = $parameters['attribute'];
            $operator = $parameters['operator'];
            $value = trim($parameters['value']);

            if (!is_string($value) || $value == '') {
                return false;
            }

            /** @var eZContentObjectAttribute[] $dataMap */
            $dataMap = $this->currentObject->attribute('data_map');
            if (isset($dataMap[$attribute]) && $dataMap[$attribute]->toString() !== '') {
                $string = $dataMap[$attribute]->toString();
                if ($dataMap[$attribute]->attribute('data_type_string') == 'ezdate') {
                    $string = mktime(23, 59, 59, date("n", $string), date("j", $string), date("Y", $string));
                }
                return $this->compare($string, $operator, $value);
            }
        } catch (Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

        return false;
    }

    private function compare($string, $operator, $value)
    {

        $now = time();

        if ($value == 'NOW') {
            $value = $now;
        } elseif ($value == 'TODAY') {
            $value = mktime(23, 59, 59, date("n", $now), date("j", $now), date("Y", $now));
        }

        if ($this->log) $this->notice(" ($string $operator $value) ", false);

        switch ($operator) {
            case 'eq':
                return $string == $value;
                break;

            case 'gt':
                return $string > $value;
                break;

            case 'ge':
                return $string >= $value;
                break;

            case 'lt':
                return $string < $value;
                break;

            case 'le':
                return $string <= $value;
                break;
        }

        return false;
    }

    private static function parseCondition($conditionSetting)
    {
        list($attributeIdentifier, $operator, $string) = explode(';', $conditionSetting);
        if (!isset($attributeIdentifier, $operator, $string)) {
            throw new Exception("Parametri non sufficienti in $conditionSetting");
        }

        if (!in_array($operator, array('eq', 'gt', 'ge', 'lt', 'le'))) {
            throw new Exception("Operatore $operator non riconosciuto");
        }

        return array(
            'attribute' => $attributeIdentifier,
            'operator' => $operator,
            'value' => $string
        );
    }

    private function addMessage($string = false, $addEOL = true)
    {
        static $key = 0;
        if($key == 0)
            $this->messages[$key] = $string;
        else
            $this->messages[$key] .= $string;
        if ($addEOL) $key++;
    }

    private function output($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if ( php_sapi_name() == 'cli' )
            eZCLI::instance()->output($string, $addEOL);
    }

    private function notice($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if ( php_sapi_name() == 'cli' )
            eZCLI::instance()->notice($string, $addEOL);
    }

    private function warning($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if ( php_sapi_name() == 'cli' )
            eZCLI::instance()->warning($string, $addEOL);
    }

    private function error($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if ( php_sapi_name() == 'cli' )
            eZCLI::instance()->error($string, $addEOL);
    }
}
