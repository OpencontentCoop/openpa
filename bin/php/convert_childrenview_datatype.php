<?php
require_once 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance(array(
    'description' => ( "Coverte children_view da ezselection a openpachildrenview" ),
    'use-session' => false,
    'use-modules' => true,
    'use-extensions' => true
));

$script->startup();
$options = $script->getOptions('[class:][attribute:]',
    '',
    array(
        'class' => 'Identificatore della classe (default: pagina_sito)',
        'attribute' => "Identificatore dell'attributo (default: children_view)"
    )
);
$script->initialize();

try {

    $classIdentifier = $options['class'] ? $options['class'] : 'pagina_sito';
    $attributeIdentifier = $options['attribute'] ? $options['attribute'] : 'children_view';


    $class = eZContentClass::fetchByIdentifier($classIdentifier);
    if (!$class instanceof eZContentClass) {
        throw new Exception("La classe {$classIdentifier} non esiste");
    }
    /** @var eZContentClassAttribute $originalAttribute */
    $originalAttribute = $class->fetchAttributeByIdentifier($attributeIdentifier);
    if (!$originalAttribute instanceof eZContentClassAttribute) {
        throw new Exception("L'attributo {$attributeIdentifier} non esiste nella classe {$classIdentifier}");
    }

    $contentAttributes = eZContentObjectAttribute::fetchSameClassAttributeIDList(
        $originalAttribute->attribute('id'),
        true
    );
    foreach ($contentAttributes as $attribute) {
        $attribute->setAttribute('data_type_string', OpenPAChildrenViewType::DATA_TYPE_STRING);
        $attribute->store();
    }

    $originalAttribute->setAttribute('data_type_string', OpenPAChildrenViewType::DATA_TYPE_STRING);
    $originalAttribute->setAttribute("data_int1", 1);
    $originalAttribute->store();

    ezpEvent::getInstance()->notify( 'content/class/cache', array( $class->attribute('id') ) );
    eZCache::clearByTag( 'template' );
    eZCache::clearByTag( 'content' );

} catch (Exception $e) {
    $cli->error($e->getMessage());
}


$script->shutdown();
