<?php

class OpenPABase
{
    const PENDING_ACTION_INDEX_OBJECTS = 'openpa_index_objects';
    const PENDING_ACTION_RENAME_OBJECT = 'openpa_rename_object';
    
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
        $script = array_shift( $arguments );
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
        array_pop( $parts );
        return implode( '_', $parts );
    }
    
    public static function getFrontendSiteaccessName()
    {
        $identifier = self::getCurrentSiteaccessIdentifier();
        return $identifier . '_frontend';
    }

    public static function getDebugSiteaccessName()
    {
        $identifier = self::getCurrentSiteaccessIdentifier();
        return $identifier . '_debug';
    }
    
    public static function getDataByURL( $url, $justCheckURL = false, $userAgent = false )
    {
        if ( extension_loaded( 'curl' ) )
        {
            $ch = curl_init( $url );
            // Options used to perform in a similar way than PHP's fopen()
            curl_setopt_array(
                $ch,
                array(
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_SSL_VERIFYPEER => false
                )
            );
            if ( $justCheckURL )
            {
                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 2 );
                curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
                curl_setopt( $ch, CURLOPT_NOBODY, 1 );
            }
            else
            {
                curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 1 );
                curl_setopt( $ch, CURLOPT_TIMEOUT, 2 );
            }

            if ( $userAgent )
            {
                curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
            }

            $ini = eZINI::instance();
            $proxy = $ini->hasVariable( 'ProxySettings', 'ProxyServer' ) ? $ini->variable( 'ProxySettings', 'ProxyServer' ) : false;
            // If we should use proxy
            if ( $proxy )
            {
                curl_setopt ( $ch, CURLOPT_PROXY , $proxy );
                $userName = $ini->hasVariable( 'ProxySettings', 'User' ) ? $ini->variable( 'ProxySettings', 'User' ) : false;
                $password = $ini->hasVariable( 'ProxySettings', 'Password' ) ? $ini->variable( 'ProxySettings', 'Password' ) : false;
                if ( $userName )
                {
                    curl_setopt ( $ch, CURLOPT_PROXYUSERPWD, "$userName:$password" );
                }
            }
            // If we should check url without downloading data from it.
            if ( $justCheckURL )
            {
                if ( !curl_exec( $ch ) )
                {
                    curl_close( $ch );
                    return false;
                }

                curl_close( $ch );
                return true;
            }
            // Getting data
            ob_start();
            if ( !curl_exec( $ch ) )
            {
                curl_close( $ch );
                ob_end_clean();
                return false;
            }

            curl_close ( $ch );
            $data = ob_get_contents();
            ob_end_clean();

            return $data;
        }

        return false;
    }

}