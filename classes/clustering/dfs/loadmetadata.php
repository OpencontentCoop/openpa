<?php

interface OpenPADFSFileHandlerDFSLoadMetadataCapable
{
    public function loadMetadata($filePath);

    public function onStoreMetadata($metadata);
}
