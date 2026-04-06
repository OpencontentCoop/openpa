<?php

class DataHandlerUsageMetrics implements OpenPADataHandlerInterface
{
    private $appName;

    public function __construct(array $Params)
    {
        $this->appName = isset($Params['Parameters'][1]) ? $Params['Parameters'][1] : null;
    }

    public function getData()
    {
        $usageMetrics = new OpenPAUsageMetrics();

        if ($this->appName){
            return $usageMetrics->getMetricByAppName($this->appName);
        }

        return $usageMetrics->getMetrics();
    }

}