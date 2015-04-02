<?php

class DataHandlerMapMarkers implements OpenPADataHandlerInterface
{
    public $contentType = 'geojson';
    
    public function __construct( array $Params )
    {
        $this->contentType = eZHTTPTool::instance()->getVariable( 'contentType', $this->contentType );
    }

    public function getData()
    {
        if ( $this->contentType == 'geojson' )
        {
            $parentNode = eZHTTPTool::instance()->getVariable( 'parentNode', 0 );        
            $data = new DataHandlerMapMarkersGeoJsonFeatureCollection();
            if ( $parentNode > 0 )
            {
                $result = false;
                
                $parentObject = eZContentObject::fetchByNodeID( $parentNode );
                if ( $parentObject instanceof eZContentObject )
                {
                    $openpa = OpenPAObjectHandler::instanceFromObject( $parentObject );
                    if ( $openpa instanceof OpenPAObjectHandler && $openpa->hasAttribute( 'content_virtual' ) && $openpa->attribute( 'content_virtual' )->attribute( 'folder' ) != false )
                    {
                        $values = $openpa->attribute( 'content_virtual' )->attribute( 'folder' );                        
                        $result = eZFunctionHandler::execute( 'openpa', 'map_markers', array( 'parent_node_id' => $values['subtree'][0], 'class_identifiers' => $values['classes'] ) );                        
                    }
                }
                
                if ( $result == false )
                {
                    $classIdentifiers = explode( ',', eZHTTPTool::instance()->getVariable( 'classIdentifiers', array() ) );                                
                    $result = eZFunctionHandler::execute( 'openpa', 'map_markers', array( 'parent_node_id' => $parentNode, 'class_identifiers' => $classIdentifiers ) );
                }
                foreach( $result as $item )
                {
                    $properties = array(
                        'type' => isset( $item['type'] ) ? $item['type'] : '',
                        'name' => $item['title'],
                        'url' => $item['urlAlias'],
                        'popupContent' => '<em>Loading...</em>'
                    );
                    $feature = new DataHandlerMapMarkersGeoJsonFeature( $item['id'], array( $item['lng'], $item['lat'] ), $properties );
                    $data->add( $feature );
                }
            }
        }
        elseif ( $this->contentType == 'marker' )
        {
            $view = eZHTTPTool::instance()->getVariable( 'view', 'panel' );
            $id = eZHTTPTool::instance()->getVariable( 'id', 0 );
            $object = eZContentObject::fetch( $id );
            if ( $object instanceof eZContentObject && $object->attribute( 'can_read' ) )
            {
                $tpl = eZTemplate::factory();
                $tpl->setVariable( 'object', $object );
                $tpl->setVariable( 'node', $object->attribute( 'main_node' ) );
                $result = $tpl->fetch( 'design:node/view/' . $view . '.tpl' );
                $data = array( 'content' => $result );
            }
            else
            {
                $data = array( 'content' => '<em>Private</em>' );
            }
        }
        return $data;
    }
}

class DataHandlerMapMarkersGeoJsonFeatureCollection
{
    public $type = 'FeatureCollection';
    public $features = array();
    
    public function add( DataHandlerMapMarkersGeoJsonFeature $feature )
    {
        $this->features[] = $feature;
    }
}

class DataHandlerMapMarkersGeoJsonFeature
{
    public $type = "Feature";
    public $id;
    public $properties;
    public $geometry;
    
    public function __construct( $id, array $geometryArray, array $properties )    
    {
        $this->id = $id;
        
        $this->geometry = new DataHandlerMapMarkersGeoJsonGeometry();
        $this->geometry->coordinates = $geometryArray;
        
        $this->properties = new DataHandlerMapMarkersGeoJsonProperties( $properties );        
    }
}

class DataHandlerMapMarkersGeoJsonGeometry
{
    public $type = "Point";
    public $coordinates;
}

class DataHandlerMapMarkersGeoJsonProperties
{
    public function __construct( array $properties = array() )
    {
        foreach( $properties as $key => $value )
        {
            $this->{$key} = $value;
        }
    }
}