<?php

interface OpenPAInstaller
{
    public function setScriptOptions( eZScript $script );

    public function beforeInstall( $options = array() );

    public function install();

    public function afterInstall();
}