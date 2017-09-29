<?php

class DataHandlerOrganigramma implements OpenPADataHandlerInterface
{
    protected $root;

    public function __construct( array $Params )
    {
        $this->root = (int)eZHTTPTool::instance()->getVariable( 'root', null );
    }

    public function getData()
    {
        return OpenPAOrganigrammaTools::instance()->tree($this->root);
    }
}
