<?php

class OpenPADFSFileHandlerDFSAWSS3Private extends OpenPADFSFileHandlerDFSAWSS3Public
{
    protected $acl = 'private';

    /**
     * @return array
     */
    protected static function getClientSettings()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = array();

        if ($ini->hasGroup("AWSS3DFSBackendSettings_private")
            && $ini->variable("AWSS3DFSBackendSettings_private", 'Override') == 'enabled') {
            eZDebugSetting::writeDebug('kernel-clustering',"Load override AWSS3DFSBackendSettings_private settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings_private");

        } elseif ($ini->hasGroup("AWSS3DFSBackendSettings")) {
            eZDebugSetting::writeDebug('kernel-clustering',"Load default AWSS3DFSBackendSettings settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings");
        }

        return $parameters;
    }

    public function applyServerUri($filePath)
    {
        return $filePath;
    }
}
