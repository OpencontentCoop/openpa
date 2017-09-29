<?php

class ObjectHandlerServiceContentRelated extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->fnData['info'] = 'infoList';
        $this->fnData['classification'] = 'classificationList';
        $this->fnData['has_data'] = 'hasData';

    }

    protected function hasData()
    {
        return ( $this->classificationList( true ) + $this->infoList( true ) ) > 0;
    }
    
    protected function infoList( $count = false )
    {
        if ( $count )
            return $this->objectCount( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_correlati_centro', array() ) );
        return $this->objectList( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_correlati_centro', array() ) );
    }

    protected function classificationList( $count = false )
    {
        if ( $count )
            return $this->objectCount( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_classificazione', array() ) );
        return $this->objectList( OpenPAINI::variable( 'DisplayBlocks', 'oggetti_classificazione', array() ) );
    }

    protected function objectList( $attributeList )
    {
        $objects = array();
        if ( !empty( $attributeList ) )
        {
            $attributeList = array_unique($attributeList);
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
    
    protected function objectCount( $attributeList )
    {
        $objects = 0;
        if ( !empty( $attributeList ) )
        {
            $attributeList = array_unique($attributeList);
            foreach( $attributeList as $attributeIdentifier )
            {
                if ( isset( $this->container->attributesHandlers[$attributeIdentifier] ) )
                {
                    /** @var eZContentObject[] $relatedObjects */
                    $relatedObjectsCount = eZFunctionHandler::execute(
                        'content',
                        'related_objects_count',
                        array(
                             'object_id' => $this->container->currentObjectId,
                             'attribute_identifier' => $this->container->currentClassIdentifier . '/' . $attributeIdentifier
                        )
                    );
                    $objects += $relatedObjectsCount;
                }
            }
        }
        return $objects;
    }


}
