<?php

include( 'autoload.php' );

$siteaccess = OpenPABase::getInstances( 'debug' );
foreach( $siteaccess as $sa )
{
    $saFrontend = str_replace( '_debug', '_frontend', $sa );
    $command = "ezini symlink {$saFrontend} {$sa}";
    print $command . "\n";
    system( $command );
}




