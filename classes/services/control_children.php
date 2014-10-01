<?php

class ObjectHandlerServiceControlChildren extends ObjectHandlerServiceBase
{

    protected $availableViews = array(
        'default',
        'filters',
        'icons',
        'map',
        'datatable',
        'calendar'
    );
    protected $currentView;

    function run()
    {
        $this->getCurrentView();
        $this->data['views'] = array();
        foreach( $this->availableViews as $view )
        {            
            $this->data['views'][$view] = array(
                'current_view' => $this->currentView,
                'identifier' => $view,
                'template' => $this->templatePath( $view )
            );
        }
    }
    
    function template()
    {
        $this->getCurrentView();
        $templateName = 'default';
        if ( $this->currentView != null )
        {
            $templateName = $this->currentView;
        }
        return $this->templatePath( $templateName );
    }
    
    protected function getCurrentView()
    {        
        if ( $this->currentView === null )
        {
            $showChildren = true;
            if ( isset( $this->container->attributesHandlers['show_children'] ) )
            {
                $showChildren = (bool) $this->container->attributesHandlers['show_children']->attribute( 'contentobject_attribute' )->attribute( 'data_int' );
            }
            
            if ( $showChildren )
            {
                if ( isset( $this->container->attributesHandlers['children_view'] )
                     && $this->container->attributesHandlers['children_view']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
                {
                    $contentClassAttributeContent = $this->container->attributesHandlers['children_view']->attribute( 'contentclass_attribute' )->attribute( 'content' );
                    $this->availableViews = array();
                    foreach( $contentClassAttributeContent['options'] as $value )
                    {
                        $this->availableViews[] = strtolower( $value['name'] );
                    }
    
                    $value = $this->container->attributesHandlers['children_view']->attribute( 'contentobject_attribute' )->attribute( 'value' );
                    if ( is_array( $value ) )
                    {
                        $value = $value[0];
                        if ( isset( $contentClassAttributeContent['options'][$value] ) )
                        {
                            $this->currentView = strtolower( $contentClassAttributeContent['options'][$value]['name'] );
                        }
                    }
                }
    
                if ( $this->container->currentClassIdentifier == 'event_calendar'
                     && ( isset( $this->container->attributesHandlers['view'] )
                          && $this->container->attributesHandlers['view']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) ) )
                {
                    $contentClassAttributeContent = $this->container->attributesHandlers['view']->attribute( 'contentclass_attribute' )->attribute( 'content' );
                    $value = $this->container->attributesHandlers['view']->attribute( 'contentobject_attribute' )->attribute( 'value' );
                    
                    if ( is_array( $value ) )
                    {                    
                        $value = $value[0];                    
                        if ( isset( $contentClassAttributeContent['options'][$value] ) )
                        {
                            $this->currentView = strtolower( $contentClassAttributeContent['options'][$value]['name'] );
                        }
                    }
                }
            }
            else
            {
                $this->currentView = 'empty';
            }
        }
    }

    

    protected function templatePath( $view )
    {
        return "design:parts/children/{$view}.tpl";
    }

}