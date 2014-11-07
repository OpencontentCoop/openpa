<?php

interface OpenPADataHandlerInterface
{
    public function __construct( array $Params );

    /**
     * @return string|array|object
     */
    public function getData();
}