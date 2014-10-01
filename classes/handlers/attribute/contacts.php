<?php

class OpenPAAttributeContactsHandler extends OpenPAAttributeHandler
{
    public function __construct( eZContentObjectAttribute $attribute, $params = array() )
    {
        parent::__construct( $attribute, $params );
        $this->data['data'] = $this->getContactsData();
    }

    protected  function getContactsData()
    {
        $data = array();
        $trans = eZCharTransform::instance();
        if ( $this->data['contentobject_attribute']->attribute( 'has_content' ) )
        {
            $matrix = $this->data['contentobject_attribute']->attribute( 'content' )->attribute( 'matrix' );
            foreach( $matrix['rows']['sequential'] as $row )
            {
                $columns = $row['columns'];
                $name = $columns[0];
                $identifier = $trans->transformByGroup( $name, 'identifier' );
                if ( !empty( $columns[1] ) )
                {
                    $data[$identifier] = $columns[1];
                }
            }
        }
        return $data;
    }
}