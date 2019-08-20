<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

$oldClassIdentifier = $http->variable('old_class_identifier', false);
$newClassIdentifier = $http->variable('new_class_identifier', false);
$wrongRelationClasses = array();

$tpl->setVariable( 'old_class_identifier', $oldClassIdentifier );
$tpl->setVariable( 'new_class_identifier', $newClassIdentifier );

if($http->hasPostVariable('check_classes')){
    $classes = eZContentClass::fetchAllClasses( false );
    foreach( $classes as $class )
    {
        $class = eZContentClass::fetch( $class['id'] );
        if ( $class->attribute( 'identifier' ) !== $newClassIdentifier )
        {
            foreach( $class->attribute( 'data_map' ) as $attribute )
            {
                if ( $attribute->attribute( 'data_type_string' ) == 'ezobjectrelationlist' )
                {
                    $content = $attribute->attribute( 'content' );
                    $list = $content['class_constraint_list'];
                    foreach( $list as $identifier )
                    {
                        if ( $oldClassIdentifier == $identifier )
                        {                                                        
                            $wrongRelationClasses[$attribute->attribute( 'id' )] = [
                                'list' => $list,
                                'identifier' => $class->attribute( 'identifier' ) . '/' . $attribute->attribute( 'identifier' )
                            ];
                        }
                    }
                }
            }
        }
    }

    $tpl->setVariable( 'wrong_relation_classes', $wrongRelationClasses );
}

if($http->hasPostVariable('fix_attributes') && !empty($newClassIdentifier)){
    $newClass = eZContentClass::fetchByIdentifier($newClassIdentifier);
    if ($newClass){
        $idList = $http->variable('fix_identifier', false);
        foreach ($idList as $id) {
            $classAttribute = eZContentClassAttribute::fetch((int)$id);
            if ($classAttribute instanceof eZContentClassAttribute && $classAttribute->attribute('data_type_string') == 'ezobjectrelationlist'){
                $classAttributeContent = $classAttribute->attribute( 'content' );
                $classConstraintList = $classAttributeContent['class_constraint_list'];
                $newClassConstraintList = array();
                foreach ($classConstraintList as $classConstraint) {
                    if ($classConstraint == $oldClassIdentifier){
                        $newClassConstraintList[] = $newClassIdentifier;
                    }else{
                        $newClassConstraintList[] = $classConstraint;
                    }
                }
                $classAttributeContent['class_constraint_list'] = $newClassConstraintList;                 
                //$doc = eZObjectRelationListType::createClassDOMDocument( $classAttributeContent );
                //$docText = eZObjectRelationListType::domString( $doc );
                //echo '<pre>'.htmlentities($docText);die();
                //$classAttribute->setAttribute( 'data_text5', $docText );                    
                $classAttribute->setContent( $classAttributeContent );        
                $classAttribute->store();    
                eZContentClassAttribute::expireCache( $classAttribute->ID, $classAttribute->attribute( 'contentclass_id' ) );              
            }
        }
    }else{
        $tpl->setVariable( 'error', "Non esiste una classe con identificatore {$newClassIdentifier}" );
    }
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/fix_class_relation.tpl' );
$Result['path'] = array( array( 'text' => 'Correzione delle relazioni nelle classi', 'url' => false ) );
