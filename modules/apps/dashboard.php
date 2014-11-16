<?php

$Module = $Params['Module'];
$tpl = eZTemplate::factory();
$helper = OpenPAAppSectionHelper::instance();
$user = eZUser::currentUser();
$ini = eZINI::instance();
$viewCacheEnabled = ( $ini->variable( 'ContentSettings', 'ViewCaching' ) == 'enabled' );
$ViewMode = 'full';
$NodeID = $helper->rootNode( true )->attribute( 'node_id' );
$LanguageCode = false;
$Result = array();
$Offset = false;
$collectionAttributes = false;
$viewParameters = array( 'offset' => '',
                         'year' => '',
                         'month' => '',
                         'day' => '',
                         'namefilter' => false );
$validation = array( 'processed' => false,
                     'attributes' => array() );
$res = eZTemplateDesignResource::instance();
$keys = $res->keys();
if ( isset( $keys['layout'] ) )
    $layout = $keys['layout'];
else
    $layout = false;

if ( eZOperationHandler::operationIsAvailable( 'content_read' ) )
{
    $operationResult = eZOperationHandler::execute( 'content', 'read', array( 'node_id' => $NodeID,
                                                                              'user_id' => $user->id(),
                                                                              'language_code' => $LanguageCode ), null, true );
}

if ( ( isset( $operationResult['status'] ) && $operationResult['status'] != eZModuleOperationInfo::STATUS_CONTINUE ) )
{
    switch( $operationResult['status'] )
    {
        case eZModuleOperationInfo::STATUS_HALTED:
        case eZModuleOperationInfo::STATUS_REPEAT:
        {
            if ( isset( $operationResult['redirect_url'] ) )
            {
                $Module->redirectTo( $operationResult['redirect_url'] );
                return;
            }
            else if ( isset( $operationResult['result'] ) )
            {
                $result = $operationResult['result'];
                $resultContent = false;
                if ( is_array( $result ) )
                {
                    if ( isset( $result['content'] ) )
                    {
                        $resultContent = $result['content'];
                    }
                    if ( isset( $result['path'] ) )
                    {
                        $Result['path'] = $result['path'];
                    }
                }
                else
                {
                    $resultContent = $result;
                }
                $Result['content'] = $resultContent;
            }
        } break;
        case eZModuleOperationInfo::STATUS_CANCELLED:
        {
            $Result = array();
            $Result['content'] = "Content view cancelled<br/>";
        } break;
    }
    return $Result;
}
else
{
    $localVars = array( "cacheFileArray", "NodeID",   "Module", "tpl",
                        "LanguageCode",   "ViewMode", "Offset", "ini",
                        "cacheFileArray", "viewParameters",  "collectionAttributes",
                        "validation" );
    if ( $viewCacheEnabled )
    {
        $user = eZUser::currentUser();

        $cacheFileArray = eZNodeviewfunctions::generateViewCacheFile( $user, $NodeID, $Offset, $layout, $LanguageCode, $ViewMode, $viewParameters, false );

        $cacheFilePath = $cacheFileArray['cache_path'];

        $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
        $args = compact( $localVars );
        $Result = $cacheFile->processCache( array( 'eZNodeviewfunctions', 'contentViewRetrieve' ),
                                            array( 'eZNodeviewfunctions', 'contentViewGenerate' ),
                                            null,
                                            null,
                                            $args );
        return $Result;
    }
    else
    {
        $cacheFileArray = array( 'cache_dir' => false, 'cache_path' => false );
        $args = compact( $localVars );
        $data = eZNodeviewfunctions::contentViewGenerate( false, $args ); // the false parameter will disable generation of the 'binarydata' entry
        return $data['content']; // Return the $Result array
    }
}

// Looking for some view-cache code?
// Try the eZNodeviewfunctions class for enlightenment.
