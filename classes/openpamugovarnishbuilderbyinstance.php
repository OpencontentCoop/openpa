<?php

class OpenpaMugoVarnishBuilderByInstance extends OpenpaMugoVarnishBuilder
{
    public function buildConditionForNodeIdCache($nodeId)
    {
        return ['obj.http.X-Location-Id ~ ' . (int)$nodeId . ' && obj.http.X-Instance ~ ' . preg_quote(OpenPABase::getCurrentSiteaccessIdentifier())];
    }
}