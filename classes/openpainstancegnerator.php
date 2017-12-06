<?php


class OpenPAInstanceGenerator
{
    protected $data;

    protected $groupedInstances;

    protected $log;

    protected $filename;

    protected $parsedData;

    public function __construct($filename, $log = null)
    {
        $this->log = $log;
        $this->filename = $filename;
        $this->data['server'] = eZSys::serverURL();
        $this->data['document_root'] = eZSys::rootDir();
        $this->data['generator'] = 'extension/openpa/bin/php/openpa/instances.php --regenerate --filename=' . $this->filename;
        $this->groupedInstances = $this->loadFromFileSystem();
        $fileHandler = eZClusterFileHandler::instance($this->filename);
        $this->parsedData = Symfony\Component\Yaml\Yaml::parse($fileHandler->fetchContents());
    }

    public function refresh($identifier = null)
    {
        if ($identifier) {
            $data = $this->parsedData;
            $data['instances'][$identifier] = $this->getInstance($identifier);
        } else {
            $this->getInstances();
            $data = $this->data;
        }

        $yaml = Symfony\Component\Yaml\Yaml::dump($data, 10);
        $fileHandler = eZClusterFileHandler::instance($this->filename);
        $fileHandler->fileStoreContents($this->filename, $yaml, 'opencontent_sys');

    }

    public function read($identifier = null)
    {
        if ($identifier) {
            $value = ( isset( $this->parsedData['instances'][$identifier] ) ) ? array( $identifier => $this->parsedData['instances'][$identifier] ) : null;
        }else{
            $value = $this->parsedData['instances'];
        }
        $yaml = Symfony\Component\Yaml\Yaml::dump($value, 10);
        return $yaml;
    }

    public function getInstances()
    {
        if (!isset( $this->data['instances'] )) {
            $this->data['instances'] = $this->generateInstances();
        }
        return $this->data['instances'];
    }

    public function getInstance($identifier)
    {
        return isset( $this->groupedInstances[$identifier] ) ?
            $this->generateInstance($identifier, $this->groupedInstances[$identifier]) :
            null;
    }

    protected function loadFromFileSystem()
    {
        eZDebug::setHandleType(eZDebug::HANDLE_EXCEPTION);
        $fileList = array();
        eZDir::recursiveList('settings/siteaccess', 'settings/siteaccess', $fileList);
        $siteaccessList = array();
        foreach ($fileList as $file) {
            if ($file['type'] == 'dir' && $file['name'] != 'admin') {
                $siteaccessList[$file['name']] = $file['name'];
            }
        }
        array_unique($siteaccessList);
        sort($siteaccessList);

        $instances = array();
        foreach ($siteaccessList as $siteacces) {
            $instance = new OpenPAInstance($siteacces);
            $identifier = $instance->getIdentifier();
            if (isset( $instances[$identifier] )) {
                $instances[$identifier][] = $instance;
            } else {
                $instances[$identifier] = array($instance);
            }
        }

        return $instances;
    }

