<?php

require 'autoload.php';

class OpenPADFSGateway extends ezpDfsPostgresqlClusterGateway
{
    /**
     * @var OpenPADFSFileHandlerDFSDispatcher
     */
    private $dfsBackend;

    public function __construct(array $params = array())
    {
        parent::__construct($params);
        $this->dfsBackend = OpenPADFSFileHandlerDFSDispatcher::build();
    }

    public function passthrough($filepath, $filesize, $offset = false, $length = false)
    {
        $this->dfsBackend->passthrough($filepath, $offset, $length);
    }
}

ezpClusterGateway::setGatewayClass('OpenPADFSGateway');