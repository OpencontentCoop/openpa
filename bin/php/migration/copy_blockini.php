<?php

include( 'autoload.php' );

$siteaccess = OpenPABase::getInstances( 'frontend' );
foreach( $siteaccess as $sa )
{
    print $sa;
    if ( $sa !== 'prototipo_frontend' && $sa !== 'prototipo2_frontend'  ) {
        print "Copy in: $sa \n";
        $command = "ezini get -s{$sa} --format=string DesignSettings AdditionalSiteDesignList";
        $test = system($command);
        if (strpos($test, 'openpa_design_base') === false) {
            $file = 'prototipo2_frontend/block.ini.append.php';
        } else {
            $file = 'prototipo_frontend/block.ini.append.php';
        }

        print "Copy in: $sa \n";
        $command = "ezini copy {$file} {$sa}";
        print $command."\n";
        system( $command );

        $saBackend = str_replace( '_frontend', '_backend', $sa );
        $command = "ezini copy {$file} {$saBackend}";
        print $command."\n";
        system( $command );

        $saDebug = str_replace( '_frontend', '_debug', $sa );
        $command = "ezini symlink {$sa} {$saDebug}";
        print $command."\n";
        system( $command );
    }
}




