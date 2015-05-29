<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();

$siteName = eZINI::instance()->variable( 'SiteSettings', 'SiteName' );
$siteUrl = '/';
eZURI::transformURI( $siteUrl, false, 'full' );
$tpl->setVariable( 'site_url', $siteName . ' (' . $siteUrl . ')' );

$infoMail = false;
if ( eZINI::instance()->hasVariable( 'MailSettings', 'PrivacyMail' ) )
    $infoMail = eZINI::instance()->variable( 'MailSettings', 'PrivacyMail' );
else
{
    $pageData = new OpenPAPageData();
    $contacts = $pageData->getContactsData();
    if ( isset( $contacts['mail'] ) )
    {
        $infoMail = $contacts['mail'];
    }
}
$tpl->setVariable( 'info_mail', $infoMail );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/cookie.tpl' );
$Result['path'] = array( array( 'text' => 'Informativa Cookie' , 'url' => false ) );