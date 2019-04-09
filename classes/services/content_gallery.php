<?php

class ObjectHandlerServiceContentGallery extends ObjectHandlerServiceBase
{
    protected static $cache = array();
    protected static $flipControlCache = array();

    protected $imagesFetchParams = array();

    protected $galleriesFetchParams = array();

    function run()
    {
        if ( $this->container->getContentMainNode() instanceof eZContentObjectTreeNode )
        {
            $this->imagesFetchParams = array(
                'parent_node_id' => $this->container->currentMainNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'image' ),
                'sort_by' => $this->container->getContentMainNode()->attribute( 'sort_array' )
            );

            $this->galleriesFetchParams = array(
                'parent_node_id' => $this->container->currentMainNodeId,
                'class_filter_type' => 'include',
                'class_filter_array' => array( 'gallery' ),
                'sort_by' => $this->container->getContentMainNode()->attribute( 'sort_array' )
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
        if ( !isset( self::$flipControlCache[$this->container->currentMainNodeId] ) )
        {
            if ( $this->container->hasAttribute( 'file' ) && method_exists( 'eZFlip', 'instance' ) )
            {
                try
                {
                    if ( eZFlip::instance( $this->container->attribute( 'file' )->attribute( 'contentobject_attribute' ) )->isConverted() )
                    {
                        self::$flipControlCache[$this->container->currentMainNodeId] = true;
                    }
                    else
                    {
                        self::$flipControlCache[$this->container->currentMainNodeId] = false;
                    }
                }
                catch( Exception $e )
                {
                    self::$flipControlCache[$this->container->currentMainNodeId] = false;
                }
            }
            else
            {
                self::$flipControlCache[$this->container->currentMainNodeId] = false;
            }
        }
        return self::$flipControlCache[$this->container->currentMainNodeId];
    }

    function getSingleImagesCount()
    {
        if ( $this->hasFlip() )
        {
            return false;
        }

        if ( !$this->container->getContentMainNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentMainNodeId]['single_images_count'] ) )
        {
            $imageCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $this->imagesFetchParams
            );

            if (
                isset($this->container->attributesHandlers['image'])
                && $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->attribute( 'data_type_string' ) == 'ezobjectrelationlist'
                && $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->attribute( 'has_content' )
            ){
                $relatedImages = explode('-', $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->toString());
                if(OpenPAINI::variable('ContentGallery', 'ExcludeFirstImageRelation', 'enabled') == 'enabled'){
                    array_shift($relatedImages);
                }
                $imageCount += count($relatedImages);
            }

            self::$cache[$this->container->currentMainNodeId]['single_images_count'] = $imageCount;
        }
        return self::$cache[$this->container->currentMainNodeId]['single_images_count'] > 0;
    }

    function getGalleriesCount()
    {
        if ( !$this->container->getContentMainNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentMainNodeId]['galleries_count'] ) )
        {
            $galleryChildrenCount = eZFunctionHandler::execute(
                'content',
                'list_count',
                $this->galleriesFetchParams
            );
            self::$cache[$this->container->currentMainNodeId]['galleries_count'] = $galleryChildrenCount;
        }
        return self::$cache[$this->container->currentMainNodeId]['galleries_count'] > 0;
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

        if ( !$this->container->getContentMainNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentMainNodeId]['single_images'] ) )
        {
            if ( $this->getImageListCount() > 0 )
            {
                self::$cache[$this->container->currentMainNodeId]['single_images'] = eZFunctionHandler::execute(
                    'content',
                    'list',
                    $this->imagesFetchParams
                );

                if (
                    isset($this->container->attributesHandlers['image'])
                    && $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->attribute( 'data_type_string' ) == 'ezobjectrelationlist'
                    && $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->attribute( 'has_content' )
                ){
                    $relatedImages = explode('-', $this->container->attributesHandlers['image']->attribute('contentobject_attribute')->toString());
                    if(OpenPAINI::variable('ContentGallery', 'ExcludeFirstImageRelation', 'enabled') == 'enabled'){
                        array_shift($relatedImages);
                    }
                    foreach ($relatedImages as $id) {
                        $relatedImage = eZContentObject::fetch((int)$id);
                        if ($relatedImage instanceof eZContentObject)
                            self::$cache[$this->container->currentMainNodeId]['single_images'][] = $relatedImage->mainNode();
                    }
                }
            }
            else
            {
                self::$cache[$this->container->currentMainNodeId]['single_images'] = array();
            }
        }
        return self::$cache[$this->container->currentMainNodeId]['single_images'];
    }

    function getGalleryList()
    {
        if ( !$this->container->getContentMainNode() instanceof eZContentObjectTreeNode )
        {
            return false;
        }

        if ( !isset( self::$cache[$this->container->currentMainNodeId]['galleries'] ) )
        {
            if ( $this->getGalleriesCount() > 0 )
            {
                self::$cache[$this->container->currentMainNodeId]['galleries'] = eZFunctionHandler::execute(
                    'content',
                    'list',
                    $this->galleriesFetchParams
                );
            }
            else
            {
                self::$cache[$this->container->currentMainNodeId]['galleries'] = array();
            }
        }
        return self::$cache[$this->container->currentMainNodeId]['galleries'];
    }

}
