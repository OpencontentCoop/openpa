<?php

include( 'autoload.php' );

$siteaccess = OpenPABase::getInstances( 'frontend' );

$devCommands = array(
    "ezini set -q -s*** DatabaseSettings Server localhost",
    "ezini set -q -s*** DatabaseSettings Port 5432",
    "ezini set -q -s*** DatabaseSettings User postgres",
    "ezini set -q -s*** DatabaseSettings Password postgres",
    "ezini set -q -s*** DatabaseSettings Charset utf-8"
);

$prodCommands = array(
    "ezini set -q -s*** DatabaseSettings Server consorzio-db-astratto",
    "ezini set -q -s*** DatabaseSettings Port ''",
    "ezini set -q -s*** DatabaseSettings User openpa",
    "ezini set -q -s*** DatabaseSettings Password open1oc",
    "ezini set -q -s*** DatabaseSettings Charset utf-8"
);

$commands = $prodCommands;

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
    print $directory . "\n";
    foreach( $commands as $command )
    {
        $command = str_replace( '***', $directory, $command );
        system( $command );
    }
}
