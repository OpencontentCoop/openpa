<?php

class ObjectHandlerServiceControlChildren extends ObjectHandlerServiceBase
{

    protected $availableViews = array(
        'default',
        'filters',
        'icons',
        'map',
        'calendar'
    );
    protected $currentView;

    function __construct( $data = array() )
    {
        parent::__construct( $data );

        if ( isset( $this->container->attributesHandlers['children_view'] )
             && $this->container->attributesHandlers['children_view']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
        {
            $contentClassAttributeContent = $this->container->attributesHandlers['children_view']->attribute( 'contentclass_attribute' )->attribute( 'content' );
            $this->availableViews = $contentClassAttributeContent['options'];

            $value = $this->container->attributesHandlers['children_view']->attribute( 'contentobject_attribute' )->attribute( 'content' );
            if ( is_array( $value ) )
            {
                $value = $value[0];
                if ( isset( $contentClassAttributeContent['options'][$value] ) )
                {
                    $this->currentView = strtolower( $contentClassAttributeContent['options'][$value] );
                }
            }
        }

        if ( $this->container->currentClassIdentifier == 'event_calendar'
             && isset( $this->container->attributesHandlers['view'] )
             && $this->container->attributesHandlers['view']->attribute( 'contentobject_attribute' )->attribute( 'has_content' ) )
        {
            $this->currentView = $this->container->attributesHandlers['view']->attribute( 'contentobject_attribute' )->attribute( 'content' );
        }
    }

    function run()
    {
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

    protected function templatePath( $view )
    {
        return "design:parts/children/{$view}.tpl";
    }

    function template()
    {
        $templateName = 'default';
        if ( $this->currentView != null )
        {
            $templateName = $this->currentView;
        }
        return $this->templatePath( $templateName );
    }
}