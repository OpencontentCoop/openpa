<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/ocimportalbo/bin/php/clean_temp_dir.php -s$sa -n";
    print "Eseguo: $command \n";
    system( $command );
    
    $command = "php extension/occsvimport/bin/php/clean_temp_dir.php -s$sa -n";
    print "Eseguo: $command \n";
    system( $command );
    
}

?>
