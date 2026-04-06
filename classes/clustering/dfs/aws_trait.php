<?php

trait OpenPADFSFileHandlerDFSAWSS3Trait
{
    protected static function getRegionConfig()
    {
        $region = getenv('AWS_REGION');
        if ($region){
            return $region;
        }

        if(defined('AWS_REGION')){
            return AWS_REGION;
        }

        return 'eu-west-1';
    }

    protected  static function getBucketConfig()
    {
        $bucket = getenv('AWS_BUCKET');
        if ($bucket){
            return $bucket;
        }

        if(defined('AWS_BUCKET')){
            return AWS_BUCKET;
        }

        $rootDir = eZSys::rootDir();
        $rootDirParts = explode('/', $rootDir);
        array_pop($rootDirParts); //html
        $bucket = array_pop($rootDirParts);

        return $bucket;
    }

    protected  static function getDynamoDBTableConfig()
    {
        $tableName = getenv('AWS_DYNAMO_DB_TABLE_NAME');
        if ($tableName){
            return $tableName;
        }

        if(defined('AWS_DYNAMO_DB_TABLE_NAME')){
            return AWS_DYNAMO_DB_TABLE_NAME;
        }

        $rootDir = eZSys::rootDir();
        $rootDirParts = explode('/', $rootDir);
        array_pop($rootDirParts); //html
        $tableName = array_pop($rootDirParts);

        return $tableName;
    }
}
