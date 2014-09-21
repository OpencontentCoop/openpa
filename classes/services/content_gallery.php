<?php

class ObjectHandlerServiceContentGallery extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['images'] = $this->getImageList();
    }

    function getImageList()
    {
        $imageChildren = array();
        $fetchParams = array(
            'parent_node_id' => $this->container->currentNodeId,
            'class_filter_type' => 'include',
            'class_filter_array' => array( 'image' )
        );
        $imageChildrenCount = eZFunctionHandler::execute(
            'content',
            'list_count',
            $fetchParams
        );

        if ( $imageChildrenCount == 0 && $this->container->currentNodeId != $this->container->currentMainNodeId )
        {
            $fetchParams = array(
                'parent_node_id' => $this->container->currentMainNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'image' )
            );
            $imageChildrenCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $fetchParams
            );
        }

        if ( $imageChildrenCount > 0 )
        {
            $imageChildren = eZFunctionHandler::execute(
                'content',
                'list',
                $fetchParams
            );
        }
        else
        {
            $galleryChildren = eZFunctionHandler::execute(
                'content',
                'list_count',
                array(
                     'parent_node_id' => $this->container->currentNodeId,
                     'class_filter_type' => 'include',
                     'class_filter_array' => array( 'gallery' ),
                     'limit' => 1
                )
            );
            if ( count( $galleryChildren ) > 0 && $galleryChildren[0] instanceof eZContentObjectTreeNode )
            {
                $imageChildren = eZFunctionHandler::execute(
                    'content',
                    'list_count',
                    array(
                         'parent_node_id' => $galleryChildren[0]->attribute( 'node_id' ),
                         'class_filter_type' => 'include',
                         'class_filter_array' => array( 'image' )
                    )
                );
            }
        }

        return $imageChildren;
    }
}