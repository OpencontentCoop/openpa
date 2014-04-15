<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/sync_trasparenza.php -s$sa";
    print "Eseguo: $command \n";
    system( $command );
    
    if ( in_array( 'sleep', $arguments ) ) sleep(5);
    if ( in_array( 'clear', $arguments ) ) system( 'clear' );
    if ( in_array( 'bell', $arguments ) ) system( 'tput bel' );    
}

?>
