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
		$ret["html"] = $tpl->fetch("design:menu/ajax_menu.tpl");
		return $ret;

    }

}
?>
