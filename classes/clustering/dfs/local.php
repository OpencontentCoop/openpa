<?php

class OpenPADFSFileHandlerDFSLocal implements eZDFSFileHandlerDFSBackendInterface, eZDFSFileHandlerDFSBackendFactoryInterface, OpenPADFSFileHandlerDFSLoadMetadataCapable
{
    /**
     * @var eZFSFileHandler
     */
    private $handler;

    private function __construct()
    {
        $this->handler = new eZFSFileHandler();
    }

    public static function build()
    {
        return new static();
    }

    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        $this->handler->fileCopy($srcFilePath, $dstFilePath);

        return true;
    }

    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        if ($dstFilePath) {
            $this->handler->fileCopy($srcFilePath, $dstFilePath);
        }

        return true;
    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        if ($dstFilePath) {
            $this->handler->fileCopy($srcFilePath, $dstFilePath);
        }

        return true;
    }

    public function delete($filePath)
    {
        $this->handler->fileDelete($filePath);
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        $handler = new eZFSFileHandler($filePath);
        $handler->passthrough($startOffset, $length);
    }

    public function getContents($filePath)
    {
        return $this->handler->fileFetchContents($filePath);
    }

    public function createFileOnDFS($filePath, $contents)
    {
        $this->handler->fileStoreContents($filePath, $contents);

        return true;
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        $this->handler->fileMove($oldPath, $newPath);

        return true;
    }

    public function existsOnDFS($filePath)
    {
        return $this->handler->fileExists($filePath);
    }

    public function getDfsFileSize($filePath)
    {
        $handler = new eZFSFileHandler($filePath);

        return $handler->size();
    }

    public function getFilesList($basePath)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $basePath,
                FilesystemIterator::SKIP_DOTS|FilesystemIterator::UNIX_PATHS
            )
        );
    }

    public function applyServerUri($filePath)
    {
        return $filePath;
    }

    public function loadMetadata($filePath)
    {
        if (!file_exists($filePath)){
            eZLog::write('-1', 'aaa.log');
            return array('mtime' => -1);
        }

        return null;
    }

    public function onStoreMetadata($metadata)
    {
        if (is_array($metadata) && isset($metadata['name'])) {
            $filePath = $metadata['name'];
            if (file_exists($filePath)) {
                $dbMtime = $metadata['mtime'];
                $localMtime = @filemtime($filePath);
                eZLog::write($filePath . ' ' . $dbMtime . ' ' . $localMtime, 'aaa.log');
                if ($dbMtime > $localMtime) {
                    eZLog::write('Local file cache is expired ' . $filePath, 'aaa.log');
                    //@touch($filePath, -1);
                }
            }
        }
    }
}