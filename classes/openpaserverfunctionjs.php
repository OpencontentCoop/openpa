<?php

class OpenPAServerFunctionsJs extends ezjscServerFunctionsJs
{
    public static function search( $args )
    {
        $http = eZHTTPTool::instance();

        $searchStr = null;
        if ( $http->hasPostVariable( 'SearchStr' ) )
            $searchStr = trim( $http->postVariable( 'SearchStr' ) );
        else if ( isset( $args[0] ) )
            $searchStr = trim( $args[0] );

        if ( $http->hasPostVariable( 'SearchOffset' ))
            $searchOffset = (int) $http->postVariable( 'SearchOffset' );
        else if ( isset( $args[1] ) )
            $searchOffset = (int) $args[1];
        else
            $searchOffset = 0;

        if ( $http->hasPostVariable( 'SearchLimit' ))
            $searchLimit = (int) $http->postVariable( 'SearchLimit' );
        else if ( isset( $args[2] ) )
            $searchLimit = (int) $args[2];
        else
            $searchLimit = 10;

        // Do not allow to search for more then x items at a time
        $ini = eZINI::instance();
        $maximumSearchLimit = (int) $ini->variable( 'SearchSettings', 'MaximumSearchLimit' );
        if ( $searchLimit > $maximumSearchLimit )
            $searchLimit = $maximumSearchLimit;

        // Prepare node encoding parameters
        $encodeParams = array();
        if ( self::hasPostValue( $http, 'EncodingLoadImages' ) )
            $encodeParams['loadImages'] = true;

        if ( self::hasPostValue( $http, 'EncodingFetchChildrenCount' ) )
            $encodeParams['fetchChildrenCount'] = true;

        if ( self::hasPostValue( $http, 'EncodingFetchSection' ) )
            $encodeParams['fetchSection'] = true;

        if ( self::hasPostValue( $http, 'EncodingFormatDate' ) )
            $encodeParams['formatDate'] = $http->postVariable( 'EncodingFormatDate' );

        // Prepare search parameters
        $params = array( 'SearchOffset' => $searchOffset,
                         'SearchLimit' => $searchLimit,
                         'SortArray' => array( 'published', 0 )
        );

        if ( self::hasPostValue( $http, 'SearchContentClassAttributeID' ) )
        {
            $params['SearchContentClassAttributeID'] = self::makePostArray( $http, 'SearchContentClassAttributeID' );
        }
        else if ( self::hasPostValue( $http, 'SearchContentClassID' ) )
        {
            $params['SearchContentClassID'] = self::makePostArray( $http, 'SearchContentClassID' );
        }
        else if ( self::hasPostValue( $http, 'SearchContentClassIdentifier' ) )
        {
            $params['SearchContentClassID'] = eZContentClass::classIDByIdentifier( self::makePostArray( $http, 'SearchContentClassIdentifier' ) );
        }

        if ( self::hasPostValue( $http, 'SearchSubTreeArray' ) )
        {
            $params['SearchSubTreeArray'] = self::makePostArray( $http, 'SearchSubTreeArray' );
        }

        if ( self::hasPostValue( $http, 'SearchSectionID' ) )
        {
            $params['SearchSectionID'] = self::makePostArray( $http, 'SearchSectionID' );
        }

        if ( self::hasPostValue( $http, 'SearchDate' ) )
        {
            $params['SearchDate'] = (int) $http->postVariable( 'SearchDate' );
        }
        else if ( self::hasPostValue( $http, 'SearchTimestamp' ) )
        {
            $params['SearchTimestamp'] = self::makePostArray( $http, 'SearchTimestamp' );
            if ( !isset( $params['SearchTimestamp'][1] ) )
                $params['SearchTimestamp'] = $params['SearchTimestamp'][0];
        }

        if ( self::hasPostValue( $http, 'EnableSpellCheck' ) || self::hasPostValue( $http, 'enable-spellcheck', '0' ) )
        {
            $params['SpellCheck'] = array( true );
        }

        if ( self::hasPostValue( $http, 'GetFacets' ) || self::hasPostValue( $http, 'show-facets', '0' ) )
        {
            $params['facet'] = eZFunctionHandler::execute( 'ezfind', 'getDefaultSearchFacets', array() );
        }

        $result = array( 'SearchOffset' => $searchOffset,
                         'SearchLimit' => $searchLimit,
                         'SearchResultCount' => 0,
                         'SearchCount' => 0,
                         'SearchResult' => array(),
                         'SearchString' => $searchStr,
                         'SearchExtras' => array()
        );

        // Possibility to keep track of callback reference for use in js callback function
        if ( $http->hasPostVariable( 'CallbackID' ) )
            $result['CallbackID'] = $http->postVariable( 'CallbackID' );

        // Only search if there is something to search for
        if ( $searchStr )
        {
            if ( OpenPAINI::variable( 'MotoreRicerca', 'UseHeuristicInRelationSearch', 'disabled' ) == 'enabled' )
            {
                $solr = new eZSolr();
                $searchStr = $searchStr ? '(*' . strtolower( $searchStr ) . '*) OR ' . strtolower( $searchStr ) : $searchStr;
                $params['QueryHandler'] = 'simplestandard';
                $params['SortBy'] = array( 'score' => 'asc' );
                $params['Filter'] = array( 'meta_name_t' => $searchStr );
                $searchList = $solr->search( $searchStr, $params );
            }
            else
            {
                $searchList = eZSearch::search( $searchStr, $params );
            }

            $result['SearchResultCount'] = $searchList['SearchResult'] !== false ? count( $searchList['SearchResult'] ) : 0;
            $result['SearchCount'] = (int) $searchList['SearchCount'];
            $result['SearchResult'] = ezjscAjaxContent::nodeEncode( $searchList['SearchResult'], $encodeParams, false );

            // ezfind stuff
            if ( isset( $searchList['SearchExtras'] ) && $searchList['SearchExtras'] instanceof ezfSearchResultInfo )
            {
                if ( isset( $params['SpellCheck'] ) )
                    $result['SearchExtras']['spellcheck'] = $searchList['SearchExtras']->attribute( 'spellcheck' );


                if ( isset( $params['facet'] ) )
                {
                    $facetInfo = array();
                    $retrievedFacets = $searchList['SearchExtras']->attribute( 'facet_fields' );
                    $baseSearchUrl = "/content/search/";
                    eZURI::transformURI( $baseSearchUrl, false, 'full' );

                    foreach ( $params['facet'] as $key => $defaultFacet )
                    {
                        $facetData       = $retrievedFacets[$key];
                        $facetInfo[$key] = array( 'name' => $defaultFacet['name'], 'list' => array() );
                        if ( $facetData !== null )
                        {
                            foreach ( $facetData['nameList'] as $key2 => $facetName )
                            {
                                if ( $key2 != '' )
                                {
                                    $tmp = array( 'value' => $facetName );
                                    $tmp['url'] = $baseSearchUrl . '?SearchText=' . $searchStr . '&filter[]=' . $facetData['queryLimit'][$key2] . '&activeFacets[' . $defaultFacet['field'] . ':' . $defaultFacet['name'] . ']=' . $facetName;
                                    $tmp['count'] = $facetData['countList'][$key2];
                                    $facetInfo[$key]['list'][] = $tmp;
                                }
                            }
                        }
                    }
                    $result['SearchExtras']['facets'] = $facetInfo;
                }
            }//$searchList['SearchExtras'] instanceof ezfSearchResultInfo
        }// $searchStr

        return $result;
    }

