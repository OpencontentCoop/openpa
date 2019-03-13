<?php


class OpenPASectionTools
{
    const REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER = 'change_register';

    /**
     * @var int[]
     */
    protected $rootNodeIdList;

    /**
     * @var string[]  class identifier => attribute identifier
     */
    protected $dataTimeAttributeIdentifierList;

    /**
     * array int[]  class identifier => section id
     */
    protected $sectionIdList;

    /**
     * int section id
     */
    protected $defaultSectionId;

    /**
     * int[]  class identifier => seconds
     */
    protected $secondsExpire;

    /**
     * bool|array class => maggiore|minore
     */
    protected $overrideValue;

    /**
     * array bool| array class => attributo|valore
     */
    protected $ignore;

    /**
     * int seconds
     */
    protected $defaultSecondExpire;

    /**
     * int
     */
    protected $now;

    /**
     * @var eZContentObjectTreeNode
     */
    protected $currentRootNode;

    /**
     * string
     */
    protected $currentClassIdentifier;

    protected $currentOverrideValue;

    protected $currentIgnore;

    protected $currentSecondsExpire;

    /**
     * @var eZContentObjectAttribute
     */
    protected $currentUnPublishDateAttribute;

    protected $currentSectionDestinationId;

    /**
     * bool
     */
    protected $log;

    /**
     * @var eZCLI
     */
    protected $cli;

    protected static $changeNodeIds = array();

    private static $isLoaded;

    private $messages = array();

    public function __construct()
    {
        $this->loadSettings();

        $this->defaultSecondExpire = OpenPAINI::variable('ChangeSection', 'ScadeDopoTotSecondiDefault', 0);
        $this->defaultSectionId = OpenPAINI::variable('ChangeSection', 'ToSectionDefault', 0);

        $this->now = time();
        $this->log = false;
        $this->cli = eZCLI::instance();
    }

    private function loadSettings()
    {
        if (self::$isLoaded === null) {

            $siteData = eZSiteData::fetchByName('changesectionsettings');
            if (!$siteData instanceof eZSiteData) {
                $siteData = new eZSiteData([
                    'name' => 'changesectionsettings',
                    'value' => json_encode(self::getRulesFromBackendIni())
                ]);
                $siteData->store();
            }

            $siteDataValue = json_decode($siteData->attribute('value'), true);

            $this->rootNodeIdList = isset($siteDataValue['rootNodeIdList']) ? $siteDataValue['rootNodeIdList'] : array();
            $this->dataTimeAttributeIdentifierList = isset($siteDataValue['dataTimeAttributeIdentifierList']) ? $siteDataValue['dataTimeAttributeIdentifierList'] : array();
            $this->sectionIdList = isset($siteDataValue['sectionIdList']) ? $siteDataValue['sectionIdList'] : array();
            $this->secondsExpire = isset($siteDataValue['secondsExpire']) ? $siteDataValue['secondsExpire'] : 0;
            $this->overrideValue = isset($siteDataValue['overrideValue']) ? $siteDataValue['overrideValue'] : 0;
            $this->ignore = isset($siteDataValue['ignore']) ? $siteDataValue['ignore'] : false;

            self::$isLoaded = true;
        }
    }

    public function getSettings()
    {
        return array(
            'rootNodeIdList' => $this->rootNodeIdList,
            'dataTimeAttributeIdentifierList' => $this->dataTimeAttributeIdentifierList,
            'sectionIdList' => $this->sectionIdList,
            'secondsExpire' => $this->secondsExpire,
            'overrideValue' => $this->overrideValue,
            'ignore' => $this->ignore,
        );
    }

    public function getDefaultSectionId()
    {
        return $this->defaultSectionId;
    }

    public function getDefaultSecondExpire()
    {
        return $this->defaultSecondExpire;
    }

