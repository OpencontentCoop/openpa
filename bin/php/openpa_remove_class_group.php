<?php

include( 'autoload.php' );
$arguments = OpenPABase::getOpenPAScriptArguments();
$siteaccess = OpenPABase::getInstances();

$argumentsString = '';
foreach( $arguments as $argument )
{
    if ( strpos( $argument, '--classgroup=' ) !== false )
    {
        $argumentPart = explode( '=', $argument );
        $argument = $argumentPart[0] . '="' . $argumentPart[1] . '"';
        $argumentsString .= $argument;
        
    }
    else
        $argumentsString .= $argument;    
}
foreach( $siteaccess as $sa )
{
    $command = "php extension/openpa/bin/php/remove_class_group.php -s$sa " . $argumentsString;
    print "Eseguo: $command \n";
    system( $command );
    
    if ( in_array( 'sleep', $arguments ) ) sleep(2);
    if ( in_array( 'clear', $arguments ) ) system( 'clear' );
    if ( in_array( 'bell', $arguments ) ) system( 'tput bel' ); 
}

?>
