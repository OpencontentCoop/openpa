<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/ocmaintenance/bin/removetrashedimages.php -s$sa -n";
    print "Eseguo: $command \n";
    system( $command );
}

?>
