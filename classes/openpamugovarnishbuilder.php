<?php

class OpenpaMugoVarnishBuilder implements MugoVarnishBuilderInterface
{
    public function buildConditionForNodeIdCache($nodeId)
    {
        return ['obj.http.X-Location-Id ~ ' . (int)$nodeId . ' && obj.http.X-Ban-Host ~ ' . preg_quote($this->getHostName())];
    }

    public function buildConditionForAllCache()
    {
        return 'obj.http.X-Ban-Url ~ ^/.* && obj.http.X-Ban-Host ~ ' . preg_quote($this->getHostName());
    }

    private function getHostName()
    {
        $pathPrefix = eZINI::instance()->variable('SiteAccessSettings', 'PathPrefix');
        if (!empty($pathPrefix)){
            $siteUrl = eZINI::instance()->variable('SiteSettings', 'SiteURL');
        }else {
            $frontendSiteaccess = OpenPABase::getFrontendSiteaccessName();
            $siteUrl = eZSiteAccess::getIni($frontendSiteaccess)->variable('SiteSettings', 'SiteURL');

            if (empty($siteUrl) || $siteUrl == 'example.com') {
                $backendSiteaccess = OpenPABase::getBackendSiteaccessName();
                $siteUrl = eZSiteAccess::getIni($backendSiteaccess)->variable('SiteSettings', 'SiteURL');
            }

            $parts = explode('/', $siteUrl);
            $siteUrl = $parts[0];
            $siteUrl = str_replace('www', '', $siteUrl);
        }

        return $siteUrl;
    }

    public static function filterVarnishServers($servers)
    {
        $mugoIni = eZINI::instance('mugo_varnish.ini');

        $hostname = $mugoIni->variable('VarnishSettings', 'VarnishHostName');
        if(!empty($hostname)) {
            $servers = gethostbynamel($hostname);
            if (empty($servers)) {
                eZDebug::writeError("Function gethostbynamel on $hostname returns empty result", __METHOD__);
            }
        }

        if (empty($servers)){
            $servers = $mugoIni->variable('VarnishSettings', 'VarnishServers');
        }

        return $servers;
    }
}
