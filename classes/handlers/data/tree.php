<?php

class DataHandlerTree implements OpenPADataHandlerInterface
{
    protected $parent;
    
    public function __construct( array $Params )
    {
        $this->parent = (int)eZHTTPTool::instance()->getVariable( 'parent', null );
    }

    public function getData()
    {
        $data = array();
        if ( $this->parent > 0 ){
            $node = eZContentObjectTreeNode::fetch( $this->parent );
            if ( $node instanceof eZContentObjectTreeNode )
            {
                if ( $node->childrenCount() > 0 )
                {
                    $data = eZFlowAjaxContent::nodeEncode( $node->children(), array( 'fetchChildrenCount' => true, 'dataMap' => array( 'file' ) ), false );
                }
            }
        }
        return $data;
    }
}
