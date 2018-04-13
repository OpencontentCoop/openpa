<?php

class ObjectHandlerServiceControlAreaTematica extends ObjectHandlerServiceBase
{
    private static $areaTematicaNodes = array();

    function __construct( $data = array() )
    {
        parent::__construct($data);
        self::$areaTematicaNodes = OpenPAPageData::getAreaTematicaNodeIdList();
    }

    function run()
    {
        $this->fnData['area_tematica'] = 'getAreaTematicaNode';
        $this->fnData['is_area_tematica'] = 'getAreaTematicaNode';
        $this->fnData['area_tematica_style'] = 'getAreaTematicaStyle';
        $this->fnData['area_tematica_css_file'] = 'getAreaTematicaCssFile';
        $this->fnData['area_tematica_cover_image'] = 'getAreaTematicaCoverImage';
        $this->fnData['area_tematica_image'] = 'getAreaTematicaImage';
    }

    protected function getAreaTematicaNode()
    {
        foreach ($this->container->fullCurrentPathNodeIds as $nodeId) {
            if (in_array($nodeId, array_keys(self::$areaTematicaNodes))) {
                return eZContentObjectTreeNode::fetch((int)self::$areaTematicaNodes[$nodeId]['node_id']);
            }
        }

        return false;
    }

    protected function getAreaTematicaStyle()
    {
        foreach ($this->container->fullCurrentPathNodeIds as $nodeId) {
            if (in_array($nodeId, array_keys(self::$areaTematicaNodes))) {
                return self::compileStyle(self::$areaTematicaNodes[$nodeId]['style']);
            }
        }

        return false;
    }

    protected function getAreaTematicaCssFile()
    {
        foreach ($this->container->fullCurrentPathNodeIds as $nodeId) {
            if (in_array($nodeId, array_keys(self::$areaTematicaNodes))) {
                $style = self::$areaTematicaNodes[$nodeId]['style'];
                if (!empty($style )) {
                    return 'aree/' . $style . '.css';
                }
            }
        }

        return false;
    }

    protected function getAreaTematicaCoverImage()
    {
        foreach ($this->container->fullCurrentPathNodeIds as $nodeId) {
            if (in_array($nodeId, array_keys(self::$areaTematicaNodes))) {
                return self::$areaTematicaNodes[$nodeId]['cover_image_url'];
            }
        }

        return false;
    }

    protected function getAreaTematicaImage()
    {
        foreach ($this->container->fullCurrentPathNodeIds as $nodeId) {
            if (in_array($nodeId, array_keys(self::$areaTematicaNodes))) {
                return self::$areaTematicaNodes[$nodeId]['image_url'];
            }
        }

        return false;
    }

    public static function compileStyle($style)
    {
        $areaStyle = array(
            'aree-tematiche',
            'area_tematica',
            $style
        );
        return implode(' ', $areaStyle);
    }
}
