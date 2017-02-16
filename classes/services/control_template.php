<?php

class ObjectHandlerServiceControlTemplate extends ObjectHandlerServiceBase
{
    function run()
    {
        $availableView = OpenPAINI::variable('ViewSettings', 'AvailableView', array(
            'full',
            'line',
            'panel',
            'carousel',
            'carousel_simple',
            'carousel_evidence',
            'accordion_content',
            'mail_ezsubtreenotification',
            'full_block',
        ));
        foreach($availableView as $view){
            $this->data[$view] = $this->getViewTemplate($view);
        }
    }

    protected function getViewTemplate( $view )
    {
        $currentDebugTemplatesUsageStatistics = $GLOBALS['eZTemplateDebugTemplatesUsageStatisticsEnabled'];
        $GLOBALS['eZTemplateDebugTemplatesUsageStatisticsEnabled'] = false;
        $currentErrorReporting = error_reporting();
        error_reporting( 0 );
        $defaultTemplateUri = "design:openpa/{$view}/_default.tpl";
        $templateUri = "design:openpa/{$view}/{$this->container->currentClassIdentifier}.tpl";
        $tpl = eZTemplate::factory();
        $result = $tpl->loadURIRoot( $templateUri, false, $extraParameters );
        error_reporting( $currentErrorReporting );
        $GLOBALS['eZTemplateDebugTemplatesUsageStatisticsEnabled'] = $currentDebugTemplatesUsageStatistics;
        return $result ? $templateUri : $defaultTemplateUri;
    }
}
