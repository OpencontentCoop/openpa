<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "Migrazione in db di openpa.ini\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions( '[class:]',
    '',
    array( 'class'  => 'Identificatore della classe' )
);
$script->initialize();
$script->setUseDebugAccumulators( true );


try
{
    OpenPAINI::$useDynamicIni = false;
    $map = OpenPAINI::$dynamicIniMap;

    function storeValue($classIdentifier,$attributeIdentifier,$paramId,$key,$value){
        $cli = eZCLI::instance();
        $cli->output( $classIdentifier . ' ', false );
        $cli->output( $attributeIdentifier . ' ', false );
        $cli->output( $paramId . ' ', false );
        $cli->output( $key . ' ', false );
        $cli->output( $value);

        $row = array(
            'class_identifier' => $classIdentifier,
            'attribute_identifier' => '*',
            'handler' => $paramId,
            'key' => 'enabled',
            'value' => 1
        );
        $parameter = new OCClassExtraParameters( $row );
        $parameter->store();

        $row = array(
            'class_identifier' => $classIdentifier,
            'attribute_identifier' => $attributeIdentifier,
            'handler' => $paramId,
            'key' => $key,
            'value' => $value
        );
        $parameter = new OCClassExtraParameters( $row );
        $parameter->store();
    }

//    foreach( $map as $block => $data ){
//        foreach( $data as $variable => $map ) {
//
//            eZCLI::instance()->error( $block . '.' . $variable );
//            $settings = OpenPAINI::variable( $block, $variable );
//            print_r($settings);
//        }
//    }
//    die();

    $classes = eZContentClass::fetchAllClasses(false);
    foreach( $classes as $class ){
        $class = eZContentClass::fetch($class['id']);
        $dataMap = $class->dataMap();
        $identifiers = array_keys($dataMap);
        foreach( $identifiers as $attributeIdentifier ){
            storeValue($class->attribute('identifier'), $attributeIdentifier, 'table_view', 'show', 1);
            storeValue($class->attribute('identifier'), $attributeIdentifier, 'table_view', 'show_link', 1);
        }
    }
    foreach( $map as $block => $data ){
        foreach( $data as $variable => $map ){

            eZCLI::instance()->warning( $block .' ' . $variable );

            $settings = OpenPAINI::variable( $block, $variable );

            $params = explode( '.', $map['to'] );
            $paramId = $params[0];
            $key = $params[1];
            $value =  $map['value'];

            if ( $map['from'] == '_full_identifier' ) {
                foreach( $settings as $setting ) {

                    eZCLI::instance()->error( $setting );

                    $parts = explode('/', $setting);
                    if (count($parts) > 1) {

                        $classIdentifier = $parts[0];
                        $attributeIdentifier = $parts[1];

                        storeValue($classIdentifier, $attributeIdentifier, $paramId, $key, $value);
                    }
                }
            }elseif ( $map['from'] == '_identifier' ) {
                foreach( $settings as $setting ) {
                    $attributes = eZPersistentObject::fetchObjectList(
                        eZContentClassAttribute::definition(),
                        null, array('identifier' => $setting), null, null,
                        true
                    );
                    $classIds = array();
                    foreach ($attributes as $attribute) {
                        $classIds[$attribute->attribute('contentclass_id')][] = $attribute->attribute('identifier');
                    }

                    foreach ($classIds as $id => $attributes) {
                        $attributes = array_unique($attributes);
                        $classIdentifier = eZContentClass::classIdentifierByID($id);
                        foreach ($attributes as $attributeIdentifier) {

                            storeValue($classIdentifier, $attributeIdentifier, $paramId, $key, $value);

                        }
                    }
                }
            }else{
                $parts = explode( '/', $map['from'] );
                if ( count( $parts ) == 2 ){
                    foreach( $settings as $setting ) {
                        $classIdentifier = $parts[0];
                        $fix = explode('/', $setting);
                        if (count($fix) > 1) {
                            $settings = $fix[0];
                        }
                        $attributeIdentifier = $setting;
                        storeValue($classIdentifier, $attributeIdentifier, $paramId, $key, $value);
                    }
                }
            }
        }
    }

    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}