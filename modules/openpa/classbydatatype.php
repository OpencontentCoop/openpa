<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();

$datatype = $Params['DataTypeString'];
$classes = array();
if ( !empty( $datatype ) )
{    
    $classIds = eZContentClass::fetchIDListContainingDatatype( $datatype );
    if ( count( $classIds ) > 0 )
    {
        foreach( $classIds as $id )
        {
            $class = eZContentClass::fetch( $id );
            $classes[$class->attribute( 'identifier' )] = $class;
        }
        ksort( $classes );
    }
}
$tpl->setVariable( 'class_list', $classes );
$tpl->setVariable( 'datatype', $datatype );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/classes.tpl' );
$Result['path'] = array( array( 'text' => 'Classi di contenuto' ,
                                'url' => false ) );
