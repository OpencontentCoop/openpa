<?php

include( 'autoload.php' );
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php bin/php/ezcache.php --clear-id='template,content' -s$sa";
    print "Eseguo: $command \n";
    system( $command );
}

?>
