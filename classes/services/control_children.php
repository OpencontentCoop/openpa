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

    protected $currentExtraConfigs = array();

    protected $currentViews;

    function run()
    {
        $this->fnData['current_view'] = 'getCurrentView';
        $this->fnData['current_extra_configs'] = 'getCurrentExtraConfigs';
        $this->fnData['current_views'] = 'getCurrentViews';
        $this->fnData['views'] = 'getViews';
    }

    function getViews()
    {
        $data = array();
        $availableViews = OpenPAChildrenViewType::getAvailableViews();
        foreach( $availableViews as $identifier => $view )
        {
            $view['current_view'] = $this->getCurrentView();
            $data[$identifier] = $view;
        }
        return $data;
    }

    function template()
    {
        $this->getCurrentView();
        $viewIdentifier = 'default';
        if ( $this->currentView != null )
        {
            $viewIdentifier = $this->currentView;
        }
        return OpenPAChildrenViewType::getViewTemplatePath($viewIdentifier);
    }

    protected function getCurrentExtraConfigs()
    {
        $this->getCurrentView();
        return $this->currentExtraConfigs;
    }

    protected function getCurrentViews()
    {
        $this->getCurrentView();
        return $this->currentViews;
    }

    protected function getCurrentView()
    {
        if ( $this->currentView === null && $this->currentViews === null )
        {
            $this->currentView = false;
            $this->currentViews = array();

            $showChildren = false;
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

                    $values = $this->container->attributesHandlers['children_view']->attribute( 'contentobject_attribute' )->attribute( 'content' );
                    if (class_exists('OpenPAChildrenViewContent') && $values instanceof OpenPAChildrenViewContent){
                        $this->currentExtraConfigs = $values->getExtraConfigs();
                        $values = $values->getOptions();
                    }
                    if ( is_array( $values ) )
                    {
                        $index = 0;
                        foreach($values as $value){
                            if ( isset($contentClassAttributeContent['active_list']) && in_array($value, $contentClassAttributeContent['active_list'] ) && isset($contentClassAttributeContent['views']))
                            {
                                foreach($contentClassAttributeContent['views'] as $view){
                                    if ((int)$view['id'] == (int)$value){
                                        $viewName = $view['identifier'];
                                        if ($index == 0){
                                            $this->currentView = $viewName;
                                        }
                                        $this->currentViews[] = $viewName;
                                        $index++;
                                        break;
                                    }
                                }
                            }elseif ( isset( $contentClassAttributeContent['options'][$value] ) ){
                                $viewName = strtolower( $contentClassAttributeContent['options'][$value]['name'] );
                                $this->currentView = $viewName;
                            }
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

        $currentUri = eZURI::instance(eZSys::requestURI());
        if (isset($currentUri->UserArray['view'])){
            $userView = $currentUri->UserArray['view'];
            if (in_array($userView, $this->currentViews)){
                $this->currentView = $userView;
            }
        }

        return $this->currentView;
    }

}
