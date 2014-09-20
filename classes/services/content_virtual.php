<?php

class ObjectHandlerServiceContentVirtual extends ObjectHandlerServiceBase
{
    function run()
    {
        $this->data['folder'] = $this->isVirtualFolder();
        $this->data['calendar'] = $this->isVirtualCalendar();
    }

    protected function isVirtualFolder()
    {
        $data = false;
        if ( isset( $this->container->attributesHandlers['classi_filtro'] )
             && $this->container->attributesHandlers['classi_filtro']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
        {
            $classes = $this->classFilter( $this->container->attributesHandlers['classi_filtro']->attribute( 'contentobject_attribute' ) );
            $subtree = $this->subTree( $this->container->attributesHandlers['subfolders']->attribute( 'contentobject_attribute' ) );
            if ( $classes )
            {
                $data = array( 'classes' => $classes, 'subtree' => $subtree );
            }
        }
        return $data;
    }

    protected function subTree( eZContentObjectAttribute $attribute )
    {
        $subtree = array();
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $relations = $attribute->content();
            foreach( $relations['relation_list'] as $relation )
            {
                $subtree[] = $relation['node_id'];
            }
        }
        return $subtree;
    }

    protected function classFilter( eZContentObjectAttribute $attribute )
    {
        $classes = false;
        if ( $attribute instanceof eZContentObjectAttribute )
        {
            $string = $attribute->toString();
            $array = explode( ',', $string );
            $classes = array_map( 'trim', $array ); //@todo check if exists?
        }
        return $classes;
    }

    protected function isVirtualCalendar()
    {
        $data = false;
        if ( isset( $this->container->attributesHandlers['subtree_array'] )
             && $this->container->attributesHandlers['subtree_array']->attribute( 'has_content' ) )
        {
            $subtree = $this->subTree( $this->container->attributesHandlers['subtree_array']->attribute( 'contentobject_attribute' ) );
            if ( count( $subtree ) > 0 )
            {
                $data = array( 'subtree' => $subtree );
            }
        }
        return $data;
    }
}