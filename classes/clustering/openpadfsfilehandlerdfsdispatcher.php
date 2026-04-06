<?php
/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 * @package kernel
 */

/**
 * DFS FS handler that dispatches/proxies calls to a sub-handler.
 */
class OpenPADFSFileHandlerDFSDispatcher implements eZDFSFileHandlerDFSBackendInterface, eZDFSFileHandlerDFSBackendFactoryInterface
{
    /** @var OpenPADFSFileHandlerDFSRegistry */
    private $fsHandlersRegistry = array();

    private static $instance;

    /**
     * @param OpenPADFSFileHandlerDFSRegistry $fsHandlersRegistry
     */
    public function __construct(OpenPADFSFileHandlerDFSRegistry $fsHandlersRegistry)
    {
        $this->fsHandlersRegistry = $fsHandlersRegistry;
    }

    /**
     * Instantiates the dispatcher
     * @return self
     */
    public static function build()
    {
        if (self::$instance === null){
            self::$instance = new self(OpenPADFSFileHandlerDFSRegistry::build());
        }

        return self::$instance;
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     */
    private function getHandler($path)
    {
        return $this->fsHandlersRegistry->getHandler($path);
    }

    /**
     * Returns all the fs handlers
     * @return eZDFSFileHandlerDFSBackendInterface[]
     */
    private function getAllHandlers()
    {
        return $this->fsHandlersRegistry->getAllHandlers();
    }

    /**
     * Creates a copy of $srcFilePath from DFS to $dstFilePath on DFS
     *
     * @param string $srcFilePath Local source file path
     * @param string $dstFilePath Local destination file path
     *
     * @return bool
     */
    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        $srcHandler = $this->getHandler($srcFilePath);
        $dstHandler = $this->getHandler($dstFilePath);

        if ($srcHandler === $dstHandler) {
            return $srcHandler->copyFromDFSToDFS($srcFilePath, $dstFilePath);
        } else {
            return $dstHandler->createFileOnDFS($dstFilePath, $srcHandler->getContents($srcFilePath));
        }
    }

