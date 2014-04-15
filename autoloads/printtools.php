<?php

class PrintToolsOperator
{
    static $Operators = array(
        'query_string'
    );
    
    function FindGlobalLayoutOperator()
    {
    }
    
    function operatorList()
    {
        return self::$Operators;
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array();
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {		
        switch ( $operatorName )
        {
            case 'query_string':
            {
                $result = '';                
                if ( count( $_GET ) > 0 )
                {
                    $result = '?';   
                    foreach( $_GET as $key => $value )
                    {
                        if ( !empty( $value ) )
                        {
                            if ( is_array( $value ) )
                            {
                                foreach( $value as $subKey => $subValue )
                                {
                                    if ( !empty( $subValue ) )
                                    {
                                        if ( is_numeric( $subKey ) )
                                        {
                                            $subKey = '';
                                        }
                                        $result .= "{$key}[{$subKey}]={$subValue}&";
                                    }
                                }
                            }
                            else
                            {
                                $result .= "$key=$value&";
                            }
                        }
                    }
                    $result = rtrim( $result, '&' );
                    if( $result == '?' )
                        $result = '';
                }
                return $operatorValue .= $result;
                
            }
        }
    }
    

}

?>