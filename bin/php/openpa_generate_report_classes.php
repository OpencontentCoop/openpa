<?php

include( 'autoload.php' );

$fileList = array();
eZDir::recursiveList( 'settings/siteaccess', 'settings/siteaccess', $fileList );
$siteaccess = array();
foreach( $fileList as $file )
{
    if ( $file['type'] == 'dir' && strpos( $file['name'], '_frontend' ) !== false )
    {
        $siteaccess[] = $file['name'];
    }
}
array_unique( $siteaccess );
sort( $siteaccess );
foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/openpa_report_classes.php -s$sa";
    print "Eseguo: $command \n";
    shell_exec( $command );
}

foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/openpa_report_classes.php -s$sa --generate-csv";
    print "Eseguo: $command \n";
    shell_exec( $command );
}


?>
