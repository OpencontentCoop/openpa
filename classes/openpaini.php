<?php

class OpenPAINI
{
    protected static $filters = array(
        'TopMenu::NodiCustomMenu',
        //'SideMenu::EsponiLink'
    );
    
    public static function variable( $block, $value, $default )
    {
        if ( self::hasFilter( $block, $value, $default ) )
        {
            return self::filter( $block, $value, $default );
        }
        
        $ini = eZINI::instance( 'openpa.ini' );
        $result = $default;
        if ( $ini->hasVariable( $block, $value ) )
        {
            $result = $ini->variable( $block, $value );
        }
        return $result;        
    }
    
    protected static function hasFilter( $block, $value, $default )
    {
        if ( in_array( $block . '::' . $value, self::$filters )
             && self::filter( $block, $value, $default ) !== null )
        {
            return true;
        }
    }
    
    protected static function filter( $block, $value, $default )
    {
        $filter = $block . '::' . $value;
        switch( $filter )
        {
            case 'TopMenu::NodiCustomMenu':
                return OpenPaFunctionCollection::fetchTopMenuNodes();              
            break;
        
            //case 'SideMenu::EsponiLink':
            //    $result = eZINI::instance( 'openpa.ini' )->hasVariable( $block, $value ) ? eZINI::instance( 'openpa.ini' )->variable( $block, $value ) : $default;
            //    if ( !eZUser::currentUser()->isAnonymous() )
            //    {
            //        $result = false;
            //    }
            //    return $result;
            //break;
        
            default:
                return null;
        }
    }
}