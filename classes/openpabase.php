<?php

class OpenPABase
{
    public static function getIniFileName( $file, $block = 'INISettings', $setting = 'INIFile' )
    {
        $ini = eZINI::instance( $file );
		$fileName = $ini->hasVariable( $block, $setting ) ? $ini->variable( $block, $setting ) : false;
		if ( !$fileName )
		{
			return $file;
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
    
    /*
     * Restituisce l'elenco dei siteaccess di debug|frontend|backend delle istanze attive.
     * Questa funzione è utile per generare script cli che lavorino su tutte le istanze
     * 
     * @var string $siteaccessType debug|frontend|backend
     * @return array Lista dei siteaccess di $siteaccessType
     */
    public static function getInstances( $siteaccessType = 'frontend' )
    {
        if ( !in_array( $siteaccessType, array( 'debug', 'frontend', 'backend' ) ) )
        {
            throw new Exception( "Tipo di siteaccess $siteaccessType non ammesso" );
        }
        $fileList = array();
        eZDir::recursiveList( 'settings/siteaccess', 'settings/siteaccess', $fileList );
        $siteaccess = array();
        foreach( $fileList as $file )
        {
            if ( $file['type'] == 'dir' && strpos( $file['name'], '_' . $siteaccessType ) !== false )
            {
                $siteaccess[$file['name']] = $file['name'];
            }
        }
        array_unique( $siteaccess );
        sort( $siteaccess );
        return $siteaccess;
    }
    
    public static function getOpenPAScriptArguments( $exclude = false )
    {
        $arguments = $GLOBALS['argv'];
        $program = $arguments[0];
        array_shift( $arguments );
        foreach( $arguments as $i => $argument )
        {
            if ( strpos( $argument, '-s' ) !== false )
            {
                unset( $arguments[$i] );
            }
            if ( $exclude && strpos( $argument, $exclude ) !== false )
            {
                unset( $arguments[$i] );
            }
        }
        return $arguments;
    }
    
    public static function getCurrentSiteaccessIdentifier()
    {
        $currentSiteaccess = eZSiteAccess::current();
        $parts = explode( '_', $currentSiteaccess['name'] );
        $identifier = array_shift( $parts );
        if ( $identifier )
        {
            return $identifier;
        }
        return $currentSiteaccess['name'];
    }

}

?>
