<?php

interface OpenPAObjectHandlerServiceInterface
{
    /**
     * @return OpenPAObjectHandlerServiceInterface
     */
    function data();

    /**
     * Popola l'array $this->data con chiave => valore
     * @return void
     */
    function run();

    /**
     * Inietta il container nel servizio
     * @param OpenPAObjectHandler $handler
     *
     * @return void
     */
    function setContainer( OpenPAObjectHandler $handler );

    /**
     * @param string $identifier
     *
     * @return void
     */
    function setIdentifier( $identifier );

    /**
     * @return string
     */
    function template();

}