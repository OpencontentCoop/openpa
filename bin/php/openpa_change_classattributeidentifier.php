<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/change_classattributeidentifier.php -s$sa " . implode( ' ', $arguments );
    print "Eseguo: $command \n";
    system( $command );
}

?>
