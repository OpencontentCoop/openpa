<?php

include( 'autoload.php' );
$fileList = array();
eZDir::recursiveList( 'settings/siteaccess', 'settings/siteaccess', $fileList );
$directories = array();
foreach( $fileList as $file )
{
    if ( $file['type'] == 'dir' )
    {
        $directories[] = $file['name'];
    }
}

foreach( $directories as $directory )
{
    $parts = explode( '_', $directory );
    $url = 'http://consorzio-java-astratto:8990/solr/' . $parts[0];
    $command ="ezini set -fsolr.ini -s{$directory} SolrBase SearchServerURI {$url}";
    system( $command );
}