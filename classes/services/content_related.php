<?php

class ObjectHandlerServiceContentRelated extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['info'] = $this->infoList();
        $this->data['classification'] = $this->classificationList();

    }

    protected function infoList()
    {
        return $this->objectList( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_correlati_centro', array() ) );
    }

    protected function classificationList()
    {
        return $this->objectList( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_classificazione', array() ) );
    }

    protected function objectList( $attributeList )
    {
        $objects = array();
        if ( !empty( $attributeList ) )
        {
            foreach( $attributeList as $attributeIdentifier )
            {
                if ( isset( $this->container->attributesHandlers[$attributeIdentifier] ) )
                {
                    /** @var eZContentObject[] $relatedObjects */
                    $relatedObjects = eZFunctionHandler::execute(
                        'content',
                        'related_objects',
                        array(
                             'object_id' => $this->container->currentObjectId,
                             'attribute_identifier' => $this->container->currentClassIdentifier . '/' . $attributeIdentifier
                        )
                    );
                    foreach( $relatedObjects as $object )
                    {
                        if ( $object->attribute( 'can_read' ) )
                        {
                            $className = $object->attribute( 'class_name' );
                            if ( !isset( $objects[$className] ) )
                            {
                                $objects[$className] = array();
                            }
                            $objects[$className][] = $object;
                        }
                    }
                }
            }
        }
        return $objects;
    }


}