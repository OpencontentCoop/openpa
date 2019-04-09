<?php


class ObjectHandlerServiceContentPageStyle extends ObjectHandlerServiceBase
{
    const DEFAULT_STYLE = 'no-main-style';

    private $mainStyle;

    function run()
    {
        $this->fnData['main_style'] = 'getMainStyle';
    }

    public function getMainStyle()
    {
        if ($this->mainStyle === null) {
            $path = eZPageData::getNodePath($this->container->currentNodeId);
            if ($path){
                $this->mainStyle = self::getStyleFromPath($path);
            }
        }

        return $this->mainStyle;
    }

    public function getStyleFromPath($path)
    {
        $style = self::DEFAULT_STYLE;

        $mainStylesTmp = OpenPAINI::variable('Stili', 'Nodo_NomeStile', array());
        $mainStyles = array();

        foreach ($mainStylesTmp as $styleParts) {
            $nodeStyle = explode(';', $styleParts);
            if (isset($nodeStyle[1])) {
                $mainStyles[$nodeStyle[0]] = $nodeStyle[1];
            }
        }

        foreach ($path['path'] as $key => $item) {
            if (isset($item['node_id'])) {

                if (isset($mainStyles[$item['node_id']])) {
                    $style = $mainStyles[$item['node_id']];
                }
            }
        }

        $controlAreaTematica = $this->container->service('control_area_tematica');
        if ($controlAreaTematica) {
            $areaStyle = $controlAreaTematica->attribute('area_tematica_style');
            if ($areaStyle) {
                $style = $areaStyle;
            }
        }

        return $style;
    }
}
