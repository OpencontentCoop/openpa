<?php

class OpenPAServerFunctionsJs extends ezjscServerFunctionsJs
{
    /**
     * Figures out where to load jQuery files from and prepends them to $packerFiles
     *
     * @param array $args
     * @param array $packerFiles ByRef list of files to pack (by ezjscPacker)
     */
    public static function jquery( $args, &$packerFiles )
    {
        $ezjscoreIni = self::getIniFile();
        if ( $ezjscoreIni->variable( 'eZJSCore', 'LoadFromCDN' ) === 'enabled' )
        {
            $scriptFiles = $ezjscoreIni->variable( 'eZJSCore', 'ExternalScripts' );
            $packerFiles = array_merge( array( $scriptFiles['jquery'] ), $packerFiles );
        }
        else
        {
            $scriptFiles = $ezjscoreIni->variable( 'eZJSCore', 'LocalScripts' );
            $packerFiles = array_merge( array( $scriptFiles['jquery'] ), $packerFiles );
        }
        return '';
    }

    /**
     * Figures out where to load jQueryUI files from and prepends them to $packerFiles
     *
     * @param array $args
     * @param array $packerFiles ByRef list of files to pack (by ezjscPacker)
     * @return string Empty string, this function only modifies $packerFiles
     */
    public static function jqueryUI( $args, &$packerFiles )
    {
        $ezjscoreIni = self::getIniFile();
        if ( $ezjscoreIni->variable( 'eZJSCore', 'LoadFromCDN' ) === 'enabled' )
        {
            $scriptFiles = $ezjscoreIni->variable( 'eZJSCore', 'ExternalScripts' );
            $packerFiles = array_merge( array( $scriptFiles['jqueryUI'] ), $packerFiles );
        }
        else
        {
            $scriptFiles = $ezjscoreIni->variable( 'eZJSCore', 'LocalScripts' );
            $packerFiles = array_merge( array( $scriptFiles['jqueryUI'] ), $packerFiles );
        }
        return '';
    }

    protected static function getIniFile()
    {
        $ezjscoreIni = eZINI::instance( 'ezjscore.ini' );
        if ( $ezjscoreIni->hasVariable( 'eZJSCore', 'ForceScriptSettingsExtension' ) )
        {
            $extension = $ezjscoreIni->variable( 'eZJSCore', 'ForceScriptSettingsExtension' );
            $activeExtension = eZExtension::activeExtensions();
            if ( in_array( $extension, $activeExtension ) )
            {
                $rootDir = eZSys::rootDir() . '/' . eZExtension::baseDirectory() . '/' . $extension . '/settings';
                $ezjscoreIni = new eZINI( 'ezjscore.ini.append.php', $rootDir, null, false, false, true );
            }
        }
        return $ezjscoreIni;
    }
}