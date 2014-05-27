<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$currentIdentifier = $Params['ID'];
$relationClasses = $inverseRelationClasses = $directRelationClasses = array();
$currentClass = eZContentClass::fetchByIdentifier( $currentIdentifier );

if ( $currentClass )
{

    $classes = eZContentClass::fetchAllClasses( false );
    foreach( $classes as $class )
    {
        $class = eZContentClass::fetch( $class['id'] );
        if ( $class->attribute( 'identifier' ) !== $currentIdentifier )
        {
            foreach( $class->attribute( 'data_map' ) as $attribute )
            {
                if ( $attribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
                {
                    $content = $attribute->attribute( 'content' );
                    $list = $content['class_constraint_list'];
                    foreach( $list as $identifier )
                    {
                        if ( $currentIdentifier == $identifier && !isset( $relationClasses[$class->attribute( 'identifier' )] ) )
                        {                            
                            $relationClasses[$class->attribute( 'identifier' )] = $class;
                            $inverseRelationClasses[$class->attribute( 'identifier' )] = $class;
                        }
                    }
                }
            }
        }
    }

    $relationClasses[$currentIdentifier] = $currentClass;
    
    foreach( $currentClass->attribute( 'data_map' ) as $attribute )
    {
        if ( $attribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
        {
            $content = $attribute->attribute( 'content' );
            $list = $content['class_constraint_list'];
            foreach( $list as $identifier )
            {
                if ( $currentIdentifier !== $identifier )
                {
                    $relationClasses[$identifier] = eZContentClass::fetchByIdentifier( $identifier );   
                    $directRelationClasses[$identifier] = eZContentClass::fetchByIdentifier( $identifier );   
                }
            }
        }
    }

}
$tpl->setVariable( 'direct_relations', $directRelationClasses );
$tpl->setVariable( 'inverse_relations', $inverseRelationClasses );
$tpl->setVariable( 'classes', $relationClasses );
$tpl->setVariable( 'current', $currentClass );
echo $tpl->fetch( 'design:openpa/relations.tpl' );
//eZdisplayDebug();
eZExecution::cleanExit();