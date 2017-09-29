<?php

class ObjectHandlerServiceContentGlobaInfo extends ObjectHandlerServiceBase
{
    protected $globalLayout;

    function run()
    {        
        $this->fnData['object'] = 'content';
        $this->fnData['has_content'] = 'hasContent';
    }

    function hasContent()
    {
        return $this->content() instanceof eZContentObjectTreeNode;
    }
    
    function content()
    {
        if ( $this->globalLayout == null ){
            $finder = new FindGlobalLayoutOperator();
            $this->globalLayout = $finder->findGlobalLayout( $this->container->getContentNode() );
        }
        return $this->globalLayout;
    }
}