    /**
     * @param $id
     * @param OpenPAInstance[] $instanceList
     *
     * @return array
     */
    protected function generateInstance($id, $instanceList)
    {
        $groupData = array(
            'name' => null,
            'url' => null,
            'url_staging' => null,
            'production_date' => null,
            'google_id' => null,
            'var_dir' => null,
            'cache_dir' => null,
            'storage_dir' => null,
            'main_siteaccess' => null,
            'script_siteaccess' => null,
            'site_access' => array(),
            'db_host' => null,
            'db_port' => null,
            'db_type' => null,
            'db_name' => null,
            'db_user' => null,
            'db_password' => null,
            'solr_host' => null
        );

        $dbNames = array();
        $error = false;

        try {
            $instance = $this->findMain($instanceList);
            $groupData['name'] = $instance->getName();
            $groupData['url'] = $instance->getUrl(OpenPAInstance::PRODUCTION);
            $groupData['url_staging'] = $instance->getUrl(OpenPAInstance::STAGING);
            $groupData['production_date'] = $instance->getProductionDate();
            $groupData['google_id'] = $instance->getGoogleId();
            $groupData['cache_dir'] = $instance->getCacheDirectory();
            $groupData['var_dir'] = $instance->getVarDirectory();
            $groupData['storage_dir'] = $instance->getStorageDirectory();
            $groupData['main_siteaccess'] = $instance->getSiteAccessName();
            $databaseSettings = $instance->getDatabaseSettings();
            $groupData['db_host'] = $databaseSettings['Server'];
            $groupData['db_port'] = $databaseSettings['Port'];
            $groupData['db_type'] = $databaseSettings['DatabaseImplementation'];
            $groupData['db_name'] = $databaseSettings['Database'];
            $groupData['db_user'] = $databaseSettings['User'];
            $groupData['db_password'] = $databaseSettings['Password'];
            $groupData['solr_host'] = $instance->getSolrHost();

            $instance = $this->findBackend($instanceList);
            $groupData['script_siteaccess'] = $instance->getSiteAccessName();


            foreach ($instanceList as $instance) {
                $databaseSettings = $instance->getDatabaseSettings();
                $dbNames[$instance->getSiteAccessName()] = $databaseSettings['DatabaseImplementation'] . $databaseSettings['Server'] . $databaseSettings['Port'] . $databaseSettings['Database'] . $databaseSettings['User'] . $databaseSettings['Password'];
                $groupData['site_access'][] = $instance->getSiteAccessName();
            }

            $checkDbUnique = array_unique($dbNames);
            if (count($checkDbUnique) > 1) {
                $error = $dbNames;
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        if ($error) {
            eZCLI::instance()->error($id . ': ' . var_export($error, 1));
        } else {
            if ($this->log) {
                eZCLI::instance()->output($id);
            }
        }

        return $groupData;
    }

    /**
     * @param OpenPAInstance[] $instanceList
     *
     * @return OpenPAInstance
     * @throws Exception
     */
    protected function findMain($instanceList)
    {
        foreach ($instanceList as $instance) {
            if ($instance->isMain()) {
                return $instance;
            }
        }
        foreach ($instanceList as $instance) {
            if ($instance->isBackend()) {
                return $instance;
            }
        }
        throw new Exception("Main instance not found");
    }

    /**
     * @param OpenPAInstance[] $instanceList
     *
     * @return OpenPAInstance
     * @throws Exception
     */
    protected function findBackend($instanceList)
    {
        foreach ($instanceList as $instance) {
            if ($instance->isBackend()) {
                return $instance;
            }
        }
        throw new Exception("Script instance not found");
    }

    /**
     * @return array
     */
    protected function generateInstances()
    {
        $data = array();
        foreach ($this->groupedInstances as $id => $instanceList) {
            $data[$id] = $this->generateInstance($id, $instanceList);
        }

        return $data;
    }

    public function check($identifier = null)
    {
        if ($identifier){
            $this->checkInstance($identifier);
        }else{
            $errors = array();
            foreach($this->parsedData['instances'] as $identifier => $instance){
                echo $identifier . eZCLI::TERMINAL_ENDOFLINE_STRING;
                try {
                    $this->checkInstance($identifier, $instance);
                }catch(Exception $e){
                    $errors[] = $e->getMessage();
                }

                $memoryMax = memory_get_peak_usage();
                $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
                eZCLI::instance()->notice( $memoryMax.'M' );
            }
            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }
        }
    }

    public function checkInstance($identifier, $fileData = null)
    {
        if(!empty($identifier)) {
            if (!$fileData) {
                $fileData = Symfony\Component\Yaml\Yaml::parse($this->read($identifier));
            }
            $sourceData = $this->getInstance($identifier);

            $silentErrorKeys = array('production_date');
            $errors = array();

            if (!$fileData || !isset( $fileData[$identifier] )) {
                $errors[] = "[$identifier] L'istanza $identifier non esiste in instances.yml";
            } else {
                foreach ($fileData[$identifier] as $name => $value) {
                    $fileValue = $value;
                    if (is_array($fileValue)){
                        $fileValue = implode('-', $fileValue);
                    }
                    if (!isset( $sourceData[$name] )) {
                        $errors[] = "[$identifier] Il valore '$name' non esiste nell'installazione corrente";
                    } elseif ($sourceData[$name] !== $value) {
                        $sourceValue = $sourceData[$name];
                        if (is_array($sourceValue)){
                            $sourceValue = implode('-', $sourceValue);
                        }
                        if (!in_array($name, $silentErrorKeys)) {
                            $errors[] = "[$identifier] Il valore di '$name' nel file instances.yml è '$fileValue', nell'installazione corrente invece è '$sourceValue'";
                        }
                    }
                }
            }

            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }

            if (empty( $sourceData['google_id'] )) {
                throw new Exception("[$identifier] Attenzione valore GoogleId vuoto");
            }
        }
    }

}
