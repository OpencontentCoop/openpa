<?php

class ObjectHandlerServiceContentDate extends ObjectHandlerServiceBase
{
    function run()
    {
        $noDateAtAll = OpenPAINI::variable( 'GestioneClassi', 'NascondiTuttiUltimaModifica', '' ) == 'enabled';
        $noDateClass = in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'NascondiUltimaModifica', array() ) );
        $this->data['show_date'] = !$noDateAtAll && !$noDateAtAll;
    }
}