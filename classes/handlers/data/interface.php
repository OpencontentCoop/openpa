<?php

interface OpenPADataHandlerInterface
{
    public function __construct( eZModule $module );

    /**
     * @return string|array|object
     */
    public function getData();
}