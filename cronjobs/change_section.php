<?php

if ( $isQuiet )
{    
    $cli->setIsQuiet( true );
}

$handlers = eZINI::instance( 'walkobjects.ini' )->variable( 'WalkObjectsHandlers', 'AvaiableHandlers' );
$params = array();
$params[] = "Per handler params:";
foreach( $handlers as $handler )
{
    $class = eZINI::instance( 'walkobjects.ini' )->variable( $handler, 'PHPClass' );
    $params[] = $handler . ": " . $class::help();
}

$handlerName = 'change_section';
$handler = false;
if ( in_array( $handlerName, $handlers ) )
{
    if ( eZINI::instance( 'walkobjects.ini' )->hasVariable( $handlerName, 'PHPClass' ) )
    {
        $class = eZINI::instance( 'walkobjects.ini' )->variable( $handlerName, 'PHPClass' );
        $handler = new $class( $options['params'] );
    }
}

$user = eZUser::fetchByName('admin');
eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );

$contentObjects = array();

$count = $handler->fetchCount();

$cli->output( "Number of objects to walk: $count" );

$length = 50;
$handler->setFetchParams( array( 'Offset' => 0 , 'Limit' => $length ) );

$output = new ezcConsoleOutput();
$progressBarOptions = array(
                    'emptyChar'         => ' ',
                    'barChar'           => '='     
                );
if ( $isQuiet )
{
    $progressBarOptions['minVerbosity'] = 10;    
}
$progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
$progressBar->start();

do
{
    $items = $handler->fetch();
    
    foreach ( $items as $item )
    {            
        if ( $handler )
        {
            $progressBar->advance();
            $handler->modify( $item, $cli );
        }
    }
    
    $handler->params['Offset'] += $length;
} while ( count( $items ) == $length );

$progressBar->finish();

?>