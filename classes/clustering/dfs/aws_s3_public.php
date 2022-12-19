<?php

use Aws\S3\S3Client as S3Client;
use Aws\S3\Exception\S3Exception;

class OpenPADFSFileHandlerDFSAWSS3Public extends OpenPADFSFileHandlerDFSAWSS3Abstract implements eZDFSFileHandlerDFSBackendInterface, eZDFSFileHandlerDFSBackendFactoryInterface
{
    protected $acl = 'public-read';

    /**
     * @return array
     */
    protected static function getClientSettings()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = array();

        if ($ini->hasGroup("AWSS3DFSBackendSettings_public")
            && $ini->variable("AWSS3DFSBackendSettings_public", 'Override') == 'enabled') {
            eZDebugSetting::writeDebug('kernel-clustering',"Load override AWSS3DFSBackendSettings_public settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings_public");

        } elseif ($ini->hasGroup("AWSS3DFSBackendSettings")) {
            eZDebugSetting::writeDebug('kernel-clustering',"Load default AWSS3DFSBackendSettings settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings");
        }

        return $parameters;
    }

    /**
     * Creates a copy of $srcFilePath from DFS to $dstFilePath on DFS
     *
     * @param string $srcFilePath Local source file path
     * @param string $dstFilePath Local destination file path
     * @return bool
     */
    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        try {
            $this->s3client->copyObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $dstFilePath,
                    'CopySource' => $this->bucket . "/" . $srcFilePath,
                    'ACL' => $this->acl
                )
            );
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Copies the DFS file $srcFilePath to FS
     *
     * @param string $srcFilePath Source file path (on DFS)
     * @param string|bool $dstFilePath
     *        Destination file path (on FS). If not specified, $srcFilePath is
     *        used
     *
     * @return bool
     */
    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        try {
            $this->s3client->getObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $srcFilePath,
                    'SaveAs' => $dstFilePath ?: $srcFilePath,
                    'ACL' => $this->acl
                )
            );
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
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
        try {
            $this->s3client->putObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $dstFilePath ?: $srcFilePath,
                    'SourceFile' => $srcFilePath,
                    'ACL' => $this->acl
                )
            );
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Sends the contents of $filePath to default output
     * Does NOT support $startOffset and $length yet
     *
     * @param string $filePath File path
     * @param int $startOffset Starting offset
     * @param bool|int $length Length to transmit, false means everything
     */
    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        try {
            $object = $this->s3client->getObject(array(
                'Bucket' => $this->bucket,
                'Key' => $filePath,
                'Range' => "{$startOffset}-{$length}"
            ));
            echo (string)$object['Body'];
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }
    }

    /**
     * Returns the binary content of $filePath from DFS
     *
     * @param string $filePath local file path
     *
     * @return mixed|bool file's content, or false
     */
    public function getContents($filePath)
    {
        try {
            $object = $this->s3client->getObject(array('Bucket' => $this->bucket, 'Key' => $filePath));
            return (string)$object['Body'];
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
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
        try {
            $this->s3client->upload($this->bucket, $filePath, $contents, $this->acl);
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
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
        try {
            $this->s3client->copyObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $newPath,
                    'CopySource' => $this->bucket . "/" . $oldPath,
                    'ACL' => $this->acl
                )
            );
            $this->delete($oldPath);
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Deletes one or more files from DFS
     *
     * @param string|array $filePath
     *        Single local filename, or array of local filenames
     *
     * @return bool true if deletion was successful, false otherwise
     */
    public function delete($filePath)
    {
        $suffix = eZFile::suffix($filePath);
        try {
            if ($suffix != 'generating') {
                $this->s3client->copyObject(
                    array(
                        'Bucket' => $this->bucket,
                        'Key' => 'trash/' . $filePath,
                        'CopySource' => $this->bucket . "/" . $filePath,
                        'ACL' => 'private'
                    )
                );
            }
            $this->s3client->deleteObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $filePath,
                )
            );
            return true;
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
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
        try {
            return $this->s3client->doesObjectExist($this->bucket, $filePath);
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Returns the mount point
     *
     * @return string
     */
    public function getMountPoint()
    {
        return null;
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
        try {
            $object = $this->s3client->headObject(
                array(
                    'Bucket' => $this->bucket,
                    'Key' => $filePath
                )
            );
            return $object['ContentLength'];
        } catch (S3Exception $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function getFilesList($basePath)
    {
        return new OpenPADFSFileHandlerDFSAmazonFilterIterator(
            $this->s3client->getIterator(
                'ListObjects', array('Bucket' => $this->bucket, 'Prefix' => $basePath)
            )
        );
    }

    /**
     * Append the S3 http host plus the bucket to the URI
     */
    public function applyServerUri($filePath)
    {
        if (strpos($this->httpHost, '{') !== false){
            $serverUri = str_replace('{protocol}', $this->protocol, $this->httpHost);
            $serverUri = str_replace('{bucket}', $this->bucket, $serverUri);
            return rtrim($serverUri, '/') . '/' . $filePath;

        }
        return sprintf('%s://%s/%s/%s', $this->protocol, $this->httpHost, $this->bucket, $filePath);
    }
}
