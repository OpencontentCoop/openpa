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

    private $db;

    public function __construct( $siteAccessName )
    {
        if ( empty( $siteAccessName ) )
            throw new Exception( "SiteAccess name not found" );
        $ipList = OpenPAINI::variable('InstanceSettings', 'LiveIPList', array());
        if (!empty($ipList)){
            self::$validIps = $ipList;
        }
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
        $stmt = $this->connectToInstanceDb()->prepare( 'SELECT value FROM ezsite_data WHERE name = :name' );
        $stmt->bindValue( ':name', 'GoogleAnalyticsAccountID' );
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data){
            return $data['value'];
        }
        return $this->getOpenpaIni( 'Seo', 'GoogleAnalyticsAccountID' );
    }

    private function connectToInstanceDb()
    {
        if (!$this->db){

            $dbMapping = array( 'ezmysqli' => 'mysql',
                                'ezmysql' => 'mysql',
                                'mysql' => 'mysql',
                                'mysqli' => 'mysql',
                                'pgsql' => 'pgsql',
                                'postgresql' => 'pgsql',
                                'ezpostgresql' => 'pgsql',
                                'ezoracle' => 'oracle',
                                'oracle' => 'oracle' );

            $databaseSettings = $this->getDatabaseSettings();
            $dbType = $databaseSettings['DatabaseImplementation'];
            $dbUser = $databaseSettings['User'];
            $dbPass = $databaseSettings['Password'];
            $dbHost = $databaseSettings['Server'];
            $dbPort = $databaseSettings['Port'];
            $dbName = $databaseSettings['Database'];

            if ( !isset( $dbMapping[$dbType] ) ) {
                throw new Exception( "Unknown / unmapped DB type '$dbType'" );
            }

            $dbType = $dbMapping[$dbType];

            $dsnHost = $dbHost . ( $dbPort != '' ? ":$dbPort" : '' );

            $dsn = "{$dbType}://{$dbUser}:{$dbPass}@{$dsnHost}/{$dbName}";
            $this->db = ezcDbFactory::create( $dsn );
        }

        return $this->db;
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

        $iniType = OpenPAINI::variable('InstanceSettings', 'InstanceType', false);
        if (!empty( $iniType )) {
            $type = $iniType;

        } elseif (strpos($this->currentSiteAccessName, '_sensor') !== false) {
            $type = 'sensor';

        } elseif (strpos($this->currentSiteAccessName, '_dimmi') !== false) {
            $type = 'dimmi';

        } elseif (strpos($this->currentSiteAccessName, '_agenda') !== false) {
            $type = 'agenda';

        } elseif (strpos($this->currentSiteAccessName, '_booking') !== false) {
            $type = 'booking';

        } elseif (in_array('fusioni', $this->getSiteIni('DesignSettings', 'AdditionalSiteDesignList'))) {
            $type = 'fusione';

        } elseif (strpos($this->getSiteIni('SiteSettings', 'SiteName'), 'Comun') !== false) {
            $type = 'comune_standard';
        }

        return $type;
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

    public function getContactsData()
    {
        $pagedata = new OpenPAPageData();
        return $pagedata->getContactsData();
    }

    public function getLogo()
    {
        return OpenPaFunctionCollection::fetchStemma();
    }
}
