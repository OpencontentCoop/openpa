<?php

class CookieOperator
{

    function __construct()
    {
        $this->Operators= array( 'cookieset', 'cookieget', 'check_and_set_cookies' );
    }

    function operatorList()
    {
        return $this->Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'cookieset' => array( 
                'cookie_name' 	=> array( 'type' 	=> 'string',	'required' => true ),
                'cookie_val' 	=> array( 'type'	=> 'string', 	'required' => true ),
                'expiry_time' 	=> array( 'type'	=> 'string',    'required' => false, 'default' => '0' )
            ),                
            'cookieget' => array(
				'cookie_name' 	=> array( 'type' 	=> 'string',	'required' => true )
			),
            'check_default_cookies' => array()
        );
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        $key = isset( $namedParameters['cookie_name'] ) ? $namedParameters['cookie_name'] : false;
				
		$ini = eZINI::instance( 'cookieoperator.ini' );
		$prefix = $ini->variable( 'CookiesSettings', 'CookieKeyPrefix' );

		$key = "{$prefix}{$key}";
		
        switch ( $operatorName )
        {
			// Set a cookie value:
            case 'cookieset':
            {
				// Get our parameters:
				$value = $namedParameters['cookie_val'];
				$expire = $namedParameters['expiry_time'];
				
				// Check and calculate the expiry time:
				if ( $expire > 0 )
				{
					// It is a number of days:
					$expire = time()+60*60*24*$expire; 
				}
				setcookie( $key, $value, $expire, '/' );
				eZDebug::writeDebug( 'setcookie('. $key .', '. $value .', '. $expire .', "/")', __METHOD__ );
				$operatorValue = false;
				return;
                
            } break;
			
            // get a cookie value:
            case 'cookieget':
            {
				$operatorValue = false;

				// if it's set then return it:
				// else return false:
				if( isset( $_COOKIE[$key] ) )	
					$operatorValue = $_COOKIE[$key];
				
                return;
            } break;
            
            case 'check_and_set_cookies':
            {
                $http = eZHTTPTool::instance();
                $return = array();
                if ( $ini->hasVariable( 'Cookies', 'Cookies' ) )
                {
                    $cookies = $ini->variable( 'Cookies', 'Cookies' );
                    foreach( $cookies as $key )
                    {
                        $_key = "{$prefix}{$key}";
                        $default = isset( $_COOKIE[ $_key ] ) ? $_COOKIE[ $_key ] : $ini->variable( $key, 'Default' );
                        $value = $http->variable( $key, $default ); 
                        setcookie( $_key, $value, time()+60*60*24*365, '/' );
                        $return[$key] = $value;
                    }
                    
                }
                $operatorValue = $return;
            } break;
        }
    }
}

?>
