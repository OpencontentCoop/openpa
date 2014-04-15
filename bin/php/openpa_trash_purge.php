<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php bin/php/trashpurge.php -s$sa";
    print "Eseguo: $command \n";
    system( $command );
}

?>
