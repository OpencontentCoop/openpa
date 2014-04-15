<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();
foreach( $siteaccess as $sa )
{
    $command = "php extension/occhangeobjectdate/bin/php/walkobjects.php --handler=change_section -s$sa";
    print "Eseguo: $command \n";
    system( $command );
}

?>
