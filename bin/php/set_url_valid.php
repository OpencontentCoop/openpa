<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "OpenPA linkcheck defender" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();
$options = $script->getOptions( "[link_id:]", "",
    array( "link_id" => "Id del link" )
);
$script->initialize();
try
{
    /** @var eZURL[] $linkList */
    $linkList = array();

    if ($options['link_id']){
        $link = eZURL::fetch($options['link_id']);
        if ( $link instanceof eZURL){
            $linkList = array($link);
        }
    }else{
        $linkList = eZURL::fetchList( array( 'only_published' => true ) );
    }

    foreach ( $linkList as $link )
    {
        $linkID = $link->attribute( 'id' );
        eZURL::setIsValid( $linkID, true );
    }
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}