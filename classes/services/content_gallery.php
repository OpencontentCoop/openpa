<?php

class ObjectHandlerServiceContentGallery extends ObjectHandlerServiceBase
{
    protected static $cache = array();
    protected static $flipControlCache = array();

    protected $imagesFetchParams = array();

    protected $galleriesFetchParams = array();

    function run()
    {
        if ( $this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            $this->imagesFetchParams = array(
                'parent_node_id' => $this->container->currentNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'image' ),
                'sort_by' => $this->container->getContentNode()->attribute( 'sort_array' )
            );

            $this->galleriesFetchParams = array(
                'parent_node_id' => $this->container->currentNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'gallery' ),
                'sort_by' => $this->container->getContentNode()->attribute( 'sort_array' )
            );
        }

        $this->fnData['has_images'] = 'getImageListCount';
        $this->fnData['has_single_images'] = 'getSingleImagesCount';
        $this->fnData['images'] = 'getImageList';
        $this->fnData['title'] = 'getGalleryTitle';
        $this->fnData['has_galleries'] = 'getGalleriesCount';
        $this->fnData['galleries'] = 'getGalleryList';
    }

    function hasFlip()
    {
        if ( !isset( self::$flipControlCache[$this->container->currentNodeId] ) )
        {
            if ( $this->container->hasAttribute( 'file' ) && method_exists( 'eZFlip', 'instance' ) )
            {
                try
                {
                    if ( eZFlip::instance( $this->container->attribute( 'file' )->attribute( 'contentobject_attribute' ) )->isConverted() )
                    {
                        self::$flipControlCache[$this->container->currentNodeId] = true;
                    }
                    else
                    {
                        self::$flipControlCache[$this->container->currentNodeId] = false;
                    }
                }
                catch( Exception $e )
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

    function getSingleImagesCount()
    {
        if ( $this->hasFlip() )
        {
            return false;
        }

        if ( !$this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentNodeId]['single_images_count'] ) )
        {
            $imageCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $this->imagesFetchParams
            );

            self::$cache[$this->container->currentNodeId]['single_images_count'] = $imageCount;
        }
        return self::$cache[$this->container->currentNodeId]['single_images_count'] > 0;
    }

    function getGalleriesCount()
    {
        if ( !$this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentNodeId]['galleries_count'] ) )
        {
            $galleryChildrenCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $this->galleriesFetchParams
            );
            self::$cache[$this->container->currentNodeId]['galleries_count'] = $galleryChildrenCount;
        }
        return self::$cache[$this->container->currentNodeId]['galleries_count'] > 0;
    }

    function getImageListCount()
    {
        return $this->getSingleImagesCount() > 0 || $this->getGalleriesCount() > 0;
    }

    function getGalleryTitle()
    {
        if ( $this->hasFlip() )
        {
            return false;
        }
        return 'Immagini';
    }

    function getImageList()
    {
        if ( $this->hasFlip() )
        {
            return false;
        }

        if ( !$this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentNodeId]['single_images'] ) )
        {
            if ( $this->getImageListCount() > 0 )
            {
                self::$cache[$this->container->currentNodeId]['single_images'] = eZFunctionHandler::execute(
                    'content',
                    'list',
                    $this->imagesFetchParams
                );
            }
            else
            {
                self::$cache[$this->container->currentNodeId]['single_images'] = array();
            }
        }
        return self::$cache[$this->container->currentNodeId]['single_images'];
    }

    function getGalleryList()
    {
        if ( !$this->container->getContentNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentNodeId]['galleries'] ) )
        {
            if ( $this->getGalleriesCount() > 0 )
            {
                self::$cache[$this->container->currentNodeId]['galleries'] = eZFunctionHandler::execute(
                    'content',
                    'list',
                    $this->galleriesFetchParams
                );
            }
            else
            {
                self::$cache[$this->container->currentNodeId]['galleries'] = array();
            }
        }
        return self::$cache[$this->container->currentNodeId]['galleries'];
    }

}