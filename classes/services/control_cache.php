<?php

class ObjectHandlerServiceControlCache extends ObjectHandlerServiceBase
{

    function run()
    {
        $noCacheClasses = OpenPAINI::variable( 'GestioneClassi', 'nocache', array( 'questionario', 'survey', 'event_calendar' ) );
        $this->data['no_cache'] = in_array( $this->container->currentClassIdentifier, $noCacheClasses );
    }
}