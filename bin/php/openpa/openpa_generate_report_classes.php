<?php

include( 'autoload.php' );

$siteaccess = OpenPABase::getInstances();
$arguments = OpenPABase::getOpenPAScriptArguments();

foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/report_classes.php -s$sa " . implode( ' ', $arguments );
    print "Eseguo: $command \n";
    shell_exec( $command );
}

foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/report_classes.php -s$sa --generate-csv " . implode( ' ', $arguments );
    print "Eseguo: $command \n";
    shell_exec( $command );
}


?>
