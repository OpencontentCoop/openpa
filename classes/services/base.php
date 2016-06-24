<?php

abstract class ObjectHandlerServiceBase extends OpenPATempletizable implements OpenPAObjectHandlerServiceInterface
{
    /**
     * @var OpenPAObjectHandler
     */
    protected $container;
    
    protected $attributeCaches = array();

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
        eZDebugSetting::writeDebug( 'openpa-services', "Load service {$this->identifier}", $this->container->currentObjectId . ' - ' . get_called_class());
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
    
    public function attribute( $key )
    {
        if (!isset( $this->attributeCaches[$key] ))
        {
            if ( isset( $this->data[$key] ) )
            {
                eZDebugSetting::writeDebug( 'openpa-services', "{$this->identifier}.{$key}", $this->container->currentObjectId . ' - ' . get_called_class());
                $this->attributeCaches[$key] = $this->data[$key];
            }
            elseif ( isset( $this->fnData[$key] ) )
            {
                eZDebugSetting::writeDebug( 'openpa-services', "(function) {$this->identifier}.{$key}", $this->container->currentObjectId . ' - ' . get_called_class());
                $this->attributeCaches[$key] = call_user_func( array( $this, $this->fnData[$key] ) );
                //return $this->{$this->fnData[$key]}();
            }
            else
            {
                eZDebug::writeNotice( "Attribute $key does not exist", get_called_class() );
                $this->attributeCaches[$key] = false;    
            }            
        }
        
        return $this->attributeCaches[$key];
    }

}