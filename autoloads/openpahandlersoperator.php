<?php

class OpenPAHandlerOperators
{
    function operatorList()
    {
        return array( 'object_handler' );
    }

    function namedParameterPerOperator()
    {
        return true;
    }

    function namedParameterList()
    {
        return array(
            'object_handler' => array(
                'node' => array( 'type' => 'object', 'required' => true, 'default' => false )
            ),
        );
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters )
    {
        switch ( $operatorName )
        {
            case 'object_handler':
            {
                $node = null;
                if ( is_object( $namedParameters['node'] ) )
                {
                    $node = $namedParameters['node'];
                }
                elseif( is_object( $operatorValue ) )
                {
                    $node = $operatorValue;
                }
                $operatorValue = OpenPAObjectHandler::instanceFromObject( $node );
            }
        }
    }
}