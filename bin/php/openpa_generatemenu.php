<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/generatemenu.php -s$sa " . implode( ' ', $arguments );
    print "Eseguo: $command \n";
    system( $command );
    
    if ( in_array( 'sleep', $arguments ) ) sleep(2);
    if ( in_array( 'clear', $arguments ) ) system( 'clear' );
    if ( in_array( 'bell', $arguments ) ) system( 'tput bel' ); 
}

?>
