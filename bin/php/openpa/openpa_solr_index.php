<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/ezfind/bin/php/updatesearchindexsolr.php -s$sa --php-exec=php";
    print "Eseguo: $command \n";
    system( $command );
}

?>
