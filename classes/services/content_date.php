<?php

class ObjectHandlerServiceContentDate extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['show_date'] = $this->data['show_date_full'] = (
            !( OpenPAINI::variable( 'GestioneClassi', 'NascondiTuttiUltimaModifica', '' ) == 'enabled' )
            && !( in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'NascondiUltimaModifica', array() ) ) )
        );

        $this->data['show_date_line'] = (
            in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'classi_con_data_inline', array() ) )
            && in_array( $this->container->currentClassIdentifier, OpenPAINI::variable( 'GestioneClassi', 'classi_senza_data_inline', array() ) )
        );
    }
}