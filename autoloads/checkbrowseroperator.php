<?php

include_once( "extension/openpa/lib/browser_detection.php" );
class CheckbrowserOperator
{

    function CheckbrowserOperator()
    {
        $this->Operators= array( 'checkbrowser', 'is_deprecated_browser' );
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
        
            'checkbrowser' => array(),
            'is_deprecated_browser' => array(
                'browser_array' => array( 'type' => 'array', 'required' => true )
            )
        );
    }

    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {

        switch ( $operatorName )
        {
            case 'checkbrowser':
            {
				$full = browser_detection( 'full_assoc', 2 );
				$operatorValue = $full;
            } break;
            
            case 'is_deprecated_browser':
            {
                $browser = $namedParameters['browser_array'];
				if ( $browser['browser_working'] == 'ie'
                    && $browser['browser_number'] > '7.0' )
                {
                    $operatorValue = true;
                }
                $operatorValue = false;
            } break;
            
        }
    }
}

?>
