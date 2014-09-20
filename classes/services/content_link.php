<?php


class ObjectHandlerServiceContentLink extends ObjectHandlerServiceBase
{
    protected $isInternal = true;

    function run()
    {
        $this->data['link'] = $this->getLink();
        $this->data['is_internal'] = $this->isInternal;
        $this->data['target'] = $this->getTarget();
    }

    protected function getLink()
    {
        $link = $this->container->getContentNode()->attribute( 'url_alias' );
        if ( $this->container->currentNodeId != $this->container->currentMainNodeId
             && $this->container->hasAttribute( 'control_area_tematica' )
             && $this->container->attribute( 'control_area_tematica' )->attribute( 'is_area_tematica' ) )
        {
            $link = $this->container->getContentObject()->attribute( 'main_node' )->attribute( 'url_alias' );
        }
        if ( isset( $this->container->attributesHandlers['location'] )
             && $this->container->attributesHandlers['location']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
        {
            $link = $this->container->attributesHandlers['location']->attribute( 'contentobject_attribute' )->attribute( 'content' );
            $this->isInternal = false;
        }
        return $link;
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