    /**
     * Figures out where to load jQuery files from and prepends them to $packerFiles
     *
     * @param array $args
     * @param array $packerFiles ByRef list of files to pack (by ezjscPacker)
     * @return string
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
                if ( empty( $ezjscoreIni->BlockValues ) )
                {
                    $ezjscoreIni->parseFile( $rootDir . '/ezjscore.ini.append.php' );                
                }
            }
        }
        return $ezjscoreIni;
    }

    public static function jqueryio( $args )
    {
        $rootUrl = self::getIndexDir();
        return "
(function($) {
    var _rootUrl = '$rootUrl', _serverUrl = _rootUrl + 'ezjscore/', _seperator = '@SEPERATOR$',
        _prefUrl = _rootUrl + 'user/preferences';

    // FIX: Ajax is broken on IE8 / IE7 on jQuery 1.4.x as it's trying to use the broken window.XMLHttpRequest object
    if ( window.XMLHttpRequest && window.ActiveXObject )
        $.ajaxSettings.xhr = function() { try { return new window.ActiveXObject('Microsoft.XMLHTTP'); } catch(e) {} };

    // (static) jQuery.ez() uses jQuery.post() (Or jQuery.get() if post paramer is false)
    //
    // @param string callArgs
    // @param object|array|string|false post Optional post values, uses get request if false or undefined
    // @param function Optional callBack
    function _ez( callArgs, post, callBack )
    {
        callArgs = callArgs.join !== undefined ? callArgs.join( _seperator ) : callArgs;
        var url = _serverUrl + 'call/';
        if ( post )
        {
            var _token = '', _tokenNode = document.getElementById('ezxform_token_js');
            if ( _tokenNode ) _token = _tokenNode.getAttribute('title');
            if ( post.join !== undefined )// support serializeArray() format
            {
                post.push( { 'name': 'ezjscServer_function_arguments', 'value': callArgs } );
                post.push( { 'name': 'ezxform_token', 'value': _token } );
            }
            else if ( typeof(post) === 'string' )// string
            {
                post += ( post ? '&' : '' ) + 'ezjscServer_function_arguments=' + callArgs + '&ezxform_token=' + _token;
            }
            else // object
            {
                post['ezjscServer_function_arguments'] = callArgs;
                post['ezxform_token'] = _token;
            }
            return $.post( url, post, callBack, 'json' );
        }
        return $.get( url + encodeURIComponent( callArgs ), {}, callBack, 'json' );
    };
    _ez.url = _serverUrl;
    _ez.root_url = _rootUrl;
    _ez.seperator = _seperator;
    $.ez = _ez;

    $.ez.setPreference = function( name, value )
    {
        var param = {'Function': 'set_and_exit', 'Key': name, 'Value': value};
            _tokenNode = document.getElementById( 'ezxform_token_js' );
        if ( _tokenNode )
            param.ezxform_token = _tokenNode.getAttribute( 'title' );

        return $.post( _prefUrl, param );
    };

    // Method version, for loading response into elements
    // NB: Does not use json (not possible with .load), so ezjscore/call will return string
    function _ezLoad( callArgs, post, selector, callBack )
    {
        callArgs = callArgs.join !== undefined ? callArgs.join( _seperator ) : callArgs;
        var url = _serverUrl + 'call/';
        if ( post )
        {
            post['ezjscServer_function_arguments'] = callArgs;
            post['ezxform_token'] = jQuery('#ezxform_token_js').attr('title');
        }
        else
            url += encodeURIComponent( callArgs );

        return this.load( url + ( selector ? ' ' + selector : '' ), post, callBack );
    };
    $.fn.ez = _ezLoad;
})(jQuery);
        ";
    }
}