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
        switch ( $operatorName )
        {
            case 'top_menu_cached':
            {
                return $operatorValue = OpenPAMenuTool::getTopMenu( $namedParameters['parameters'] );
            } break;
            
            case 'left_menu_cached':
            {
                return $operatorValue = OpenPAMenuTool::getLeftMenu( $namedParameters['parameters'] );
            } break;

            case 'tree_menu':
            {
                return $operatorValue = OpenPAMenuTool::getTreeMenu( $namedParameters['parameters'] );
            } break;
        }
        return false;
    }
}

?>