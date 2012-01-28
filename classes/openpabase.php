<?php

class OpenPABase
{
    public static function getIniFileName( $file, $block = 'INISettings', $setting = 'INIFile' )
    {
        $ini = eZINI::instance( $file );
		$fileName = $ini->hasVariable( $block, $setting ) ? $siteIni->variable( $block, $setting ) : false;
		if ( !$fileName )
		{
			return $ini;
		}
		else 
		{
			return $fileName;
		}
    }
    
    public static function getIni( $file, $block = 'INISettings', $setting = 'INIFile' )
    {
        $ini = self::getIniFileName( $file, $block, $setting );
        return eZINI::instance( $ini );
    }
}

?>