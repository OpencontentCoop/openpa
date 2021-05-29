<?php

class OpenPADFSFileHandlerDFSAWSS3PrivateCache extends OpenPADFSFileHandlerDFSAWSS3Private
{
    /**
     * @return array
     */
    protected static function getClientSettings()
    {
        $ini = OpenPADFSFileHandlerDFSRegistry::getCurrentInstanceIni('openpa_cluster.ini');
        $parameters = array();

        if ($ini->hasGroup("AWSS3DFSBackendSettings_private_cache")
            && $ini->variable("AWSS3DFSBackendSettings_private_cache", 'Override') == 'enabled') {
            eZDebugSetting::writeDebug('kernel-clustering',"Load override AWSS3DFSBackendSettings_private_cache settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings_private_cache");

        } elseif ($ini->hasGroup("AWSS3DFSBackendSettings")) {
            eZDebugSetting::writeDebug('kernel-clustering',"Load default AWSS3DFSBackendSettings settings", __METHOD__);
            $parameters = $ini->group("AWSS3DFSBackendSettings");
        }

        return $parameters;
    }
}
