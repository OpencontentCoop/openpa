<?php

class ObjectHandlerServiceControlTemplate extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['full'] = $this->getFullTemplate();
    }

    protected function getFullTemplate()
    {
        $currentErrorReporting = error_reporting();
        error_reporting( 0 );
        $defaultTemplateUri = "design:full/_default.tpl";
        $templateUri = "design:full/{$this->container->currentClassIdentifier}.tpl";
        $tpl = eZTemplate::factory();
        $result = $tpl->loadURIRoot( $templateUri, false, $extraParameters );
        error_reporting( $currentErrorReporting );
        return $result ? $templateUri : $defaultTemplateUri;
    }
}