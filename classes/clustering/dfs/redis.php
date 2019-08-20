<?php

use Predis\Client;
use Predis\Collection\Iterator;

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
        return $this->redisClient->set($dstFilePath, $this->redisClient->get($srcFilePath));
    }

    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        return eZFile::create($dstFilePath ?: $srcFilePath, false, $this->redisClient->get($srcFilePath));
    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        $path = $dstFilePath ?: $srcFilePath;
        $this->redisClient->set($path, file_get_contents($srcFilePath));
    }

    public function delete($filePath)
    {
        $this->redisClient->del($filePath);
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        echo $this->redisClient->get($filePath);
    }

    public function getContents($filePath)
    {
        return $this->redisClient->get($filePath);
    }

    public function createFileOnDFS($filePath, $contents)
    {
        return $this->redisClient->set($filePath, $contents);
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        return $this->redisClient->rename($oldPath, $newPath);
    }

    public function existsOnDFS($filePath)
    {
        return $this->redisClient->exists($filePath);
    }

    public function getDfsFileSize($filePath)
    {
        return $this->redisClient->strlen($filePath);
    }

    public function getFilesList($basePath)
    {
        $list = array();
        foreach (new Iterator\Keyspace($this->redisClient, $basePath.'*') as $key) {
            $list[] = $key;
        }

        return $list;
    }

    public function applyServerUri($filePath)
    {
        return $filePath;
    }
}
