<?php

class OpenPAPageData
{
    private static $openpaContextData;

    private static $areaTematicaNodeIdList;

    function operatorList()
    {
        return array('openpapagedata', 'fill_contacts_matrix', 'contacts_matrix_fields', 'parse_contacts_matrix', 'openpacontext');
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'openpapagedata' => array(
                'params' => array('type' => 'array', 'required' => false, 'default' => array())
            ),
            'fill_contacts_matrix' => array(
                'attribute' => array('type' => 'object', 'required' => true),
                'fields' => array('type' => 'array', 'required' => false, 'default' => OpenPAAttributeContactsHandler::getContactsFields())
            ),
            'parse_contacts_matrix' => array(
                'node' => array('type' => 'object', 'required' => true)
            )
        );
    }

    function modify(eZTemplate $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters)
    {
        switch ($operatorName) {
            case 'parse_contacts_matrix':
                {
                    $operatorValue = $this->getContactsData($namedParameters['node']);
                }
                break;

            case 'openpacontext':
                {
                    if (self::$openpaContextData === null) {
                        $data = array(
                            'current_main_style' => ObjectHandlerServiceContentPageStyle::DEFAULT_STYLE,
                            'reverse_path_id_array' => array(),
                            'path_array' => array(),
                            'is_homepage' => false,
                            'is_area_tematica' => false,
                            'show_breadcrumb' => true,
                            'canonical_url' => false,
                            'ui_context' => false,
                            'css_classes' => 'nosidemenu noextrainfo',
                            'top_menu' => eZINI::instance('menu.ini')->variable('SelectedMenu', 'TopMenu'),
                            'root_node' => (int)eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode'),
                            'top_menu_cache_key' => '',
                            'current_node_id' => null,
                            'logo' => OpenPaFunctionCollection::fetchHeaderLogo(),
                        );

                        $currentModuleParams = $GLOBALS['eZRequestedModuleParams'];
                        $request = array(
                            'module' => $currentModuleParams['module_name'],
                            'function' => $currentModuleParams['function_name'],
                            'parameters' => $currentModuleParams['parameters'],
                        );
                        $data['is_login_page'] = $request['module'] == 'user' && $request['function'] == 'login';
                        if (class_exists('OcCrossLogin') && in_array('occrosslogin', eZExtension::activeExtensions('access'))) {
                            /** @var OcCrossLogin $helper */
                            $helper = OcCrossLogin::instance();
                            $data['is_login_page'] = $helper->needRedirectionToLoginAccessByModule();
                        }
                        $data['is_register_page'] = $request['module'] == 'user' && $request['function'] == 'register';
                        $data['is_search_page'] = $request['module'] == 'content' && ($request['function'] == 'search' || $request['function'] == 'advancedsearch');
                        $data['is_edit'] = $request['module'] == 'content' && $request['function'] == 'edit';
                        $data['is_browse'] = $request['module'] == 'content' && $request['function'] == 'browse';

                        if ($tpl->hasVariable('module_result')) {

                            $moduleResult = $tpl->variable('module_result');

                            if (isset($moduleResult['node_id'])) {
                                $data['current_node_id'] = (int)$moduleResult['node_id'];
                            }

                            $data['ui_context'] = $moduleResult['ui_context'];
                            if (isset($moduleResult['content_info'])) {

                                if (isset($moduleResult['content_info']['main_node_url_alias']) && $moduleResult['content_info']['main_node_url_alias']) {
                                    $data['canonical_url'] = $moduleResult['content_info']['main_node_url_alias'];
                                }
                                if (isset($moduleResult['content_info']['persistent_variable'])
                                    && is_array($moduleResult['content_info']['persistent_variable'])) {
                                    $data = array_merge($data, $moduleResult['content_info']['persistent_variable']);

                                    if (!($data['is_edit'] || $data['is_browse'])) {
                                        if (isset($moduleResult['content_info']['persistent_variable']['left_menu']))
                                            $data['css_classes'] = $moduleResult['content_info']['persistent_variable']['left_menu'] ? ' sidemenu' : 'nosidemenu';
                                        if (isset($moduleResult['content_info']['persistent_variable']['extra_menu']))
                                            $data['css_classes'] .= $moduleResult['content_info']['persistent_variable']['extra_menu'] ? ' extrainfo' : ' noextrainfo';
                                    }
                                }
                            }

                            if (isset($moduleResult['section_id'])) {
                                $data['css_classes'] .= ' section_id_' . $moduleResult['section_id'];
                            }

                            if (OpenPAINI::variable('AreeTematiche', 'UsaStileInMotoreRicerca', false) == 'enabled'
                                && in_array($request['function'], array('search', 'advancedsearch'))) {
                                $areaTematicaNodeIdList = self::getAreaTematicaNodeIdList();
                                $http = eZHTTPTool::instance();
                                if ($http->hasGetVariable('SubTreeArray')) {
                                    $subTreeArray = $http->getVariable('SubTreeArray');
                                    if (count($subTreeArray) == 1 && isset($areaTematicaNodeIdList[$subTreeArray[0]])) {
                                        $data['current_main_style'] = ObjectHandlerServiceControlAreaTematica::compileStyle($areaTematicaNodeIdList[$subTreeArray[0]]['style']);
                                    }
                                }
                            }

                            $path = (isset($moduleResult['path']) && is_array($moduleResult['path'])) ? $moduleResult['path'] : array();
                            $reversePath = array_reverse($path);
                            foreach ($reversePath as $key => $item) {
                                if (isset($item['node_id'])) {
                                    $data['reverse_path_id_array'][] = $item['node_id'];
                                }
                            }
                            foreach ($path as $key => $item) {
                                $data['path_array'][] = $item;
                                if (isset($item['node_id'])) {
                                    $data['css_classes'] .= ' subtree_level_' . $key . '_node_id_' . $item['node_id'];
                                }
                            }

                            foreach ($data['path_array'] as $index => $item) {
                                if (isset($item['node_id'])) {
                                    if ($index <= 2) {
                                        $data['top_menu_cache_key'] .= $item['node_id'] . '-';
                                    }
                                }
                            }
                        }

                        if ($data['is_homepage']
                            || $data['is_search_page']
                            || $data['is_edit']
                            || $data['is_browse']
                            || $data['is_login_page']
                        ) {
                            $data['show_breadcrumb'] = false;
                        }

                        if (isset($data['show_path'])) {
                            $data['show_breadcrumb'] = $data['show_path'];
                        }

                        $data['show_path'] = $data['show_breadcrumb'];

                        $uriPrefix = '/';
                        eZURI::transformURI($uriPrefix);
                        $data['uri_prefix'] = rtrim($uriPrefix, '/') . '/';

                        self::$openpaContextData = $data;

                        eZDebug::appendBottomReport('OpenPA Pagedata', array('OpenPAPageData', 'printDebugReport'));
                    }

                    $operatorValue = self::$openpaContextData;
                }
                break;

            case 'contacts_matrix_fields':
                {
                    $operatorValue = OpenPAAttributeContactsHandler::getContactsFields();
                }
                break;

            case 'fill_contacts_matrix':
                {
                    $attribute = $namedParameters['attribute'];
                    $fields = $namedParameters['fields'];
                    $operatorValue = OpenPAAttributeContactsHandler::fillContactsData($attribute, $fields);
                }
                break;

            case 'openpapagedata':
                {
                    $ezPageData = new eZPageData();
                    $data = array();
                    $ezPageData->modify($tpl, 'ezpagedata', $operatorParameters, $rootNamespace, $currentNamespace, $data, $namedParameters);

                    $data['homepage'] = OpenPaFunctionCollection::fetchHome();

                    if ($data['homepage'] instanceof eZContentObjectTreeNode)
                        $data['is_homepage'] = $data['node_id'] == $data['homepage']->attribute('node_id');
                    else
                        $data['is_homepage'] = $data['node_id'] == eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode');

                    $footerNotes = OpenPaFunctionCollection::fetchFooterNotes();
                    $footerLinks = OpenPaFunctionCollection::fetchFooterLinks();
                    $data['footer'] = array(
                        'notes' => $footerNotes['result'],
                        'links' => $footerLinks['result']
                    );

                    $data['header'] = array(
                        'image' => (array)OpenPaFunctionCollection::fetchHeaderImage(),
                        'logo' => (array)OpenPaFunctionCollection::fetchHeaderLogo(),
                        'links' => array() //@todo
                    );

                    $currentModuleParams = $GLOBALS['eZRequestedModuleParams'];
                    $data['request'] = array(
                        'module' => $currentModuleParams['module_name'],
                        'function' => $currentModuleParams['function_name'],
                        'parameters' => $currentModuleParams['parameters'],
                    );
                    $data['is_login_page'] = $data['request']['module'] == 'user' && $data['request']['function'] == 'login';
                    if (class_exists('OcCrossLogin') && in_array('occrosslogin', eZExtension::activeExtensions('access'))) {
                        /** @var OcCrossLogin $helper */
                        $helper = OcCrossLogin::instance();
                        $data['is_login_page'] = $helper->needRedirectionToLoginAccessByModule();
                    }

                    $data['is_register_page'] = $data['request']['module'] == 'user' && $data['request']['function'] == 'register';
                    $data['is_search_page'] = $data['request']['module'] == 'content' && ($data['request']['function'] == 'search' || $data['request']['function'] == 'advancedsearch');

                    $openPaOperator = new OpenPAOperator();
                    $openPaOperatorName = 'get_main_style';
                    $openPaOperator->modify($tpl, $openPaOperatorName, $operatorParameters, $rootNamespace, $currentNamespace, $style, $namedParameters);
                    $data['current_theme'] = $style;

                    $data['contacts'] = $this->getContactsData();

                    $pathArray = $data['path_array'];
                    $openpaPathArray = array();
                    $start = false;
                    foreach ($pathArray as $path) {
                        if (isset($path['node_id']) && $path['node_id'] == eZINI::instance('content.ini')->variable('NodeSettings', 'RootNode')) {
                            $start = true;
                        }
                        if ($start) {
                            $openpaPathArray[] = $path;
                        }
                    }
                    $data['openpa_path_array'] = $openpaPathArray;
                    $data['default_path_array'] = $data['path_array'];

                    $operatorValue = $data;
                }
        }
    }

    function getContactsData($node = null)
    {
        $data = array();

        if ($node === null) {
            $node = OpenPaFunctionCollection::fetchHome();
        }

        if ($node instanceof eZContentObjectTreeNode) {
            $object = $node->attribute('object');
            if ($object instanceof eZContentObject) {
                /** @var eZContentObjectAttribute[] $dataMap */
                $dataMap = $object->attribute('data_map');
                if (isset($dataMap['contacts'])){
                    $data = OpenPAAttributeContactsHandler::getContactsData($dataMap['contacts']);
                } else {
                    if (isset($dataMap['facebook'])
                        && $dataMap['facebook'] instanceof eZContentObjectAttribute
                        && $dataMap['facebook']->attribute('has_content')) {
                        $data['facebook'] = $dataMap['facebook']->toString();
                    }
                    if (isset($dataMap['twitter'])
                        && $dataMap['twitter'] instanceof eZContentObjectAttribute
                        && $dataMap['twitter']->attribute('has_content')) {
                        $data['twitter'] = $dataMap['twitter']->toString();
                    }
                }
            }
        }
        return $data;
    }

    public static function getAreaTematicaNodeIdList()
    {
        if (self::$areaTematicaNodeIdList === null) {
            self::$areaTematicaNodeIdList = self::getAreaTematicaNodeIdListCache()->processCache(
                function ($file) {
                    $content = include($file);
                    return $content;
                },
                function () {
                    eZDebug::writeNotice("Regenerate area_tematica_node_list cache", 'OpenPAPageData::getAreaTematicaNodeIdList');
                    $list = array();
                    $ini = eZINI::instance('openpa.ini');
                    $areeIdentifiers = $ini->hasVariable('AreeTematiche', 'IdentificatoreAreaTematica') ?
                        $ini->variable('AreeTematiche', 'IdentificatoreAreaTematica') :
                        array('area_tematica');
                    $stileAreaAttribute = $ini->hasVariable('AreeTematiche', 'IdentificatoreStileAreaTematica') ?
                        $ini->variable('AreeTematiche', 'IdentificatoreStileAreaTematica')
                        : 'stile';

                    foreach ($areeIdentifiers as $index => $areaIdentifier){
                        if (!eZContentClass::classIDByIdentifier($areaIdentifier)){
                            unset($areeIdentifiers[$index]);
                        }
                    }

                    if(!empty($areeIdentifiers)) {
                        /** @var eZContentObjectTreeNode[] $nodes */
                        $nodes = eZContentObjectTreeNode::subTreeByNodeID(array(
                            'ClassFilterType' => 'include',
                            'ClassFilterArray' => $areeIdentifiers,
                            'LoadDataMap' => false,
                            'Limitation' => array(),
                        ), 1);
                        foreach ($nodes as $node) {
                            $style = false;
                            $cover = false;
                            $image = false;

                            $dataMap = $node->dataMap();
                            if (isset($dataMap[$stileAreaAttribute]) && $dataMap[$stileAreaAttribute]->hasContent()) {
                                $style = $dataMap[$stileAreaAttribute]->toString();
                            }
                            if (isset($dataMap['cover']) && $dataMap['cover']->hasContent()) {
                                /** @var eZImageAliasHandler $coverAttribute */
                                $coverAttribute = $dataMap['cover']->content();
                                $coverImage = $coverAttribute->attribute('header_area_tematica');
                                $cover = '/' . $coverImage['full_path'];
                            }
                            if (isset($dataMap['image']) && $dataMap['image']->hasContent()) {
                                /** @var eZImageAliasHandler $imageAttribute */
                                $imageAttribute = $dataMap['image']->content();
                                $imageImage = $imageAttribute->attribute('header_area_tematica');
                                $image = '/' . $imageImage['full_path'];
                            }
                            $list[$node->attribute('node_id')] = array(
                                'id' => $node->attribute('contentobject_id'),
                                'node_id' => $node->attribute('node_id'),
                                'style' => $style,
                                'name' => $node->attribute('name'),
                                'class_identifier' => $node->attribute('class_identifier'),
                                'cover_image_url' => $cover,
                                'image_url' => $image,
                            );
                        }
                    }

                    return array(
                        'content' => $list,
                        'scope' => 'cache',
                        'datatype' => 'php',
                        'store' => true
                    );
                }
            );
        }
        return self::$areaTematicaNodeIdList;
    }

    private static function getAreaTematicaNodeIdListCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/pagedata/area_tematica_node_list.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getHeaderLogoStyleCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/pagedata/header_logo_style.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getHeaderLogoCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/pagedata/header_logo.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getHeaderImageStyleCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/pagedata/header_image.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getEntLocaleBackgroundCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/pagedata/ente_locale_background.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getSearchDataCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/ini/search_data.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getSeoCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/openpa/' . 'seo.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    /**
     * @deprecated
     * @return eZClusterFileHandlerInterface
     */
    public static function getGoogleAnalyticsCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/' . 'google_analytics_account_id.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function getThemeIdentifierCache()
    {
        $cacheFilePath = eZSys::cacheDirectory() . '/' . 'openpa/pagedata/theme_identifier.cache';
        return eZClusterFileHandler::instance($cacheFilePath);
    }

    public static function clearCache()
    {
        self::clearOnModifyHomepage();

        self::clearOnModifyAreaTematica();

        self::getSearchDataCache()->delete();
        self::getSearchDataCache()->purge();

        self::getSeoCache()->delete();
        self::getSeoCache()->purge();
    }

    public static function clearOnModifyAreaTematica()
    {
        self::getAreaTematicaNodeIdListCache()->delete();
        self::getAreaTematicaNodeIdListCache()->purge();
    }

    public static function clearOnModifyHomepage()
    {
        self::getHeaderLogoStyleCache()->delete();
        self::getHeaderLogoStyleCache()->purge();

        self::getHeaderLogoCache()->delete();
        self::getHeaderLogoCache()->purge();

        self::getHeaderImageStyleCache()->delete();
        self::getHeaderImageStyleCache()->purge();

        self::getEntLocaleBackgroundCache()->delete();
        self::getEntLocaleBackgroundCache()->purge();
    }

    static public function printDebugReport($as_html = true)
    {
        if (!eZTemplate::isTemplatesUsageStatisticsEnabled())
            return '';

        $stats = '';
        if ($as_html) {
            $stats .= '<h3>OpenPA Pagedata:</h3>';
            $stats .= '<table id="ocpagedata" class="debug_resource_usage">';
            ksort(self::$openpaContextData);
            foreach (self::$openpaContextData as $key => $data) {
                $value = json_encode($data);
                $stats .= "<tr class='data'><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
            }
            $stats .= '</table>';
            $stats .= "<h3>Aree tematiche</h3>";
            $stats .= '<table id="ocareetematiche" class="debug_resource_usage">';
            $stats .= "<tr class='data'><th>Object</th><th>Node</th><th>Class</th><th>Name</th></tr>";
            foreach (self::getAreaTematicaNodeIdList() as $area){
                $stats .= "<tr class='data'><td>" . $area['id'] . "</td><td>" . $area['node_id'] . "</td><td>" . $area['class_identifier'] . "</td><td>" . $area['name'] . "</td></tr>";
            }
            $stats .= "</table>";
        }

        return $stats;
    }
}
