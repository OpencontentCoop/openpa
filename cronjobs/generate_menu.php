<?php

$siteaccess = eZINI::instance()->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' );

foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/generatemenu.php -s$sa";
    //print "Eseguo: $command \n";
    system( $command );
}