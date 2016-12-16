<?php

class OpenPAInstance
{
    const PRODUCTION = 'production';

    const STAGING = 'staging';

    const DEVELOPMENT = 'development';

    /**
     * IP delle macchine di produzione
     *
     * @var array
     */
    private static $validIps = array(
        '84.18.151.65', //proxy Riva del Garda
        '194.105.50.4', //consorzio-web
        '194.105.50.2' //consorzio-varnish
    );

    /**
     * Il file site.ini del siteaccess interrogato
     * @var eZINI
     */
    protected $siteIni;

    /**
     * Il file openpa.ini del siteaccess interrogato
     * @var eZINI
     */
    protected $openpaIni;

    protected $solrIni;

    /**
     * Il nome del siteAccess interrogato
     * @var $currentSiteAccessName
     */
    protected $currentSiteAccessName;

    public function __construct( $siteAccessName )
    {
        if ( empty( $siteAccessName ) )
            throw new Exception( "SiteAccess name not found" );
        $this->currentSiteAccessName = $siteAccessName;
    }

    protected function getSiteIni( $block, $value = null )
    {
        if ( !$this->siteIni instanceof eZINI )
            $this->siteIni = eZSiteAccess::getIni( $this->currentSiteAccessName );
        if ( $value )
            return $this->siteIni->hasVariable( $block, $value ) ? $this->siteIni->variable( $block, $value ) : null;
        else
            return $this->siteIni->hasGroup( $block ) ? $this->siteIni->group( $block ) : array();
    }

    protected function getOpenpaIni( $block, $value )
    {
        if ( !$this->openpaIni instanceof eZINI )
            $this->openpaIni = eZSiteAccess::getIni( $this->currentSiteAccessName, 'openpa.ini' );
        return $this->openpaIni->hasVariable( $block, $value ) ? $this->openpaIni->variable( $block, $value ) : null;
    }

    protected function getSolrIni( $block, $value )
    {
        if ( !$this->solrIni instanceof eZINI )
            $this->solrIni = eZSiteAccess::getIni( $this->currentSiteAccessName, 'solr.ini' );
        return $this->solrIni->hasVariable( $block, $value ) ? $this->solrIni->variable( $block, $value ) : null;
    }

    public static function current()
    {
        $currentSiteaccess = eZSiteAccess::current();
        return new self( $currentSiteaccess['name'] );
    }

    public function getIdentifier()
    {
        return OpenPABase::getSiteaccessIdentifier( $this->currentSiteAccessName );
    }

    public function getSiteAccessName()
    {
        return $this->currentSiteAccessName;
    }

    private static function getIP( $url )
    {
        $dns = dns_get_record( $url );
        $ip = false;
        foreach( $dns as $dnsItem )
        {
            if ( isset( $dnsItem['type'] ) && $dnsItem['type'] == 'A' )
            {
                $ip = $dnsItem['ip'];
            }
            elseif ( isset( $dnsItem['type'] ) && $dnsItem['type'] == 'CNAME' )
            {
                $ip = self::getIP( $dnsItem['target'] );
            }
        }
        return $ip;
    }

    /**
     * Confronta l'url del site.ini e verifica che non contenga "opencontent" e che rientri nella lista degli Ip di produzione
     * @return bool
     */
    public function isLive()
    {
        $url = $this->getUrl( self::PRODUCTION );
        if ( stripos( $url, 'opencontent' ) === false )
        {
            $url = rtrim( $url, '/' );
            $url = str_replace( 'http://', '', $url );
            $ip = self::getIP( $url );
            return in_array( $ip, self::$validIps );
        }
        return false;
    }

    /**
     * Restituisce il SiteSettings.SiteName
     * @return string
     */
    public function getName()
    {
        return $this->getSiteIni( 'SiteSettings', 'SiteName' );
    }

    /**
     * Restituisce il SiteSettings.SiteURL per la produzione o il nome convenzionale del dominio opencontent
     * @param $type string valori possibili production|staging|development
     *
     * @return string
     */
    public function getUrl( $type )
    {
        switch( $type )
        {
            case self::PRODUCTION:
                return 'http://' .  $this->getSiteIni( 'SiteSettings', 'SiteURL' );
        }
        return 'http://' . $this->getSiteAccessBaseName() . '.opencontent.it';
    }

    /**
     * Restituisce la data di modifica del file site.ini
     * @todo Come calcolare la data di creazione?
     * @param $format string Valore formato data gestibile da date()
     * @return string
     */
    public function getProductionDate( $format = 'd/m/Y' )
    {
        return date( $format, filemtime( 'settings/siteaccess/' . $this->currentSiteAccessName . '/site.ini.append.php' ) );
    }

    /**
     * Restituisce openpa.ini[Seo]GoogleAnalyticsAccountID
     * @return string
     */
    public function getGoogleId()
    {
        return $this->getOpenpaIni( 'Seo', 'GoogleAnalyticsAccountID' );
    }

    /**
     * Restituisce il nome del siteaccess epurato dal suffisso (ala_backend -> ala)
     * @return string
     */
    public function getSiteAccessBaseName()
    {
        return $this->getIdentifier();
    }
        
