<?php

class OpenPADFSFileHandlerDFSRegistry
{
    /**
     * Handlers based on path (as key)
     * @var eZDFSFileHandlerDFSBackendInterface[string]
     */
    private $pathHandlers = array();

    /**
     * The default handler, used when no {@see $pathHandlers} matches
     * @var eZDFSFileHandlerDFSBackendInterface
     */
    private $defaultHandler;

    /**
     * @param eZDFSFileHandlerDFSBackendInterface $defaultHandler
     * @param eZDFSFileHandlerDFSBackendInterface[] $pathHandlers
     */
    public function __construct(eZDFSFileHandlerDFSBackendInterface $defaultHandler, array $pathHandlers = array())
    {
        foreach ($pathHandlers as $supportedPath => $handler) {
            if (!$handler instanceof eZDFSFileHandlerDFSBackendInterface) {
                throw new InvalidArgumentException(get_class($handler) . " does not implement eZDFSFileHandlerDFSBackendInterface");
            }
        }

        $this->defaultHandler = $defaultHandler;
        $this->pathHandlers = $pathHandlers;
    }

    /**
     * Returns the FSHandler for $path
     * @param $path
     * @return eZDFSFileHandlerDFSBackendInterface
     * @throws OutOfRangeException If no handler supports $path
     */
    public function getHandler($path)
    {
        foreach ($this->pathHandlers as $supportedPath => $handler) {
            if (strstr($path, $supportedPath) !== false) {
                return $handler;
            }
        }

        return $this->defaultHandler;
    }

    public function getAllHandlers()
    {
        $handlers = array_values($this->pathHandlers);
        $handlers[] = $this->defaultHandler;
        return $handlers;
    }

    /**
     * Builds a registry using either the provided configuration, or settings from self::getConfiguration
     * @return self
     */
    public static function build()
    {
        $privateHandler = self::buildHandler('OpenPADFSFileHandlerDFSAWSS3Private');
        $publicHandler = self::buildHandler('OpenPADFSFileHandlerDFSAWSS3Public');

        $redisHandler = self::buildHandler('OpenPADFSFileHandlerDFSRedis');
        //$dynamoDbHandler = self::buildHandler('OpenPADFSFileHandlerDFSAWSDynamoDb');
        $localHandler = self::buildHandler('OpenPADFSFileHandlerDFSLocal');

        $pathHandlers = array();

        $ini = self::getCurrentInstanceIni();
        $varDir = eZDir::path(array($ini->variable('FileSettings', 'VarDir')));

        $cacheDir = $ini->variable('FileSettings', 'CacheDir');
        if ($cacheDir[0] == "/") {
            $cacheDir = eZDir::path(array($cacheDir));
        } else {
            $cacheDir = eZDir::path(array($varDir, $cacheDir));
        }

        $pathHandlers["$varDir/storage/images"] = $publicHandler;
        $pathHandlers["$cacheDir/public"] = $publicHandler;
        $pathHandlers["$cacheDir/content"] = $localHandler;
        $pathHandlers[$cacheDir] = $redisHandler;

        return new static($privateHandler, $pathHandlers);
    }

    private static function buildHandler($className)
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Invalid DFSBackend class $className. Were autoloads generated ?");
        }

        if (self::hasFactorySupport($className)) {
            $handler = $className::build();
        } else {
            $handler = new $className();
        }

        if (!$handler instanceof eZDFSFileHandlerDFSBackendInterface) {
            throw new InvalidArgumentException("$className doesn't implement eZDFSFileHandlerDFSBackendInterface");
        }

        $loggedHandlerWrapper = new OpenPADFSFileHandlerDFSLogged($handler);

        return $loggedHandlerWrapper;
    }

    private static function hasFactorySupport($className)
    {
        $implementedClasses = class_implements($className);
        return isset( $implementedClasses['eZDFSFileHandlerDFSBackendFactoryInterface'] );
    }

    public static function getCurrentInstanceIni($settingFile = null)
    {
        if (defined('OPENCONTENT_CURRENT_INSTANCE')) {
            $GLOBALS['eZCurrentAccess']['name'] = null;
            $siteAccess = OPENCONTENT_CURRENT_INSTANCE . '_backend';
            return eZSiteAccess::getIni($siteAccess, $settingFile);
        }

        return eZINI::instance($settingFile);
    }
}