    /**
     * Copies the DFS file $srcFilePath to FS
     *
     * @param string $srcFilePath Source file path (on DFS)
     * @param string|bool $dstFilePath Destination file path (on FS). If not specified, $srcFilePath is used
     *
     * @return bool
     */
    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        return $this->getHandler($srcFilePath)->copyFromDFS($srcFilePath, $dstFilePath);
    }

    /**
     * Copies the local file $filePath to DFS under the same name, or a new name
     * if specified
     *
     * @param string $srcFilePath Local file path to copy from
     * @param bool|string $dstFilePath
     *        Optional path to copy to. If not specified, $srcFilePath is used
     *
     * @return bool
     */
    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        return $this->getHandler($dstFilePath ?: $srcFilePath)->copyToDFS($srcFilePath, $dstFilePath);
    }

    /**
     * Deletes one or more files from DFS
     *
     * @param string|array $filePath Single local filename, or array of local filenames
     *
     * @return bool true if deletion was successful, false otherwise
     */
    public function delete($filePath)
    {
        $map = $this->mapFilePathArray((array)$filePath);

        $returnValue = true;
        /** @var eZDFSFileHandlerDFSBackendInterface $handler */
        foreach ($map['handlers'] as $handlerClass => $handler) {
            $returnValue &= $handler->delete($map['files'][$handlerClass]);
        }

        return (bool)$returnValue;
    }

    /**
     * Sends the contents of $filePath to default output
     *
     * @param string $filePath File path
     * @param int $startOffset Starting offset
     * @param bool|int $length Length to transmit, false means everything
     *
     * @return bool true, or false if operation failed
     */
    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        $handler = $this->getHandler($filePath);

        if (defined('CLUSTER_ENABLE_DEBUG') && CLUSTER_ENABLE_DEBUG) {
            header("X-DFSHandler: " . get_class($handler));
        }
        if (get_class($handler) == 'eZDFSFileHandlerDFSBackend') {
            $gateway = ezpClusterGateway::getGateway();
            ezpClusterGateway::setGatewayClass('ezpDfsPostgresqlClusterGateway');
            $gateway->passthrough($filePath, $startOffset, $length);
        } else {
            $handler->passthrough($filePath, $startOffset, $length);
        }
    }

    /**
     * Returns the binary content of $filePath from DFS
     *
     * @param string $filePath local file path
     *
     * @return string|bool file's content, or false
     */
    public function getContents($filePath)
    {
        return $this->getHandler($filePath)->getContents($filePath);
    }

    /**
     * Creates the file $filePath on DFS with content $contents
     *
     * @param string $filePath
     * @param string $contents
     *
     * @return bool
     */
    public function createFileOnDFS($filePath, $contents)
    {
        return $this->getHandler($filePath)->createFileOnDFS($filePath, $contents);
    }

    /**
     * Renamed DFS file $oldPath to DFS file $newPath
     *
     * @param string $oldPath
     * @param string $newPath
     *
     * @return bool
     */
    public function renameOnDFS($oldPath, $newPath)
    {
        $oldPathHandler = $this->getHandler($oldPath);
        $newPathHandler = $this->getHandler($newPath);

        // same handler, normal rename
        if ($oldPathHandler === $newPathHandler) {
            return $oldPathHandler->renameOnDFS($oldPath, $newPath);
        } // different handlers, create on new, delete on old
        else {
            if ($newPathHandler->createFileOnDFS($newPath, $oldPathHandler->getContents($oldPath)) !== true)
                return false;

            return $oldPathHandler->delete($oldPath);
        }
    }

    /**
     * Checks if a file exists on the DFS
     *
     * @param string $filePath
     *
     * @return bool
     */
    public function existsOnDFS($filePath)
    {
        return $this->getHandler($filePath)->existsOnDFS($filePath);
    }

    /**
     * Returns size of a file in the DFS backend, from a relative path.
     *
     * @param string $filePath The relative file path we want to get size of
     *
     * @return int
     */
    public function getDfsFileSize($filePath)
    {
        return $this->getHandler($filePath)->getDfsFileSize($filePath);
    }

    /**
     * Returns an AppendIterator with every handler's iterator
     *
     * @param string $basePath
     *
     * @return Iterator
     */
    public function getFilesList($basePath)
    {
        $iterator = new AppendIterator();
        foreach ($this->getAllHandlers() as $handler) {
            $iterator->append($handler->getFilesList($basePath));
        }
        return $iterator;
    }

    public function applyServerUri($filePath)
    {
        return $this->getHandler($filePath)->applyServerUri($filePath);
    }

    /**
     * Groups file paths from $filePath by handlers.
     *
     * @param array $filePath
     * @param eZDFSFileHandlerDFSBackendInterface[] $handler
     * @param array $handlerClass
     *
     * @return array an array with two sub-arrays
     *               $return['handlers'] is a hash of eZDFSFileHandlerDFSBackendInterface, indexed by handler  class name
     *               $return['files'] is a hash of file path  arrays, indexed by handler class name
     */
    private function mapFilePathArray(array $filePath)
    {
        $map = array('handlers' => array(), 'files' => array());
        foreach ($filePath as $path) {
            $handler = $this->getHandler($path);
            $handlerClass = get_class($handler);
            if (!isset($map['handlers'][$handlerClass])) {
                $map['handlers'][$handlerClass] = $handler;
            }

            $map['files'][$handlerClass] = $path;
        }

        return $map;
    }

    public static function loadMetadata($filePath)
    {
        if (eZINI::instance('file.ini')->variable('eZDFSClusteringSettings', 'DFSBackend') == 'OpenPADFSFileHandlerDFSDispatcher') {
            $dispatcher = self::build();
            $handler = $dispatcher->getHandler($filePath);

            if ($handler instanceof OpenPADFSFileHandlerDFSLoadMetadataCapable) {
                return $handler->loadMetadata($filePath);
            }
        }

        return null;
    }
}
