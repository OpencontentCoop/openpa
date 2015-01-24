<?php

class OpenPABase
{
    const PENDING_ACTION_INDEX_OBJECTS = 'openpa_index_objects';

    const PENDING_ACTION_RENAME_OBJECT = 'openpa_rename_object';

    protected static $cacheNodes = array();

    protected static $sudoFlag = false;

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
        if ( count( $parts ) > 1 )
        {
            if ( in_array( $parts[1], eZLocale::languageList() ) )
            {
                unset( $parts[1] );       
            }
        }
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

    public static function getBackendSiteaccessName()
    {
        $identifier = self::getCurrentSiteaccessIdentifier();
        return $identifier . '_backend';
    }

    public static function getCustomSiteaccessName( $customName )
    {
        $identifier = self::getCurrentSiteaccessIdentifier();
        return $identifier . '_' . strtolower( $customName );
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

    /**
     * @param $nodeID
     *
     * @return eZContentObjectTreeNode
     */
    public static function fetchNode( $nodeID )
    {
        if ( !isset( self::$cacheNodes[$nodeID] ) )
        {
            self::$cacheNodes[$nodeID] = eZContentObjectTreeNode::fetch( $nodeID );
        }
        return self::$cacheNodes[$nodeID];
    }


    /**
     * @param $groupIdentifier
     * @param $stateIdentifiers
     * @return eZContentObjectState[]
     * @throws Exception
     */
    public static function initStateGroup( $groupIdentifier, $stateIdentifiers )
    {
        $states = array();
        $transStates = array();
        foreach( $stateIdentifiers as $key => $state )
        {
            if ( is_string( $key ) )
            {
                $transStates[$key] = $state;
            }
            else
            {
                $transStates[$state] = str_replace( '_', ' ', ucfirst( $state ) );
            }
        }

        $group = array(
            'identifier' => $groupIdentifier,
            'name' => str_replace( '_', ' ', ucfirst( $groupIdentifier ) ),
            'states' => $transStates
        );

        $stateGroup = eZContentObjectStateGroup::fetchByIdentifier( $group['identifier'] );
        if ( !$stateGroup instanceof eZContentObjectStateGroup )
        {
            $stateGroup = new eZContentObjectStateGroup();
            $stateGroup->setAttribute( 'identifier', $group['identifier'] );
            $stateGroup->setAttribute( 'default_language_id', 2 );

            /** @var eZContentObjectStateLanguage[] $translations */
            $translations = $stateGroup->allTranslations();
            foreach( $translations as $translation )
            {
                $translation->setAttribute( 'name', $group['name'] );
                $translation->setAttribute( 'description', $group['name'] );
            }

            $messages = array();
            $isValid = $stateGroup->isValid( $messages );
            if ( !$isValid )
            {
                throw new Exception( implode( ',', $messages ) );
            }
            $stateGroup->store();
        }

        foreach( $group['states'] as $StateIdentifier => $StateName )
        {
            $stateObject = $stateGroup->stateByIdentifier( $StateIdentifier );
            if ( !$stateObject instanceof eZContentObjectState )
            {
                $stateObject = $stateGroup->newState( $StateIdentifier );
                $stateObject->setAttribute( 'default_language_id', 2 );
                /** @var eZContentObjectStateLanguage[] $stateTranslations */
                $stateTranslations = $stateObject->allTranslations();
                foreach( $stateTranslations as $translation )
                {
                    $translation->setAttribute( 'name', $StateName );
                    $translation->setAttribute( 'description', $StateName );
                }
                $messages = array();
                $isValid = $stateObject->isValid( $messages );
                if ( !$isValid )
                {
                    throw new Exception( implode( ',', $messages ) );
                }
                $stateObject->store();
            }
            $id = $group['identifier'] . '.' . $StateIdentifier;
            $states[$id] = $stateObject;
        }
        return $states;
    }

    public static function initSection( $name, $identifier, $navigationPart = 'ezcontentnavigationpart' )
    {
        $section = eZSection::fetchByIdentifier( $identifier, false );
        if ( isset( $section['id'] ) )
        {
            $section = eZSection::fetch( $section['id'] );
        }
        if ( !$section instanceof eZSection )
        {
            $section = new eZSection( array() );
            $section->setAttribute( 'name', $name );
            $section->setAttribute( 'identifier', $identifier );
            $section->setAttribute( 'navigation_part_identifier', $navigationPart );
            $section->store();
        }
        if ( !$section instanceof eZSection )
        {
            throw new Exception( "Section $identifier not found" );
        }
        return $section;
    }

    public static function initRole( $name, $policies )
    {
        $role = eZRole::fetchByName( $name );
        if ( !$role instanceof eZRole )
        {
            $role = eZRole::create( $name );
            $role->store();

            foreach( $policies as $policy )
            {
                $role->appendPolicy( $policy['ModuleName'], $policy['FunctionName'], isset( $policy['Limitation'] ) ? $policy['Limitation'] : array() );
            }
        }
        return $role;
    }

    public static function sudo( Closure $callback )
    {
        if ( self::$sudoFlag === true )
            throw new RuntimeException( "Recursive sudo use detected, abort abort!" );

        self::$sudoFlag = true;

        $loggedUser = eZUser::currentUser();
        $admin = eZUser::fetchByName( 'admin' );
        if ( $admin instanceof eZUser )
        {
            eZUser::setCurrentlyLoggedInUser( $admin, $admin->attribute( 'contentobject_id' ), 1 );
        }
        try
        {
            $returnValue = $callback();
        }
        catch ( Exception $e  )
        {
            self::$sudoFlag = false;
            eZUser::setCurrentlyLoggedInUser( $loggedUser, $loggedUser->attribute( 'contentobject_id' ), 1 );
            throw $e;
        }

        self::$sudoFlag = false;
        eZUser::setCurrentlyLoggedInUser( $loggedUser, $loggedUser->attribute( 'contentobject_id' ), 1 );
        return $returnValue;
    }

}