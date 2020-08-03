<?php

class ObjectHandlerServiceContentLink extends ObjectHandlerServiceBase
{
    protected $isInternal = true;

    protected $isNodeLink = true;

    protected $link;

    function run()
    {
        $this->fnData['link'] = 'getLink';
        $this->fnData['is_internal'] = 'isInternal';
        $this->fnData['target'] = 'getTarget';
        $this->fnData['full_link'] = 'getFullLink';
        $this->fnData['is_node_link'] = 'isNodeLink';
    }

    protected function getFullLink()
    {
        $link = $this->getLink();
        if ( $this->isInternal )
        {
            eZURI::transformURI( $link, false, 'full' );
        }
        return $link;
    }

    protected function isInternal()
    {
        $this->getLink();
        return $this->isInternal;
    }

    protected function isNodeLink()
    {
        $this->getLink();
        return $this->isNodeLink;
    }

    protected function getLink()
    {
        if ($this->link === null){
            $link = false;
            if ( $this->container->getContentNode() instanceof eZContentObjectTreeNode )
            {
                $link = $this->container->getContentNode()->attribute( 'url_alias' );
                if ( $this->container->currentNodeId != $this->container->currentMainNodeId
                    && in_array( $this->container->getContentNode()->attribute( 'class_identifier' ), OpenPAINI::variable( 'AreeTematiche', 'IdentificatoreAreaTematica', array( 'area_tematica' ) ) ) )
                {
                    $link = $this->container->getContentObject()->attribute( 'main_node' )->attribute( 'url_alias' );
                }
                if ( isset( $this->container->attributesHandlers['location'] )
                    && $this->container->attributesHandlers['location']->attribute( 'contentobject_attribute' )->attribute( 'data_type_string' ) == eZURLType::DATA_TYPE_STRING
                    && $this->container->attributesHandlers['location']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
                {
                    $link = $this->container->attributesHandlers['location']->attribute( 'contentobject_attribute' )->attribute( 'content' );
                    $this->isInternal = false;
                    $this->isNodeLink = false;
                }
                if ( isset( $this->container->attributesHandlers['internal_location'] )
                    && $this->container->attributesHandlers['internal_location']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
                {
                    $content = $this->container->attributesHandlers['internal_location']->attribute( 'contentobject_attribute' )->attribute( 'content' );
                    if ( $this->container->attributesHandlers['internal_location']->attribute( 'contentobject_attribute' )->attribute( 'data_type_string' ) == eZObjectRelationListType::DATA_TYPE_STRING )
                    {
                        foreach( $content['relation_list'] as $relation )
                        {
                            $object = eZContentObject::fetch( $relation['contentobject_id'] );
                            if ( $object instanceof eZContentObject )
                            {
                                $node = $object->attribute( 'main_node' );
                                if ( $node instanceof eZContentObjectTreeNode )
                                {
                                    $link = $node->attribute( 'url_alias' );
                                    $this->isInternal = true;
                                    $this->isNodeLink = false;
                                }
                            }
                        }
                    }
                    elseif ( $this->container->attributesHandlers['internal_location']->attribute( 'contentobject_attribute' )->attribute( 'data_type_string' ) == eZObjectRelationType::DATA_TYPE_STRING )
                    {
                        $link = $content->attribute( 'main_node' )->attribute( 'url_alias' );
                        $this->isInternal = true;
                        $this->isNodeLink = false;
                    }
                }
            }
            $this->link = $link;
        }
        
        return $this->link;
    }

    protected function getTarget()
    {
        $target = false;
        if ( isset( $this->container->attributesHandlers['open_in_new_window'] )
            && $this->container->attributesHandlers['open_in_new_window']->attribute( 'contentobject_attribute' )->attribute( 'data_int' ) == 1 )
        {
            $target = '_blank';
        }
        return $target;
    }
}
