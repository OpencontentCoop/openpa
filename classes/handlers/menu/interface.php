<?php

interface OpenPAMenuHandlerInterface
{
    public function cacheFileName();

    public function getParameters();

    public static function menuRetrieve( $file, $mtime, $args );

    public static function menuGenerate( $file, $args );
}