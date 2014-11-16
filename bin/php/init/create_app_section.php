<?php

$rootNode = OpenPAAppSectionHelper::instance()->rootNode( false );

if ( !$rootNode instanceof eZContentObjectTreeNode )
{
    $siteaccess = eZSiteAccess::current();

    $identifier = OpenPAAppSectionHelper::ROOT_CLASSIDENTIFIER;
    $tools = new OpenPAClassTools( $identifier, true );
    if ( !$tools->isValid() )
    {
        $tools->sync( true );
        $cli->output( "La classe $identifier è stata aggiornata" );
    }

    OpenPAAppSectionHelper::instance()->rootNode( true );
    $cli->output( "Apps container installato" );
}