<?php

class OpenPADFSFileHandlerDFSLogged implements eZDFSFileHandlerDFSBackendInterface, OpenPADFSFileHandlerDFSLoadMetadataCapable
{
    private $backendImplementation;

    private $backendImplementationName;

    private $methods = array();

    public function __construct(eZDFSFileHandlerDFSBackendInterface $backendImplementation)
    {
        $this->backendImplementation = $backendImplementation;
        $this->backendImplementationName = get_class($backendImplementation);
        eZDebug::createAccumulatorGroup($this->backendImplementationName);
    }

    private function parseMethod($method)
    {
        if (!isset($this->methods[$method])) {
            $parts = explode('::', $method);
            $this->methods[$method] = array_pop($parts);
        }

        return $this->backendImplementationName . '::' . $this->methods[$method];
    }

    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->copyFromDFSToDFS($srcFilePath, $dstFilePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->copyFromDFS($srcFilePath, $dstFilePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;

    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->copyToDFS($srcFilePath, $dstFilePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));


        return $result;
    }

    public function delete($filePath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->delete($filePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        $this->backendImplementation->passthrough($filePath, $startOffset, $length);
    }

    public function getContents($filePath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->getContents($filePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function createFileOnDFS($filePath, $contents)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->createFileOnDFS($filePath, $contents);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->renameOnDFS($oldPath, $newPath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function existsOnDFS($filePath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName);
        $result = $this->backendImplementation->existsOnDFS($filePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function getDfsFileSize($filePath)
    {
        eZDebug::accumulatorStart($this->parseMethod(__METHOD__), $this->backendImplementationName, $this->parseMethod(__METHOD__));
        $result = $this->backendImplementation->getDfsFileSize($filePath);
        eZDebug::accumulatorStop($this->parseMethod(__METHOD__));
        eZDebugSetting::writeDebug('kernel-clustering', "call handler method " . $this->parseMethod(__METHOD__));

        return $result;
    }

    public function getFilesList($basePath)
    {
        return $this->backendImplementation->getFilesList($basePath);
    }

    public function applyServerUri($filePath)
    {
        return $this->backendImplementation->applyServerUri($filePath);
    }

    public function loadMetadata($filePath)
    {
        if ($this->backendImplementation instanceof OpenPADFSFileHandlerDFSLoadMetadataCapable) {
            return $this->backendImplementation->loadMetadata($filePath);
        }

        return null;
    }
}