    /**
     * @return array
     */
    private static function getRulesFromBackendIni()
    {
        return array(
            'rootNodeIdList' => OpenPAINI::variable('ChangeSection', 'RootNodeList'),
            'dataTimeAttributeIdentifierList' => OpenPAINI::variable('ChangeSection', 'DataTime'),
            'sectionIdList' => OpenPAINI::variable('ChangeSection', 'ToSection'),
            'secondsExpire' => OpenPAINI::variable('ChangeSection', 'ScadeDopoTotSecondi', 0),
            'overrideValue' => OpenPAINI::variable('ChangeSection', 'UsaValore', false),
            'ignore' => OpenPAINI::variable('ChangeSection', 'Ignora', false),
        );
    }

    public static function hasRulesBackup()
    {
        return eZSiteData::fetchByName('changesectionsettings_backup') instanceof eZSiteData;
    }

    public static function storeBackup()
    {
        $data = '';
        $currentData = eZSiteData::fetchByName('changesectionsettings');
        if ($currentData instanceof eZSiteData) {
            $data = $currentData->attribute('value');
        }

        $siteData = eZSiteData::fetchByName('changesectionsettings_backup');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changesectionsettings_backup',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', $data);
        $siteData->store();
    }

    public static function resetRulesFromBackendIni()
    {
        $siteData = eZSiteData::fetchByName('changesectionsettings');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changesectionsettings',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', json_encode(self::getRulesFromBackendIni()));
        $siteData->store();
    }

    public static function restoreBackup()
    {
        $backupData = eZSiteData::fetchByName('changesectionsettings_backup');
        if ($backupData instanceof eZSiteData) {
            $data = $backupData->attribute('value');

            $siteData = eZSiteData::fetchByName('changesectionsettings');
            if (!$siteData instanceof eZSiteData) {
                $siteData = new eZSiteData([
                    'name' => 'changesectionsettings',
                    'value' => ''
                ]);
            }
            $siteData->setAttribute('value', $data);
            $siteData->store();

            $backupData->remove();
        }
    }

    public static function validateSetting($classIdentifier, $rootNodeId, $dataTimeAttributeIdentifier, $sectionId, $secondsExpire, $overrideValue, $ignore)
    {
        $class = eZContentClass::fetchByIdentifier($classIdentifier);
        if (!$class instanceof eZContentClass) {
            throw new Exception("Classe $classIdentifier non trovata");
        }

        /** @var eZContentClassAttribute[] $dataMap */
        $dataMap = $class->dataMap();
        if (!isset($dataMap[$dataTimeAttributeIdentifier])) {
            throw new Exception("Attributo $dataTimeAttributeIdentifier non trovato");
        }

        if (!in_array($dataMap[$dataTimeAttributeIdentifier]->attribute('data_type_string'), [eZDateType::DATA_TYPE_STRING, eZDateTimeType::DATA_TYPE_STRING])) {
            throw new Exception("Tipo attributo $dataTimeAttributeIdentifier non ammesso");
        }

        if (is_numeric($rootNodeId)) {
            if (!eZContentObjectTreeNode::fetch((int)$rootNodeId) instanceof eZContentObjectTreeNode) {
                throw new Exception("Nodo $rootNodeId non trovato");
            }
        } elseif (is_string($rootNodeId) && $rootNodeId !== 'RootNode') {
            throw new Exception("Stringa $rootNodeId non riconosciuta");
        }

        if (!empty($sectionId) && !eZSection::fetchByIdentifier($sectionId)) {
            throw new Exception("Sezione $sectionId non trovata");
        }

        if (!empty($overrideValue) && !in_array($overrideValue, ['maggiore', 'minore'])) {
            throw new Exception("Valore override $overrideValue non ammesso (maggiore, minore)");
        }

        if (!empty($ignore) && !in_array($ignore, ['attributo', 'secondi'])) {
            throw new Exception("Valore ignore $ignore non ammesso (attributo, secondi)");
        }

        if (!empty($secondsExpire) && !is_numeric($secondsExpire)){
            throw new Exception("Valore secondi $secondsExpire non valido");
        }
    }

    public function setSetting($classIdentifier, $rootNodeId, $dataTimeAttributeIdentifier, $sectionId, $secondsExpire, $overrideValue, $ignore)
    {
        $this->rootNodeIdList[$classIdentifier] = $rootNodeId;
        $this->dataTimeAttributeIdentifierList[$classIdentifier] = $dataTimeAttributeIdentifier;

        if (!empty($sectionId))
            $this->sectionIdList[$classIdentifier] = $sectionId;
        else
            unset($this->sectionIdList[$classIdentifier]);

        if (!empty($secondsExpire))
            $this->secondsExpire[$classIdentifier] = $secondsExpire;
        else
            unset($this->secondsExpire[$classIdentifier]);

        if (!empty($overrideValue))
            $this->overrideValue[$classIdentifier] = $overrideValue;
        else
            unset($this->overrideValue[$classIdentifier]);

        if (!empty($ignore))
            $this->ignore[$classIdentifier] = $ignore;
        else
            unset($this->ignore[$classIdentifier]);
    }

    public function removeSetting($classIdentifier)
    {
        unset($this->rootNodeIdList[$classIdentifier]);
        unset($this->dataTimeAttributeIdentifierList[$classIdentifier]);
        unset($this->sectionIdList[$classIdentifier]);
        unset($this->secondsExpire[$classIdentifier]);
        unset($this->overrideValue[$classIdentifier]);
        unset($this->ignore[$classIdentifier]);
    }

    public function store($data = false)
    {
        if (!$data) {
            $data = $this->getSettings();
        }
        $siteData = eZSiteData::fetchByName('changesectionsettings');
        if (!$siteData instanceof eZSiteData) {
            $siteData = new eZSiteData([
                'name' => 'changesectionsettings',
                'value' => ''
            ]);
        }
        $siteData->setAttribute('value', json_encode($data));
        $siteData->store();

        self::$isLoaded = null;
        $this->loadSettings();
    }

    public function getMessages()
    {
        return $this->messages;
    }


    public function setLog($bool)
    {
        $this->log = $bool;
    }

    public function result()
    {
        return self::$changeNodeIds;
    }

    public function changeAllSubTreeSection()
    {
        $moveToTrashNodes = array();
        foreach ($this->rootNodeIdList as $classIdentifier => $nodeId) {
            try {
                $this->changeSubTreeSectionForClass($nodeId, $classIdentifier, $moveToTrashNodes);
            } catch (Exception $e) {
                if ($this->log) $this->error($e->getMessage());
            }
        }
        $this->removeNodes($moveToTrashNodes);
    }

    public function changeSubTreeSectionForClass($subTreeNodeId, $classIdentifier, &$moveToTrashNodes)
    {
        $this->getCurrentRootNode($subTreeNodeId);
        $this->getCurrentParameters($classIdentifier);
        $humanSecondsExpire = intval($this->currentSecondsExpire / 60 / 60 / 24 / 365);

        if ($this->log) {
            $this->output("classe: {$this->currentClassIdentifier} ", false);
            $this->output("subtree: {$this->currentRootNode->attribute( 'node_id' )} ", false);
            $this->output("attributo: {$this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier]} ", false);
            $this->output("secondi: {$this->currentSecondsExpire} ($humanSecondsExpire anni) ", false);
            if ($this->currentOverrideValue) {
                $this->output("usa il valore {$this->currentOverrideValue}");
            }
            if ($this->currentIgnore) {
                $this->output("ignora {$this->currentIgnore}");
            }
            $this->output();
        }

        $params = array(
            'ClassFilterType' => 'include',
            'ClassFilterArray' => array($this->currentClassIdentifier),
            'LoadDataMap' => false,
            'Limitation' => array(),
            'AttributeFilter' => array(array('section', '!=', $this->currentSectionDestinationId))
        );

        $count = (int)$this->currentRootNode->subTreeCount($params);
        if ($this->log) {
            $this->output('Trovati: ' . $count . ' oggetti');
        }
        if ($count > 0) {
            $length = 50;
            $params['Offset'] = 0;
            $params['Limit'] = $length;

            $progressBar = false;
            if ($this->log) {
                $output = new ezcConsoleOutput();
                $progressBarOptions = array('emptyChar' => ' ', 'barChar' => '=');
                $progressBar = new ezcConsoleProgressbar($output, $count, $progressBarOptions);
                $progressBar->start();
            }

            do {
                /** @var eZContentObjectTreeNode[] $nodeArray */
                $nodeArray = (array)$this->currentRootNode->subTree($params);
                foreach ($nodeArray as $currentNode) {
                    if ($this->log) {
                        $progressBar->advance();
                    }
                    $result = $this->changeNodeSection($currentNode, $moveToTrashNodes);
                    if ($this->log && $result) {
                        $this->output('*');
                    }
                }
                $params['Offset'] += $length;
            } while (count($nodeArray) == $length);

            if ($this->log) {
                $progressBar->finish();
            }
        }
        if ($this->log) {
            $this->output();
            $memoryMax = memory_get_peak_usage(); // Result is in bytes
            $memoryMax = round($memoryMax / 1024 / 1024, 2); // Convert in Megabytes
            $this->output('Memoria usata: ' . $memoryMax . 'M');
        }
    }

    public function test($currentNode)
    {
        if (is_numeric($currentNode)) {
            $currentNode = eZContentObjectTreeNode::fetch($currentNode);
        }
        if ($currentNode instanceof eZContentObjectTreeNode) {
            $this->getCurrentParameters($currentNode->attribute('class_identifier'));
            $this->currentUnPublishDateAttribute = $this->getCurrentUnPublishAttribute($currentNode);
            $currentObject = $currentNode->attribute('object');
            $handler = OpenPAObjectHandler::instanceFromContentObject($currentObject);
            $date = $this->currentUnPublishDateAttribute->content();
            $attributeIdentifier = $this->currentUnPublishDateAttribute->attribute('contentclass_attribute_identifier');
            $attributeRetractDate = $date->attribute('timestamp');
            $iniRetractDate = $currentObject->attribute('published') + $this->currentSecondsExpire;
            $objectRetractDate = $this->getRetractDate($attributeRetractDate, $iniRetractDate, $this->currentIgnore, $this->currentOverrideValue);

            $this->notice("{$currentNode->attribute( 'class_identifier' )} Nodo #{$currentNode->attribute( 'node_id' )} - {$currentNode->attribute( 'name' )}");

            $this->warning("Valore di $attributeIdentifier: " . date('Y-m-d H:i', $date->attribute('timestamp')));
            $this->warning("Valore data di pubblicazione sommata a secondi di scadenza ({$this->currentSecondsExpire}): " . date('Y-m-d H:i', $iniRetractDate));
            $this->warning("Valore di ignore: " . $this->currentIgnore);
            $this->warning("Valore di override: " . $this->currentOverrideValue);

            $this->warning("Valore utilizzato da change_section: " . date('Y-m-d H:i', $objectRetractDate));
            $this->warning(" -  controllo se maggiore di zero: " . var_export($objectRetractDate > 0, 1));
            $this->warning(" -  controllo se inferiore ad adesso: " . var_export($objectRetractDate < $this->now, 1));

            $this->warning("Valore di sezione di destinazione: " . $this->currentSectionDestinationId);
            $this->warning("Sezione corrente: " . $currentObject->attribute('section_id') . ' (' . $currentObject->attribute('section_identifier') . ')');
            $this->warning(" -  controllo se diversa da sezione di destinazione: " . var_export($currentObject->attribute('section_id') != $this->currentSectionDestinationId, 1));
            $this->warning(" -  controllo se esiste la sezione di destinazione: " . var_export($this->currentSectionDestinationId !== 0, 1));
            $this->warning(" -  controllo se nessun filtro blocca l'esecuzione: " . var_export($handler->filter('change_section', 'run') == OpenPAObjectHandler::FILTER_CONTINUE, 1));
        }
    }

    public function changeSection($currentNode)
    {
        if (is_numeric($currentNode)) {
            $currentNode = eZContentObjectTreeNode::fetch($currentNode);
        }
        if ($currentNode instanceof eZContentObjectTreeNode) {
            $this->getCurrentParameters($currentNode->attribute('class_identifier'));
            $moveToTrashNodes = array();
            $isChanged = $this->changeNodeSection($currentNode, $moveToTrashNodes);
            $this->removeNodes($moveToTrashNodes);

            return $isChanged;
        }

        return false;
    }

    public function removeNodes($trashNodes)
    {
        if (count($trashNodes) > 0) {
            if ($this->log) $this->output();

            foreach ($trashNodes as $nodeId) {
                eZContentObjectTreeNode::removeSubtrees(array($nodeId), true);
                if ($this->log) {
                    $memoryMax = memory_get_peak_usage(); // Result is in bytes
                    $memoryMax = round($memoryMax / 1024 / 1024, 2); // Convert in Megabytes
                    $this->output("Sposto nel cestino il node #$nodeId (" . $memoryMax . 'M)');
                }
            }
        }
    }

    protected function changeNodeSection(eZContentObjectTreeNode $currentNode, &$moveToTrashNodes)
    {
        if (!isset(self::$changeNodeIds[$this->currentClassIdentifier])) {
            self::$changeNodeIds[$this->currentClassIdentifier] = array();
        }
        /** @var eZContentObject $currentObject */
        $currentObject = $currentNode->attribute('object');
        if ($currentObject instanceof eZContentObject) {
            /** @var eZDateTime $date */
            $this->currentUnPublishDateAttribute = $this->getCurrentUnPublishAttribute($currentNode);
            $date = $this->currentUnPublishDateAttribute->content();

            $attributeRetractDate = $date->attribute('timestamp');
            $iniRetractDate = $currentObject->attribute('published') + $this->currentSecondsExpire;
            $objectRetractDate = $this->getRetractDate($attributeRetractDate, $iniRetractDate, $this->currentIgnore, $this->currentOverrideValue);

            $handler = OpenPAObjectHandler::instanceFromContentObject($currentObject);

            if ($objectRetractDate > 0
                && $objectRetractDate < $this->now
                && $currentObject->attribute('section_id') != $this->currentSectionDestinationId
                && $this->currentSectionDestinationId !== 0
                && $handler->filter('change_section', 'run') == OpenPAObjectHandler::FILTER_CONTINUE) {

                //@todo refactor in service start -> $moveToTrashNodes[] = $handler->filter( 'change_section', 'move_to_trash' )
                $isClone = false;
                if (class_exists('OscuraAttiHandler')) {
                    if (OscuraAttiHandler::isPrivacyClonedObject($currentObject)) {
                        $moveToTrashNodes[] = $currentObject->attribute('main_node_id');
                        $isClone = true;
                    } elseif ($clone = OscuraAttiHandler::hasPrivacyClonedObject($currentObject)) {
                        $moveToTrashNodes[] = $clone->attribute('main_node_id');
                    }
                }
                if ($isClone) return false;
                //@todo refactor in service end

                if ($currentNode->childrenCount() > 0) {
                    eZContentOperationCollection::updateSection($currentNode->attribute('node_id'), $this->currentSectionDestinationId);
                } else {
                    $this->changeSingleObjectSection($currentNode->attribute('contentobject_id'), $this->currentSectionDestinationId);
                }

                $relatedChanges = $this->changeNodeRelatedFilesNodeSection($currentObject, $this->currentSectionDestinationId);

                self::$changeNodeIds[$this->currentClassIdentifier][] = $currentNode->attribute('node_id');

                $this->registerChangeSection($currentObject, $this->currentSectionDestinationId, $relatedChanges);
                $this->flushObject($currentObject);

                return true;
            }
            $handler->flush(false, false);
        }
        return false;
    }

    private function flushObject(eZContentObject $object)
    {
        eZContentObject::clearCache(array($object->attribute('id')));
        $object = eZContentObject::fetch($object->attribute('id'));
        eZContentOperationCollection::registerSearchObject($object->attribute('id'));
        eZContentCacheManager::clearContentCacheIfNeeded($object->attribute('id'));
    }

    protected function changeSingleObjectSection($objectId, $sectionDestinationId)
    {
        $db = eZDB::instance();
        $db->begin();
        $db->query("UPDATE ezcontentobject SET section_id='{$sectionDestinationId}' WHERE id = $objectId");
        $db->commit();
    }

    protected function changeNodeRelatedFilesNodeSection(eZContentObject $object, $sectionDestinationId)
    {
        $relatedChanges = [];
        $dataMap = $object->dataMap();
        foreach ($dataMap as $attribute) {
            if (in_array($attribute->attribute('data_type_string'), [eZObjectRelationListType::DATA_TYPE_STRING, eZObjectRelationType::DATA_TYPE_STRING])) {
                $idList = explode('-', $attribute->toString());
                $objectList = OpenPABase::fetchObjects($idList);
                foreach ($objectList as $object) {
                    if (in_array($object->attribute('class_identifier'), ['file', 'file_pdf'])) {
                        $this->changeSingleObjectSection($object->attribute('id'), $sectionDestinationId);
                        $this->flushObject($object);
                        $relatedChanges[] = $object->attribute('id');
                    }
                }
            }
        }

        return $relatedChanges;
    }

    private function registerChangeSection(eZContentObject $object, $sectionDestinationId, $relatedChanges = array())
    {
        $message = 'Object #' . $object->attribute('id') . ' - Section #' . $sectionDestinationId;
        if (count($relatedChanges)) {
            $message .= ' - Related objects' . implode('-', $relatedChanges);
        }
        eZLog::write($message, 'change_section.log', eZSys::varDirectory() . '/log');
        $this->messages[] = $message;

        $dataMap = $object->dataMap();
        if (isset($dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER])) {
            $value = $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->toString();
            $time = strftime("%b %d %Y %H:%M:%S", strtotime("now"));
            $message = "Cambio sezione in #" . $sectionDestinationId;
            if (count($relatedChanges)) {
                $message .= ' (oggetti correlati' . implode('-', $relatedChanges) . ')';
            }
            $value .= "[$time][change_section] $message \n";
            $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->fromString($value);
            $dataMap[self::REGISTER_CHANGE_ATTRIBUTE_IDENTIFIER]->store();
        }
    }

    protected function getCurrentParameters($classIdentifier)
    {
        $this->currentClassIdentifier = $classIdentifier;
        $this->currentOverrideValue = $this->getCurrentOverrideValue();
        $this->currentIgnore = $this->getCurrentIgnore();
        $this->currentSecondsExpire = $this->getCurrentSecondExpire();
        $this->currentSectionDestinationId = $this->getCurrentSectionDestinationId();
    }


    protected function getRetractDate($attributeTimestamp, $iniTimestamp, $ignore, $overrideValue)
    {
        if ($attributeTimestamp > 0) {
            // fine giornata
            $objectRetractDate = $attributeTimestamp = mktime(23, 59, 59, date("n", $attributeTimestamp), date("j", $attributeTimestamp), date("Y", $attributeTimestamp));
        } else {
            $objectRetractDate = $iniTimestamp;
        }

        if (!$ignore) {
            if ($overrideValue && $overrideValue == 'maggiore') {
                if ($attributeTimestamp > $iniTimestamp) {
                    $objectRetractDate = $attributeTimestamp;
                } else {
                    $objectRetractDate = $iniTimestamp;
                }
            }

            if ($overrideValue && $overrideValue == 'minore') {
                if ($attributeTimestamp < $iniTimestamp) {
                    $objectRetractDate = $attributeTimestamp;
                } else {
                    $objectRetractDate = $iniTimestamp;
                }
            }
        } elseif ($ignore == 'attributo') {
            $objectRetractDate = $iniTimestamp;
        } elseif ($ignore == 'secondi') {
            $objectRetractDate = $attributeTimestamp;
        }

        return $objectRetractDate;
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function getCurrentSectionDestinationId()
    {
        if (isset($this->sectionIdList[$this->currentClassIdentifier])) {
            $toSection = $this->sectionIdList[$this->currentClassIdentifier];
        } else {
            $toSection = $this->defaultSectionId;
        }

        if (!is_numeric($toSection)) {
            $sectionObject = eZSection::fetchByIdentifier($toSection, false);
        } else {
            $sectionObject = eZSection::fetch($toSection, false);
        }

        if (is_array($sectionObject) && !empty($sectionObject)) {
            return $sectionObject['id'];
        }
        throw new Exception("Section $toSection non trovata");
    }

    /**
     * @param eZContentObjectTreeNode $currentNode
     * @return eZContentObjectAttribute
     * @throws Exception
     */
    protected function getCurrentUnPublishAttribute(eZContentObjectTreeNode $currentNode)
    {
        if (isset($this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier])) {
            $attributeIdentifier = $this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier];
            $dataMap = $currentNode->attribute('data_map');
            if (isset($dataMap[$attributeIdentifier]) && $dataMap[$attributeIdentifier] instanceof eZContentObjectAttribute) {
                return $dataMap[$attributeIdentifier];
            } else {
                throw new Exception("Attributo {$this->currentClassIdentifier}/{$this->dataTimeAttributeIdentifierList[$this->currentClassIdentifier]} non trovato");
            }
        }
        throw new Exception("Attributo non trovato");
    }

    /**
     * @return int
     */
    protected function getCurrentSecondExpire()
    {
        if (isset($this->secondsExpire[$this->currentClassIdentifier])) {
            return $this->secondsExpire[$this->currentClassIdentifier];
        } else {
            return $this->defaultSecondExpire;
        }
    }

    /**
     * @return bool|string attributo|secondi
     * @throws Exception
     */
    protected function getCurrentIgnore()
    {
        $ignore = false;
        if (isset($this->ignore[$this->currentClassIdentifier])) {
            if ($this->ignore[$this->currentClassIdentifier] == 'attributo' || $this->ignore[$this->currentClassIdentifier] == 'secondi') {
                $ignore = $this->ignore[$this->currentClassIdentifier];
            } else {
                throw new Exception("Valore Ignora errato: " . $this->ignore[$this->currentClassIdentifier] . ". Valori ammessi: attributo secondi");
            }
        }
        return $ignore;
    }

    /**
     * @return bool| string maggiore|minore
     * @throws Exception
     */
    protected function getCurrentOverrideValue()
    {
        $overrideValue = false;
        if (isset($this->overrideValue[$this->currentClassIdentifier])) {
            if ($this->overrideValue[$this->currentClassIdentifier] == 'maggiore' || $this->overrideValue[$this->currentClassIdentifier] == 'minore') {
                $overrideValue = $this->overrideValue[$this->currentClassIdentifier];
            } else {
                throw new Exception("Valore UsaValore errato: " . $this->overrideValue[$this->currentClassIdentifier] . ". Valori ammessi: maggiore minore");
            }
        }
        return $overrideValue;
    }

    /**
     * @param int $nodeId
     * @throws Exception
     */
    protected function getCurrentRootNode($nodeId)
    {
        if ($nodeId == 'RootNode') {
            $nodeId = eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode');
        }
        $rootNode = eZContentObjectTreeNode::fetch($nodeId);
        if (!$rootNode instanceof eZContentObjectTreeNode) {
            throw new Exception("RootNode {$nodeId} non trovato");
        }
        $this->currentRootNode = $rootNode;
    }

    private function addMessage($string = false, $addEOL = true)
    {
        static $key = 0;
        if ($key == 0)
            $this->messages[$key] = $string;
        else
            $this->messages[$key] .= $string;
        if ($addEOL) $key++;
    }

    private function output($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if (php_sapi_name() == 'cli')
            eZCLI::instance()->output($string, $addEOL);
    }

    private function notice($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if (php_sapi_name() == 'cli')
            eZCLI::instance()->notice($string, $addEOL);
    }

    private function warning($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if (php_sapi_name() == 'cli')
            eZCLI::instance()->warning($string, $addEOL);
    }

    private function error($string = false, $addEOL = true)
    {
        $this->addMessage($string, $addEOL);

        if (php_sapi_name() == 'cli')
            eZCLI::instance()->error($string, $addEOL);
    }
} 
