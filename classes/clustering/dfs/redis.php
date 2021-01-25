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

    public function __construct(Client $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public static function build()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = array();
        if ($ini->hasGroup("RedisDFSBackendSettings")) {
            $parameters = $ini->group("RedisDFSBackendSettings");
        }
        $endpoint = isset($parameters['Endpoint']) ? $parameters['Endpoint'] : null;
        $redisClient = new Client($endpoint);

        return new static($redisClient);
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
            $this->log( __METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
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
            $this->log( __METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
            return false;
        }
    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        try {
            $path = $dstFilePath ?: $srcFilePath;
            $this->redisClient->set($path, file_get_contents($srcFilePath));
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $srcFilePath, $dstFilePath);
        }
    }

    public function delete($filePath)
    {
        try {
            $this->redisClient->del($filePath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
        }
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        try {
            echo $this->redisClient->get($filePath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
            echo 'Error getting file. Please contact the site administrator';
        }
    }

    public function getContents($filePath)
    {
        try {
            return $this->redisClient->get($filePath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function createFileOnDFS($filePath, $contents)
    {
        try {
            return $this->redisClient->set($filePath, $contents);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        try {
            return $this->redisClient->rename($oldPath, $newPath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $oldPath, $newPath);
            return false;
        }
    }

    public function existsOnDFS($filePath)
    {
        try {
            return $this->redisClient->exists($filePath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function getDfsFileSize($filePath)
    {
        try {
            return $this->redisClient->strlen($filePath);
        } catch (PredisException $e) {
            $this->log( __METHOD__, $e->getMessage(), $filePath);
            return false;
        }
    }

    public function getFilesList($basePath)
    {
        $list = array();
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
