<?php

class OpenPADataHandler
{
    /**
     * @param string $identifier
     * @param array $Params
     * @return mixed
     * @throws Exception
     */
    public static function runHandler( $identifier, $Params )
    {
        if ( !in_array( $identifier, self::availableHandlers() ) )
        {
            throw new Exception( "Data handler $identifier not found" );
        }
        $handler = self::handler( $identifier, $Params );
        return $handler->getData();
    }

    /**
     * return string[]
     */
    public static function availableHandlers()
    {
        return array_keys( OpenPAINI::variable( 'DataHandlers', 'Handlers', array() ) );
    }


    /**
     * @param string $identifier
     * @param array $Params
     * @return OpenPADataHandlerInterface
     * @throws Exception
     */
    public static function handler( $identifier, $Params )
    {
        $className = false;
        $handlers = OpenPAINI::variable( 'DataHandlers', 'Handlers', array() );
        if ( isset( $handlers[$identifier] ) )
        {
            $className = $handlers[$identifier];
        }
        if ( class_exists( $className ) )
        {
            $handler = new $className( $Params );
            if ( $handler instanceof OpenPADataHandlerInterface )
            {
                return $handler;
            }
            throw new Exception( "Data handler $className must implement OpenPADataHandlerInterface" );
        }
        throw new Exception( "Data handler class '$className' not found" );
    }

}