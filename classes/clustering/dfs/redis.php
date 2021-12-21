<?php

use Predis\Client;
use Predis\Collection\Iterator;
use Predis\PredisException;

class OpenPADFSFileHandlerDFSRedis implements eZDFSFileHandlerDFSBackendInterface, eZDFSFileHandlerDFSBackendFactoryInterface
{
    /**
     * @var Client
     */
    private $redisClient;

    private $isCluster;

    public function __construct(Client $redisClient, $isCluster = false)
    {
        $this->redisClient = $redisClient;
        $this->isCluster = $isCluster;
    }

    public static function build()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = [];
        if ($ini->hasGroup("RedisDFSBackendSettings")) {
            $parameters = $ini->group("RedisDFSBackendSettings");
        }
        $endpoint = isset($parameters['Endpoint']) ? $parameters['Endpoint'] : null;
        $endpoints = explode(',', $endpoint);

        $password = isset($parameters['Password']) ? $parameters['Password'] : null;
        $isCluster = (isset($parameters['Cluster']) && $parameters['Cluster'] == 'enabled') || count($endpoints) > 1;

        $options = [];
        if (!empty($password)) {
            $options['parameters'] = [
                'password' => $password,
            ];
        }
        if ($isCluster) {
            $options['cluster'] = 'redis';
        }

        $redisClient = new Client(
            $isCluster ? $endpoints : $endpoint,
            count($options) ? $options : null
        );

        return new static($redisClient, $isCluster);
    }

    public function getRedisClient()
    {
        return $this->redisClient;
    }

    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        try {
            return $this->redisClient->set($dstFilePath, $this->redisClient->get($srcFilePath));
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
            return false;
        }
    }

    private function log($method, $message, $filePath, $otherFilePath = null)
    {
        eZLog::write("Error \"$message\" executing \"$method\" in file \"$filePath $otherFilePath\"", 'cluster_error.log');
    }

    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        try {
            return eZFile::create($dstFilePath ?: $srcFilePath, false, $this->redisClient->get($srcFilePath));
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
            return false;
        }
    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        try {
            $path = $dstFilePath ?: $srcFilePath;
            $this->redisClient->set($path, file_get_contents($srcFilePath));
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
        }
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        try {
            echo $this->redisClient->get($filePath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
            echo 'Error getting file. Please contact the site administrator';
        }
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        try {
            if ($this->isCluster) {
                $this->createFileOnDFS($newPath, $this->getContents($oldPath));
                $this->delete($oldPath);
                return true;
            }
            return $this->redisClient->rename($oldPath, $newPath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $oldPath, $newPath);
            return false;
        }
    }

    public function createFileOnDFS($filePath, $contents)
    {
        try {
            return $this->redisClient->set($filePath, $contents);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function getContents($filePath)
    {
        try {
            return $this->redisClient->get($filePath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function delete($filePath)
    {
        try {
            $this->redisClient->del($filePath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
        }
    }

    public function existsOnDFS($filePath)
    {
        try {
            return $this->redisClient->exists($filePath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function getDfsFileSize($filePath)
    {
        try {
            return $this->redisClient->strlen($filePath);
        } catch (PredisException $e) {
            $this->log(__METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function getFilesList($basePath)
    {
        $list = [];
        foreach (new Iterator\Keyspace($this->redisClient, $basePath . '*') as $key) {
            $list[] = $key;
        }

        return $list;
    }

    public function applyServerUri($filePath)
    {
        return $filePath;
    }
}
