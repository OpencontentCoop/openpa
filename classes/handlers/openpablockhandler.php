<?php


class OpenPABlockHandler extends OpenPATempletizable
{
    protected $currentBlock;

    protected $currentCustomAttributes;

    public function __construct( eZPageBlock $block, $params = array() )
    {
        $this->currentBlock = $block;
        $this->currentCustomAttributes = $block->attribute( 'custom_attributes' );
        $this->data['page_block'] = $this->currentBlock;
        $this->run();
    }

    protected function run()
    {
        return false;
    }

    protected function getFetchParameters()
    {
        return false;
    }
}