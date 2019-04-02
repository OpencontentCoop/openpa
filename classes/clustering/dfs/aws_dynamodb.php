<?php

use Aws\DynamoDb\DynamoDbClient as DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;

class OpenPADFSFileHandlerDFSAWSDynamoDb implements eZDFSFileHandlerDFSBackendInterface, eZDFSFileHandlerDFSBackendFactoryInterface
{
    use OpenPADFSFileHandlerDFSAWSS3Trait;

    /**
     * @var DynamoDbClient
     */
    private $client;

    private $tableName;

    private $readCapacityUnits;

    private $writeCapacityUnits;

    private static $numQueries = 0;

    public function __construct(DynamoDbClient $client, $tableName, $readCapacityUnits, $writeCapacityUnits)
    {
        $this->client = $client;
        $this->tableName = $tableName;
        $this->readCapacityUnits = $readCapacityUnits;
        $this->writeCapacityUnits = $writeCapacityUnits;
        $this->initTable();
    }

    /**
     * @return static
     */
    public static function build()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = array();
        if ($ini->hasGroup("AWSDynamoDbDFSBackendSettings")) {
            $parameters = $ini->group("AWSDynamoDbDFSBackendSettings");
        }

        $region = isset($parameters['Region']) ? $parameters['Region'] : static::getRegionConfig();

        $readCapacityUnits = isset($parameters['ReadCapacityUnits']) ? (int)$parameters['ReadCapacityUnits'] : 10;
        $writeCapacityUnits = isset($parameters['WriteCapacityUnits']) ? (int)$parameters['WriteCapacityUnits'] : 10;

        $args = [
            'region' => $region,
            'version' => 'latest',
        ];

        if (isset($parameters['Endpoint'])){
            $args['endpoint'] = $parameters['Endpoint'];
        }

        $sdk = new Aws\Sdk($args);
        $client = $sdk->createDynamoDb();

        $tableName = isset($parameters['TableName']) ? $parameters['TableName'] : static::getDynamoDBTableConfig();

        return new static($client, $tableName, $readCapacityUnits, $writeCapacityUnits);
    }

    public function initTable()
    {
        $schema = [
            'TableName' => $this->tableName,
            'KeySchema' => [
                [
                    'AttributeName' => 'file_name',
                    'KeyType' => 'HASH'
                ],
                [
                    'AttributeName' => 'file_path',
                    'KeyType' => 'RANGE'
                ]
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'file_name',
                    'AttributeType' => 'S'
                ],
                [
                    'AttributeName' => 'file_path',
                    'AttributeType' => 'S'
                ]
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => $this->readCapacityUnits,
                'WriteCapacityUnits' => $this->writeCapacityUnits
            ]
        ];

        try {
            $this->client->describeTable(['TableName' => $this->tableName]);
        } catch (DynamoDbException $e) {
            $this->client->createTable($schema);
        }
    }

    public function deleteTable()
    {
        try {
            $this->client->deleteTable(['TableName' => $this->tableName]);
        } catch (DynamoDbException $e) {
            eZDebug::writeError($e->getMessage(), __METHOD__);
        }

    }

    public function getDynamoDbClient()
    {
        return $this->client;
    }

    private function compress($input)
    {
        return base64_encode(gzcompress($input));
    }

    private function uncompress($data)
    {
        return gzuncompress(base64_decode($data));
    }

    public function set($key, $value)
    {
        $marshaler = new Marshaler();

        $itemArray = $marshaler->marshalJson(json_encode([
            'file_name' => $key,
            'file_path' => $key,
            'value' => $this->compress($value)
        ]));
        $params = [
            'TableName' => $this->tableName,
            'Item' => $itemArray
        ];

        $result = $this->client->putItem($params);
        $this->report($params, __METHOD__, $result);

        return true;
    }

    public function get($key)
    {
        $marshaler = new Marshaler();
        $itemKey = $marshaler->marshalJson(json_encode([
            'file_name' => $key,
            'file_path' => $key,
        ]));
        $params = [
            'TableName' => $this->tableName,
            'Key' => $itemKey
        ];

        $result = $this->client->getItem($params);

        $this->report($params, __METHOD__, $result);

        if (!$result["Item"]) {
            return null;
        }

        foreach ($result["Item"] as $key => $value){
            if ($key == 'value'){
                $value = current($value);
                return $this->uncompress($value);
            }
        }

        return null;
    }

    public function del($key)
    {
        $marshaler = new Marshaler();
        $itemKey = $marshaler->marshalJson(json_encode([
            'file_name' => $key,
            'file_path' => $key,
        ]));
        $params = [
            'TableName' => $this->tableName,
            'Key' => $itemKey
        ];

        $result = $this->client->deleteItem($params);
        $this->report($params, __METHOD__, $result);
    }

    public function rename($oldKey, $newKey)
    {
        $value = $this->get($oldKey);
        $this->set($newKey, $value);
        $this->del($oldKey);

        return true;
    }

    public function exists($key)
    {
        $value = $this->get($key);
        return $value !== null;
    }

    public function size($key)
    {
        $value = $this->get($key);
        return strlen($value);
    }

    private function report($params, $caller, $result)
    {
        self::$numQueries++;
        eZDebugSetting::writeDebug(
            'clustering-dynamodb',
            'Request: ' . json_encode($params, 1) . "\nResponse: " . $result,
            'DynamoDB queries ' . self::$numQueries . ' (' . $caller . ')'
        );
    }

    public function copyFromDFSToDFS($srcFilePath, $dstFilePath)
    {
        return $this->set($dstFilePath, $this->get($srcFilePath));
    }

    public function copyFromDFS($srcFilePath, $dstFilePath = false)
    {
        return eZFile::create($dstFilePath ?: $srcFilePath, false, $this->get($srcFilePath));
    }

    public function copyToDFS($srcFilePath, $dstFilePath = false)
    {
        $path = $dstFilePath ?: $srcFilePath;
        $this->set($path, file_get_contents($srcFilePath));
    }

    public function delete($filePath)
    {
        $this->del($filePath);
    }

    public function passthrough($filePath, $startOffset = 0, $length = false)
    {
        echo $this->get($filePath);
    }

    public function getContents($filePath)
    {
        return $this->get($filePath);
    }

    public function createFileOnDFS($filePath, $contents)
    {
        return $this->set($filePath, $contents);
    }

    public function renameOnDFS($oldPath, $newPath)
    {
        return $this->rename($oldPath, $newPath);
    }

    public function existsOnDFS($filePath)
    {
        return $this->exists($filePath);
    }

    public function getDfsFileSize($filePath)
    {
        return $this->size($filePath);
    }

    public function getFilesList($basePath)
    {
        // TODO: Implement getFilesList() method.
    }

    public function applyServerUri($filePath)
    {
        return $filePath;
    }
}
