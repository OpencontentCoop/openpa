<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();

if ( in_array( 'help', $arguments ) )
{
    print "\n=== OpenPA Script ===\nEsecuzione di script php di openpa su tutte le istanze attive \n\n";
    print "\tphp extension/openpa/bin/php/openpa.php <nome_file_script_senza_estensione> <parametri> \n\n";
    print "Esempio:\nphp extension/openpa/bin/php/openpa.php check_class --class=folder \nesegue lo script \"php extension/openpa/bin/php/check_class.php\" con i parametri \"--class=folder\" su tutte le istanze\n\n";
    print "E' possibile usare inoltre i seguenti parametri:\n\n";
    print "\tsleep\t attende input utente dopo l'esecuzione dello script su un'istanza\n";
    print "\tclear\t pulisce lo schermo dopo l'esecuzione dello script su un'istanza\n";
    print "\tbell\t emette un suono dopo l'esecuzione dello script su un'istanza\n\n";
    print "E' inoltre possibile escludere dall'esecuzione alcuni siti attraverso il parametero --exclude, ad esempio:\n\n";
    print "\t--exclude=folgaria,zambana\t esclude i siteaccess folgaria_* e zambana_* dall'esecuzione\n\n";
    eZExecution::cleanExit();
}

$siteaccess = OpenPABase::getInstances();
$script = array_shift( $arguments );
$excludeSiteaccess = array();

$doBell = false;
$doSleep = false;
$doClear = false;

foreach( $arguments as $index => $argument )
{
    if ( strpos( $argument, '--exclude' ) === 0 )
    {
        $parts = explode( '=', $argument );
        $exludeIdentifiers = explode( ',', $parts[1] );        
        foreach( $siteaccess as $indexSa => $sa )
        {
            foreach( $exludeIdentifiers as $exludeIdentifier )
            {
                if ( strpos( $sa, $exludeIdentifier ) === 0 )
                {
                    unset( $siteaccess[$indexSa] );
                }
            }
        }
        unset( $arguments[$index] );
    }
    elseif ( $argument == 'sleep' )
    {
        $doSleep = true;
        unset( $arguments[$index] );
    }
    elseif ( $argument == 'clear' )
    {
        $doClear = true;
        unset( $arguments[$index] );
    }
    elseif ( $argument == 'bell' )
    {
        $doBell = true;
        unset( $arguments[$index] );
    }
}

foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/{$script}.php -s{$sa} " . implode( ' ', $arguments );
    print "\nEseguo: $command \n";
    system( $command );

    if ( $doSleep ) {
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
    }
    if ( $doClear ) system( 'clear' );
    if ( $doBell ) system( 'tput bel' ); 
}

?>
