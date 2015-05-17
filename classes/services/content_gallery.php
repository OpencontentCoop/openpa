<?php

class ObjectHandlerServiceContentGallery extends ObjectHandlerServiceBase
{
    protected static $cache = array();
    protected static $flipControlCache = array();
    
    function run()
    {
        $this->fnData['has_images'] = 'getImageListCount';
        $this->fnData['images'] = 'getImageList';
        $this->fnData['title'] = 'getGalleryTitle';
    }

    function hasFlip()
    {
        if ( !isset( self::$flipControlCache[$this->container->currentNodeId] ) )
        {
            if ( method_exists( 'ezFlip', 'has_converted' ) )
            {
                if ( ezFlip::has_converted( $this->container->currentObjectId ) )
                {
                    self::$flipControlCache[$this->container->currentNodeId] = true;
                }
                else
                {
                    self::$flipControlCache[$this->container->currentNodeId] = false;
                }
            }
            else
            {
                self::$flipControlCache[$this->container->currentNodeId] = false;
            }
        }
        return self::$flipControlCache[$this->container->currentNodeId];
    }
    
    function getImageListCount()
    {                
        if ( $this->hasFlip() )
        {
            return false;
        }
        
        if ( !isset( self::$cache[$this->container->currentNodeId] ) )
        {            
            $imageCount = 0;
            $title = false;
            
            $fetchParams = array(
                'parent_node_id' => $this->container->currentNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'image' )
            );
            $imageCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $fetchParams
            );
    
            if ( $imageCount == 0 && $this->container->currentNodeId != $this->container->currentMainNodeId )
            {
                $fetchParams = array(
                    'parent_node_id' => $this->container->currentMainNodeId,
                    'class_filter_type' => 'include',
                    'class_filter_array' => array( 'image' )
                );
                $imageCount = eZFunctionHandler::execute(
                    'content',
                    'list_count',
                    $fetchParams
                );
            }
    
            if ( $imageCount == 0 )
            {            
                $galleryChildren = eZFunctionHandler::execute(
                    'content',
                    'list',
                    array(
                         'parent_node_id' => $this->container->currentNodeId,
                         'class_filter_type' => 'include',
                         'class_filter_array' => array( 'gallery' ),
                         'limit' => 1
                    )
                );            
                if ( count( $galleryChildren ) > 0 && $galleryChildren[0] instanceof eZContentObjectTreeNode )
                {
                    
                    $fetchParams = array(
                        'parent_node_id' => $galleryChildren[0]->attribute( 'node_id' ),                         
                        'class_filter_type' => 'include',
                        'class_filter_array' => array( 'image' )
                    );
                    
                    $imageCount = eZFunctionHandler::execute(
                        'content',
                        'list_count',
                        $fetchParams
                    );
                    
                    $title = $galleryChildren[0]->attribute( 'name' );
                }
            }
            
            self::$cache[$this->container->currentNodeId] = array(
                'image_count' => $imageCount,
                'fetch_params' => $fetchParams,
                'title' => $title
            );        
        }
        return self::$cache[$this->container->currentNodeId]['image_count'] > 0;
    }
    
    function getGalleryTitle()
    {
        if ( $this->hasFlip() )
        {
            return false;
        }
        
        if ( isset( self::$cache[$this->container->currentNodeId]['title'] ) && self::$cache[$this->container->currentNodeId]['title'] )
        {
            return self::$cache[$this->container->currentNodeId]['title'];
        }
        return 'Immagini';
    }
    
    function getImageList()
    {                        
        if ( $this->hasFlip() )
        {
            return false;
        }
        
        if ( !isset( self::$cache[$this->container->currentNodeId]['list'] ) )
        {            
            if ( $this->getImageListCount() > 0 )
            {
                self::$cache[$this->container->currentNodeId]['list'] = eZFunctionHandler::execute(
                    'content',
                    'list',
                    self::$cache[$this->container->currentNodeId]['fetch_params']
                );                
            }
            else
            {
                self::$cache[$this->container->currentNodeId]['list'] = array();
            }
        }        
        return self::$cache[$this->container->currentNodeId]['list'];
    }

}