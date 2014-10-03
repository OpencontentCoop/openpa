<?php

class ObjectHandlerServiceContentGallery extends ObjectHandlerServiceBase
{
    public $fetchParams = array();
    public $imageCount;
    public $imageList;
    
    function run()
    {
        $this->data['has_images'] = $this->getImageListCount();
        $this->data['images'] = $this->getImageList();
    }

    function getImageListCount()
    {        
        if ( $this->imageCount === null )
        {
            $this->imageCount = 0;
            
            $this->fetchParams = array(
                'parent_node_id' => $this->container->currentNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'image' )
            );
            $this->imageCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $this->fetchParams
            );
    
            if ( $this->imageCount == 0 && $this->container->currentNodeId != $this->container->currentMainNodeId )
            {
                $this->fetchParams = array(
                    'parent_node_id' => $this->container->currentMainNodeId,
                    'class_filter_type' => 'include',
                    'class_filter_array' => array( 'image' )
                );
                $this->imageCount = eZFunctionHandler::execute(
                    'content',
                    'list_count',
                    $this->fetchParams
                );
            }
    
            //if ( $this->imageCount == 0 )
            //{            
            //    $galleryChildren = eZFunctionHandler::execute(
            //        'content',
            //        'list',
            //        array(
            //             'parent_node_id' => $this->container->currentNodeId,
            //             'class_filter_type' => 'include',
            //             'class_filter_array' => array( 'gallery' ),
            //             'limit' => 1
            //        )
            //    );            
            //    if ( count( $galleryChildren ) > 0 && $galleryChildren[0] instanceof eZContentObjectTreeNode )
            //    {
            //        
            //        $this->fetchParams = array(
            //            'parent_node_id' => $galleryChildren[0]->attribute( 'node_id' ),                         
            //            'class_filter_type' => 'include',
            //            'class_filter_array' => array( 'image' )
            //        );
            //        
            //        $this->imageCount = eZFunctionHandler::execute(
            //            'content',
            //            'list_count',
            //            $this->fetchParams
            //        );                
            //    }
            //}
        }
        return $this->imageCount > 0;
    }
    
    function getImageList()
    {
        if ( $this->imageList === null )
        {
            if ( $this->getImageListCount() > 0 )
            {
                $this->imageList = eZFunctionHandler::execute(
                    'content',
                    'list',
                    $this->fetchParams
                );
            }
        }
        return $this->imageList;
    }

}