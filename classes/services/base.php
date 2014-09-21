<?php

abstract class ObjectHandlerServiceBase extends OpenPATempletizable implements OpenPAObjectHandlerServiceInterface
{
    /**
     * @var OpenPAObjectHandler
     */
    protected $container;

    /**
     * @var string
     */
    protected $identifier;

    function __construct( $data = array() )
    {
        $this->data = $data;
    }

    function data()
    {
        $this->run();
        return $this;
    }

    function setContainer( OpenPAObjectHandler $handler )
    {
        $this->container = $handler;
    }

    function setIdentifier( $identifier )
    {
        $this->identifier = $identifier;
        $this->data['template'] = $this->template();
    }

    function template()
    {
        $currentErrorReporting = error_reporting();
        error_reporting( 0 );
        $templateUri = "design:openpa/services/{$this->identifier}.tpl";
        $tpl = eZTemplate::factory();
        $result = $tpl->loadURIRoot( $templateUri, false, $extraParameters );
        error_reporting( $currentErrorReporting );
        return $result ? $templateUri : false;
    }

}