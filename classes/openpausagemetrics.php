<?php

class OpenPAUsageMetrics
{
    private $availableAppsSuffix;

    private $numeroAbitanti;

    public function __construct()
    {
        $this->availableAppsSuffix = [
            'OpenCity' => 'frontend',
            'OpenAgenda' => 'agenda',
            'OpenTrasparenza' => 'trasparenza',
            'SpaziComuni' => 'booking',
            'OpenConsultazioni' => 'dimmi',
            'OpenSegnalazioni' => 'sensor'
        ];

        $this->numeroAbitanti = 'n.p.'; //@todo
    }

    public function getMetrics()
    {
        $data = array();
        foreach ($this->availableAppsSuffix as $appName => $suffix) {
            $metrics = $this->getMetricByAppName($appName);
            if ($metrics) {
                $data[] = $metrics;
            }
        }

        return $data;
    }

    public function getMetricByAppName($appName)
    {
        if (!isset($this->availableAppsSuffix[$appName])) {
            return false;
        }
        $suffix = $this->availableAppsSuffix[$appName];
        $appUrl = rtrim(OpenPABase::hasActiveSiteaccessSuffix($suffix), '/');

        if (!$appUrl) {
            return false;
        }

        $usage = null;

        if ($appName == 'OpenCity') {
            $usage = [
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di oggetti pubblicati"),
                    'value' => (int)eZContentObjectTreeNode::subTreeCountByNodeID([
                        'Limitation' => []
                    ], 1)
                ],
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di abitanti riferibili all'ente"),
                    'value' => $this->numeroAbitanti
                ]
            ];
        }

        if ($appName == 'OpenAgenda') {
            $siteAccesName = OpenPABase::getCustomSiteaccessName($suffix);
            $classIdentifier = eZSiteAccess::getIni($siteAccesName, 'editorialstuff.ini')->variable('associazione', 'ClassIdentifier');
            $stateId = 0;
            $stateGroup = eZContentObjectStateGroup::fetchByIdentifier('privacy');
            if ($stateGroup instanceof eZContentObjectStateGroup) {
                $state = $stateGroup->stateByIdentifier('public');
                if ($state instanceof eZContentObjectState) {
                    $stateId = $state->attribute('id');
                }
            }

            $usage = [
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di associazioni registrate ed approvate"),
                    'value' => $classIdentifier ? (int)eZContentObjectTreeNode::subTreeCountByNodeID([
                        'ClassFilterType' => 'include',
                        'ClassFilterArray' => [$classIdentifier],
                        'Limitation' => [],
                        'AttributeFilter' => ['and', ['state', "=", $stateId]]
                    ], 1) : 0
                ]
            ];
        }

        if ($appName == 'SpaziComuni' && class_exists('ObjectHandlerServiceControlBookingSalaPubblica')) {
            $booking = new ObjectHandlerServiceControlBookingSalaPubblica();
            $usage = [
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di prenotazioni"),
                    'value' => (int)eZContentObjectTreeNode::subTreeCountByNodeID([
                        'Limitation' => [],
                        'ClassFilterType' => 'include',
                        'ClassFilterArray' => [$booking->prenotazioneClassIdentifier()],
                    ], 1)
                ],
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di abitanti riferibili all'ente"),
                    'value' => $this->numeroAbitanti
                ]
            ];
        }

        if ($appName == 'OpenTrasparenza') {
            $usage = [
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di abitanti riferibili all'ente"),
                    'value' => $this->numeroAbitanti
                ]
            ];
        }

        if ($appName == 'OpenSegnalazioni' || $appName == 'OpenConsultazioni') {
            $usage = [
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di utenti profilati sul sistema"),
                    'value' => (int)eZContentObjectTreeNode::subTreeCountByNodeID([
                        'Limitation' => [],
                        'ClassFilterType' => 'include',
                        'ClassFilterArray' => [eZINI::instance()->variable("UserSettings", "UserClassID")],
                    ], eZINI::instance()->variable('UserSettings', 'DefaultUserPlacement'))
                ],
                [
                    'name' => ezpI18n::tr('openpa/usage_metrics', "Numero di abitanti riferibili all'ente"),
                    'value' => $this->numeroAbitanti
                ]
            ];
        }

        return [
            'service_name' => $appName,
            'service_url' => $appUrl,
            'usage_metrics' => $usage
        ];
    }
}