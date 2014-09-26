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

    final function data()
    {
        $this->data['template'] = $this->template();
        $this->run();
        return $this;
    }

    final function setContainer( OpenPAObjectHandler $handler )
    {
        $this->container = $handler;
    }

    final function setIdentifier( $identifier )
    {
        $this->identifier = $identifier;
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

    function filter( $filterIdentifier, $action )
    {
        return OpenPAObjectHandler::FILTER_CONTINUE;
    }

}