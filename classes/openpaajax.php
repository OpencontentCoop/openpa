<?php


class OpenPAAjax extends ezjscServerFunctions
{

    const LOGFILE = 'openpa_ajax.log';
    
    public static function menu( $args )
    {        
        $params = array();				
        
        $http = eZHTTPTool::instance();
        if ( $http->hasPostVariable( 'node_id' ) 
             && $http->hasPostVariable( 'current_node_id' ) 
             && $http->hasPostVariable( 'current_menu' ) 
             && $http->hasPostVariable( 'ui_context' )
             && $http->hasPostVariable( 'is_area_tematica' )	)
        {
            $params['node_id'] = $http->variable( 'node_id' );
            $params['current_node_id'] = $http->variable( 'current_node_id' );
            $params['current_menu'] = $http->variable( 'current_menu' );
            $params['ui_context'] = $http->variable( 'ui_context' );
            $params['is_area_tematica'] = $http->variable( 'is_area_tematica' );
        }
        else
        {
            $eZURI = eZURI::instance();
            $params = $eZURI->userParameters();
        }
        
        $tpl = eZTemplate::factory();
        $tpl->setVariable( "view_parameters", $params );
        $menu = $tpl->fetch("design:menu/ajax_menu.tpl");
        $menu = trim( preg_replace( array('/\s{2,}/', '/[\t\n]/'), ' ', $menu ) );
        echo $menu;
        eZExecution::cleanExit();
    }

    public static function userInfo()
    {
        $user = eZUser::currentUser();
        if ($user->isRegistered() && $user->attribute('login') != 'utente'){
            $sessionKey = 'user_info_' . $user->id();
            if (!eZHTTPTool::instance()->hasSessionVariable($sessionKey)) {
                $accessToDashboard = $user->hasAccessTo('content', 'dashboard');
                $accessToRegister = $user->hasAccessTo('user', 'register');
                eZHTTPTool::instance()->setSessionVariable( $sessionKey, array(
                    'name' => $user->contentObject()->attribute('name'),
                    'has_access_to_dashboard' => $accessToDashboard['accessWord'] == 'yes',
                    'has_access_to_register' => $accessToRegister['accessWord'] != 'no',
                ));
            }

            return eZHTTPTool::instance()->sessionVariable($sessionKey);
        }

        return false;
    }

    public static function loadWebsiteToolbar($args)
    {
        $currentNodeId = $args[0];
        $user = eZUser::currentUser();
        if ($user->isRegistered() && $user->attribute('login') != 'utente'){
            $access = $user->hasAccessTo('websitetoolbar', 'use');
            if ($access['accessWord'] != 'no'){
                $preference = (int)eZPreferences::value('show_editor');
                $sessionKey = 'websitetoolbar_' . $user->id() . '_' . $currentNodeId . '_' . $preference;
                if (isset($args[1])){
                    eZHTTPTool::instance()->removeSessionVariable($sessionKey);
                }
                if (!eZHTTPTool::instance()->hasSessionVariable($sessionKey)) {
                    $tpl = eZTemplate::factory();
                    $tpl->setVariable('current_node_id', $currentNodeId);
                    $tpl->setVariable('show_editor', $preference);
                    $tpl->setVariable('current_user', eZUser::currentUser());
                    $data = $tpl->fetch('design:parts/website_toolbar.tpl');
                    eZHTTPTool::instance()->setSessionVariable($sessionKey, $data);
                }
                $result = eZHTTPTool::instance()->sessionVariable($sessionKey);

                if (in_array('ezformtoken', eZExtension::activeExtensions()) && class_exists('ezxFormToken')){
                    $token = ezxFormToken::getToken();
                    $field = ezxFormToken::FORM_FIELD;
                    $result = preg_replace(
                        '/(<form\W[^>]*\bmethod=(\'|"|)POST(\'|"|)\b[^>]*>)/i',
                        '\\1' . "\n<input type=\"hidden\" name=\"{$field}\" value=\"{$token}\" />\n",
                        $result
                    );
                }

                echo $result;
                if (isset($args[1])) eZDisplayDebug();
                eZExecution::cleanExit();
            }
        }

        return false;
    }

    public static function loadValuation($args)
    {
        $tpl = eZTemplate::factory();
        $tpl->setVariable('node_id', $args[0]);
        echo $tpl->fetch('design:openpa/valuation.tpl');
        if (isset($args[1])) eZDisplayDebug();
        eZExecution::cleanExit();
    }

}
