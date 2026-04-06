<?php

class OpenPALegacyMenuHandler implements OpenPAMenuHandlerInterface
{
    protected $identifier;
    protected $parameters;
    protected $rootNodeId;
    protected $userHash;

    public function __construct( $identifier, $parameters )
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;

        $this->parameters['identifier'] = $this->identifier;
        if ( isset( $this->parameters['root_node_id'] ) )
        {
            $this->rootNodeId = $this->parameters['root_node_id'];
        }
        else
        {
            $this->rootNodeId = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
            $this->parameters['root_node_id'] = $this->rootNodeId;
        }

        if ( isset( $this->parameters['user_hash'] ) && $this->parameters['user_hash'] != false )
        {
            $this->userHash = $this->parameters['user_hash'];
        }
        $this->parameters['user_hash'] = $this->userHash;
    }

    public function cacheFileName()
    {
        $extraCacheKey = false;
        if ( $this->userHash !== null )
        {
            $extraCacheKey = md5( $this->userHash );
        }
        return $this->identifier . '_' . $this->rootNodeId . $extraCacheKey . '.html';
    }

    public static function menuRetrieve( $file, $mtime, $args )
    {
        $result = include( $file );
        return $result;
    }

    public static function menuGenerate( $file, $args )
    {
        extract( $args );
        $result = self::getMenu( $parameters );
        return array( 'content' => $result,
                      'scope'   => OpenPAMenuTool::CACHE_IDENTIFIER );
    }

    public static function getMenu( $parameters )
    {
        if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false  )
        {
            OpenPAMenuTool::suAnonymous();
        }

        eZDebug::writeNotice( "Generate menu {$parameters['identifier']} for node {$parameters['root_node_id']}", __METHOD__ );

        if ( !isset( $parameters['template'] ) )
            $parameters['template'] = 'menu/cached/' . $parameters['identifier'] . '.tpl';

        $tpl = eZTemplate::factory();
        foreach( $parameters as $key => $value )
        {
            $tpl->setVariable( $key, $value );
        }

        $result = $tpl->fetch( 'design:' . $parameters['template'] );

        if ( !isset( $parameters['user_hash'] ) || $parameters['user_hash'] == false  )
        {
            OpenPAMenuTool::exitAnonymous();
        }
        return $result;
    }

    public function getParameters()
    {
        return $this->parameters;
    }


}