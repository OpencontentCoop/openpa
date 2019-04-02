<?php

class OpenPADFSFileHandlerDFSAWSS3Private extends OpenPADFSFileHandlerDFSAWSS3Public
{
    protected $acl = 'private';

    public function applyServerUri($filePath)
    {
        return $filePath;
    }
}
