<?php
/** @var eZMOdule $module */
$module = $Params['Module'];
$classIdentifier = $Params['Identifier'];
$tpl = eZTemplate::factory();

$class = $classIdentifier ? eZContentClass::fetchByIdentifier( $classIdentifier ) : false;
if ( !$class instanceof eZContentClass )
{
    $module->redirectToView( 'classes' );
    return;
}

$tpl->setVariable( 'class', $class );
$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/classsettings.tpl' );
$Result['path'] = array( array( 'text' => 'Configurazione classe di contenuto ' . $class->attribute( 'name' ), 'url' => false ) );