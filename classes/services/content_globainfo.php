<?php

class ObjectHandlerServiceContentGlobaInfo extends ObjectHandlerServiceBase
{
    protected $globalLayout;

    function run()
    {
        $content = $this->content();
        $this->data['object'] = $content;
        $this->data['has_content'] = $content instanceof eZContentObjectTreeNode;
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