<?php

class OpenPAMenuOperator
{
    public $Operators;
    
    private static $_area_tematica_node = array();
    
    private static $_cache = array();
    
    function OpenPAMenuOperator()
    {
        $this->Operators= array( 'top_menu_cached', 'left_menu_cached', 'tree_menu' );
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
            'top_menu_cached' => array
            (
                'parameters' => array( "type" => "array", "required" => false, "default" => array() )
            ),
            'left_menu_cached' => array
            (
                'parameters' => array( "type" => "array", "required" => false, "default" => array() )
            ),
            'tree_menu' => array(
                'parameters' => array( "type" => "array", "required" => true, "default" => array() )
            )
        );
    }
    
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {		
        $cacheKey = md5( serialize( $namedParameters['parameters'] ) );
        switch ( $operatorName )
        {            
            case 'top_menu_cached':
            {
                if ( !isset( self::$_cache[$cacheKey] ) )
                {
                    self::$_cache[$cacheKey] = OpenPAMenuTool::getTopMenu( $namedParameters['parameters'] );
                }
                return $operatorValue = self::$_cache[$cacheKey];
            } break;
            
            case 'left_menu_cached':
            {
                if ( !isset( self::$_cache[$cacheKey] ) )
                {
                    self::$_cache[$cacheKey] = OpenPAMenuTool::getLeftMenu( $namedParameters['parameters'] );
                }
                return $operatorValue = self::$_cache[$cacheKey];                
            } break;

            case 'tree_menu':
            {
                if ( !isset( self::$_cache[$cacheKey] ) )
                {
                    self::$_cache[$cacheKey] = OpenPAMenuTool::getTreeMenu( $namedParameters['parameters'] );
                }
                return $operatorValue = self::$_cache[$cacheKey];                
            } break;
        }
        return false;
    }
}

?>