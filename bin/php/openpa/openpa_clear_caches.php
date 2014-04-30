<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php bin/php/ezcache.php --clear-all -s$sa";
    print "Eseguo: $command \n";
    system( $command );
}

?>
