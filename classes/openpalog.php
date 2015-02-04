<?php
class OpenPALog
{    
    const ALL = 0;
    const NOTICE = 1;
    const WARNING = 2;
    const ERROR = 3;

    public static $outputLevel = -1;
    
    public static $logFileName;
    
    public static function setOutputLevel( $level )
    {
        self::$outputLevel = $level;
    }
    
    public static function output( $message = '', $addEOL = true )
    {
        if ( self::$outputLevel <= 0 )
        {
            eZCLI::instance()->output( $message, $addEOL );
            self::log( $message );
        }
    }
    
    
    public static function notice( $message = '', $addEOL = true )
    {
        if ( self::$outputLevel <= 1 )
        {
            eZCLI::instance()->notice( $message, $addEOL );
            self::log( $message );
        }
    }
    
    public static function warning( $message = '', $addEOL = true )
    {
        if ( self::$outputLevel <= 2 )
        {
            eZCLI::instance()->warning( $message, $addEOL );
            self::log( $message );
        }
    }
    
    public static function error( $message = '', $addEOL = true )
    {
        if ( self::$outputLevel <= 3 )
        {
            eZCLI::instance()->error( $message, $addEOL );
            self::log( $message );
        }
    }
    
    protected static function log( $message )
    {
        if ( self::$logFileName !== null )
        {
            eZLog::write( $message, self::$logFileName );
        }
    }

    public static function writeCsv( $message, $type )
    {
        if ( empty( $message ) || empty( $type ) )
            return false;

        $directory = 'var/log/openpa';
        $logFileName = 'log-' . date( 'Y-m-d' ) . '.csv';
        $filePath = $directory . '/' . $logFileName;
        if ( !file_exists( $filePath ) )
        {
            eZFile::create( $logFileName, $directory );            
        }
        $processUser = posix_getpwuid( posix_geteuid() );
        $fp = fopen( $filePath, 'a+' );
        $values = array( $processUser['name'], OpenPABase::getCurrentSiteaccessIdentifier(), date( 'Y-m-d H:i:s' ), $message );
        if ( is_string( $type ) )
        {
            $values[] = $type;
        }
        elseif( is_array( $type ) )
        {
            $values = array_merge( $values, $type );
        }
        else
        {
            $values[] = var_export( $type, 1 );
        }
        fputcsv( $fp, $values );
        fclose( $fp );
        return true;
    }
}