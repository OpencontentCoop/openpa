<?php

class OpenPAPageData
{
    function operatorList()
    {
        return array( 'openpapagedata' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'openpapagedata' => array(
                'params' => array( 'type' => 'array', 'required' => false, 'default' => array() )
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'openpapagedata':
            {
                $ezPageData = new eZPageData();
                $data = array();
                $ezPageData->modify( $tpl, 'ezpagedata', $operatorParameters, $rootNamespace, $currentNamespace, $data, $namedParameters );

                $data['homepage'] = OpenPaFunctionCollection::fetchHome();

                if ( $data['homepage'] instanceof eZContentObjectTreeNode )
                    $data['is_homepage'] = $data['node_id'] == $data['homepage']->attribute( 'node_id' );
                else
                    $data['is_homepage'] = $data['node_id'] == eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );

                $footerNotes = OpenPaFunctionCollection::fetchFooterNotes();
                $footerLinks = OpenPaFunctionCollection::fetchFooterLinks();
                $data['footer'] = array(
                    'notes' => $footerNotes['result'],
                    'links' => $footerLinks['result']
                );

                $data['header'] = array(
                    'image' => (array) OpenPaFunctionCollection::fetchHeaderImage(),
                    'logo' => (array) OpenPaFunctionCollection::fetchHeaderLogo(),
                    'links' => array() //@todo
                );

                $currentModuleParams = $GLOBALS['eZRequestedModuleParams'];
                $data['request'] = array(
                    'module' => $currentModuleParams['module_name'],
                    'function' => $currentModuleParams['function_name'],
                    'parameters' => $currentModuleParams['parameters'],
                );
                $data['is_login_page'] = $data['request']['module'] == 'user' && $data['request']['function'] == 'login';
                $data['is_register_page'] = $data['request']['module'] == 'user' && $data['request']['function'] == 'register';
                $data['is_search_page'] = $data['request']['module'] == 'content' && ( $data['request']['function'] == 'search' || $data['request']['function'] == 'advancedsearch' );

                $openPaOperator = new OpenPAOperator();
                $openPaOperatorName = 'get_main_style';
                $openPaOperator->modify( $tpl, $openPaOperatorName, $operatorParameters, $rootNamespace, $currentNamespace, $style, $namedParameters );
                $data['current_theme'] = $style;

                $operatorValue = $data;
            }
        }
    }
}