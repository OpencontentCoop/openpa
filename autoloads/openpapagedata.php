<?php

class OpenPAPageData
{
    public static $contactsMatrixFields = array(
        "Telefono",
        "Fax",
        "Email",
        "PEC",
        "Indirizzo",        
        "Facebook",
        "Twitter",
        "Web",
        "Codice fiscale",
        "Partita IVA",
        "Codice iPA",
        "Via",
        "Numero Civico",
        "CAP",
        "Comune",
    );

    function operatorList()
    {
        return array( 'openpapagedata', 'fill_contacts_matrix', 'contacts_matrix_fields' );
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
            'fill_contacts_matrix' => array(
                'attribute' => array( 'type' => 'object', 'required' => true ),
                'fields' => array( 'type' => 'array', 'required' => false, 'default' => self::$contactsMatrixFields )
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'contacts_matrix_fields':
            {
                $operatorValue = self::$contactsMatrixFields;
            } break;

            case 'fill_contacts_matrix':
            {
                $attribute = $namedParameters['attribute'];
                $fields = $namedParameters['fields'];
                $existingFields = array();
                if ( $attribute instanceof eZContentObjectAttribute )
                {
                    $matrix = $attribute->attribute( 'content' );
                    if ( $attribute->hasContent() )
                    {
                        $rows = $matrix->attribute( 'rows' );                        
                        foreach( $rows['sequential'] as $row )
                        {
                            if ( in_array( $row['columns'][0], $fields ) )
                            {
                                $existingFields[] = $row['columns'][0];
                            }
                        }
                    }
                    foreach( $fields as $field )
                    {
                        if ( !in_array( $field, $existingFields ) )
                        {
                            $matrix->addRow();
                        }
                    }
                    
                    $attribute->setAttribute( 'data_text', $matrix->xmlString() );
                    $matrix->decodeXML( $attribute->attribute( 'data_text' ) );
                    $attribute->setContent( $matrix );
                    $attribute->store();                    
                }
                $operatorValue = $attribute;
            } break;

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
                if (class_exists('OcCrossLogin')){
                    $helper = OcCrossLogin::instance();                    
                    $data['is_login_page'] = $helper->needRedirectionToLoginAccessByModule();
                }
                $data['is_register_page'] = $data['request']['module'] == 'user' && $data['request']['function'] == 'register';
                $data['is_search_page'] = $data['request']['module'] == 'content' && ( $data['request']['function'] == 'search' || $data['request']['function'] == 'advancedsearch' );

                $openPaOperator = new OpenPAOperator();
                $openPaOperatorName = 'get_main_style';
                $openPaOperator->modify( $tpl, $openPaOperatorName, $operatorParameters, $rootNamespace, $currentNamespace, $style, $namedParameters );
                $data['current_theme'] = $style;

                $data['contacts'] = $this->getContactsData();
                
                $pathArray = $data['path_array'];
                $openpaPathArray = array();
                $start = false;
                foreach( $pathArray as $path )
                {
                    if ( isset( $path['node_id'] ) && $path['node_id'] == eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) )
                    {
                        $start = true;
                    }
                    if ( $start )
                    {
                        $openpaPathArray[] = $path;
                    }
                }
                $data['openpa_path_array'] = $openpaPathArray;
                $data['default_path_array'] = $data['path_array'];
                
                $operatorValue = $data;
            }
        }
    }

    function getContactsData()
    {
        $data = array();
        $homePage = OpenPaFunctionCollection::fetchHome();
        if ( $homePage instanceof eZContentObjectTreeNode  )
        {
            $homeObject = $homePage->attribute( 'object' );
            if ( $homeObject instanceof eZContentObject )
            {
                $dataMap = $homeObject->attribute( 'data_map' );
                if ( isset( $dataMap['contacts'] )
                     && $dataMap['contacts'] instanceof eZContentObjectAttribute
                     && $dataMap['contacts']->attribute( 'data_type_string' ) == 'ezmatrix'
                     && $dataMap['contacts']->attribute( 'has_content' ) )
                {
                    $trans = eZCharTransform::instance();
                    $matrix = $dataMap['contacts']->attribute( 'content' )->attribute( 'matrix' );
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
                else
                {
                    if( isset( $dataMap['facebook'] )
                        && $dataMap['facebook'] instanceof eZContentObjectAttribute
                        && $dataMap['facebook']->attribute( 'has_content' ) )
                    {
                        $data['facebook'] = $dataMap['facebook']->toString();
                    }
                    if( isset( $dataMap['twitter'] )
                        && $dataMap['twitter'] instanceof eZContentObjectAttribute
                        && $dataMap['twitter']->attribute( 'has_content' ) )
                    {
                        $data['twitter'] = $dataMap['twitter']->toString();
                    }
                }
            }
        }
        return $data;
    }
}