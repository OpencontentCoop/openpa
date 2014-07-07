<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$classIdentifier = $Params['Identifier'];
if ( $classIdentifier )
{
    $class = eZContentClass::fetchByIdentifier( $classIdentifier );
    if ( $class instanceof eZContentClass )
    {
        $tpl->setVariable( 'class', $class );
    }
}
$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/classes.tpl' );
$Result['path'] = array( array( 'text' => 'Classi di contenuto' ,
                                'url' => false ) );
