<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$http = eZHTTPTool::instance();

if ( $http->hasPostVariable( 'GoogleID' ) )
{
    $GoogleID = trim( $http->postVariable( 'GoogleID' ) );
    $save = OpenPAINI::set( "Seo", "GoogleAnalyticsAccountID", $GoogleID );
    if ($save){
        $tpl->setVariable('message', 'Google Analytics ID salvato' );
        eZCache::clearByTag( 'template' );
    }else{
        $tpl->setVariable('message', 'Errore!' );
    }
}

$tpl->setVariable('googleId', OpenPAINI::variable( 'Seo', 'GoogleAnalyticsAccountID', false ));

$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/seo.tpl' );
$Result['path'] = array( array( 'text' => 'Impostazioni SEO' , 'url' => false ) );