    public function getType()
    {
        $type = 'altro';
        $suffix = '_standard';
        if ( in_array( 'openpa_flight', $this->getSiteIni( 'DesignSettings', 'AdditionalSiteDesignList' ) ) )
        {
            $suffix = '_new_design';
        }
        
        if ( strpos( $this->currentSiteAccessName, '_sensor' ) !== false )
        {
            $type = 'sensor';
        }
        elseif ( strpos( $this->currentSiteAccessName, '_dimmi' ) !== false )
        {
            $type = 'dimmi';
        }
        elseif ( in_array( 'fusioni', $this->getSiteIni( 'DesignSettings', 'AdditionalSiteDesignList' ) ) )
        {
            $type = 'fusione';
        }
        elseif ( strpos( $this->getSiteIni( 'SiteSettings', 'SiteName' ), 'Comune' ) !== false )
        {
            $type = 'comune';
        }        
        
        return $type . $suffix;
    }

    /**
     * Ritorna l'istanza in formato wiki table row
     * @param string $index Indice della riga
     * @param bool $returnHeaders Restituisce gli headers
     *
     * @return string
     */
    public function toWikiTableRow( $index = '', $returnHeaders = false )
    {
        $isValid = ( $this->isLive() == true ) ? '[[span(style=color: #FF0000, si )]]' : 'no';
        $seo = $this->getGoogleId() != '' ? '{{{' . $this->getGoogleId() . '}}}' : '?';

        $data = array(
            '=N='                       => $index,
            '=Identificatore='          => $this->getSiteAccessBaseName(),
            '=Tipologia='               => $this->getType(),
            '=Ente='                    => $this->getName(),
            '=Dominio di produzione='   => $this->isLive() ? "'''" . $this->getUrl( self::PRODUCTION ) . "'''" : $this->getUrl( self::PRODUCTION ),
            '=Dominio di staging='      => $this->isLive() ? $this->getUrl( self::STAGING ) : "'''" . $this->getUrl( self::STAGING ) . "'''",
            '=Data='                    => $this->getProductionDate(),
            '=Live='                    => $isValid,
            '=GoogleID='                => $seo,
        );

        if ( $returnHeaders )
        {
            $toImplode = array_keys( $data );
        }
        else
        {
            $toImplode = array_values( $data );
        }

        $string = '||'. implode( '||', $toImplode ) . '||';

        return $string;
    }

    /**
     * Restituisce un set di valori in formato hash usato poi da instances.yml
     * @todo Astrarre formato hash con OCSiteInstanceInterface o simili
     * @return array
     */
    public function toHash()
    {
        return array(
            'name' => $this->getName(),
            'url' => $this->getUrl( self::PRODUCTION ),
            'url_staging' => $this->getUrl( self::STAGING ),
            'production_date' => $this->getProductionDate(),
            'google_id' => $this->getGoogleId()
        );
    }

    /**
     * @todo Astrarre formato hash con OCSiteInstanceInterface o simili
     * @see self::toHash
     * @param string $instanceName
     * @param array $compareValues
     *
     * @throws Exception
     */
    public static function compare( $instanceName, $compareValues )
    {
        $silentErrorKeys = array( 'production_date' );
        $errors = array();
        $instance = new self( $instanceName . '_frontend' );
        $liveData = $instance->toHash();
        foreach( $compareValues as $name => $value )
        {
            if ( !isset( $liveData[$name] ) )
            {
                $errors[] = "Il valore '$name' non esiste nell'installazione corrente";
            }
            elseif ( $liveData[$name] !== $value )
            {
                if ( !in_array( $name, $silentErrorKeys ) )
                    $errors[] = "Il valore di '$name' è '$value', nell'installazione corrente invece è '$liveData[$name]'";
            }
        }
        if ( count( $errors ) > 0 )
        {
            throw new Exception( implode( "\n", $errors ) );
        }
        $googleId = $instance->getGoogleId();
        if ( empty( $googleId ) )
        {
            throw new Exception( "Attenzione valore GoogleId vuoto" );
        }
    }

    public function isMain()
    {
        return $this->currentSiteAccessName == OpenPABase::getFrontendSiteaccessName( $this->getIdentifier() );
    }

    public function isBackend()
    {
        return $this->currentSiteAccessName == OpenPABase::getBackendSiteaccessName( $this->getIdentifier() );
    }

    public function getCacheDirectory()
    {
        $cacheDir = $this->getSiteIni( 'FileSettings', 'CacheDir' );

        if ( $cacheDir[0] == "/" )
        {
            return eZDir::path( array( $cacheDir ) );
        }
        else
        {
            return eZDir::path( array( $this->getVarDirectory(), $cacheDir ) );
        }
    }

    public function getVarDirectory()
    {
        return eZDir::path( array( $this->getSiteIni( 'FileSettings', 'VarDir' ) ) );
    }

    public function getStorageDirectory()
    {
        $varDir = $this->getVarDirectory();
        $storageDir = $this->getSiteIni( 'FileSettings', 'StorageDir' );
        return eZDir::path( array( $varDir, $storageDir ) );
    }

    public function getDatabaseSettings()
    {
        return $this->getSiteIni( 'DatabaseSettings' );
    }

    public function getSolrHost()
    {
        return $this->getSolrIni( 'SolrBase', 'SearchServerURI' );
    }

}
