<?php

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$tpl->setVariable( 'error', false );

// ricavo la classe ruolo
$ruoloClass = eZContentClass::fetchByIdentifier( 'ruolo' );
if ( !$ruoloClass instanceof eZContentClass )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

// Automazione delle policies: l'utente anonimo può leggere gli oggetti di tipo ruolo?
$anonymousRole = eZRole::fetchByName( 'Anonymous' );
$anonymousHasAccess = false;
if ( $anonymousRole instanceof eZRole )
{
    foreach( $anonymousRole->attribute( 'policies' ) as $policy )
    {
        if ( $policy->attribute( 'module_name' ) == 'content' && $policy->attribute( 'function_name' ) == 'read' )
        {
            foreach( $policy->attribute( 'limitations' ) as $limitation )
            {
                if ( $limitation->attribute( 'identifier' ) == 'Class' )
                {
                    foreach ( $limitation->attribute( 'values_as_array' ) as $id )
                    {
                        if ( $id == $ruoloClass->attribute( 'id' ) )
                        {
                            $anonymousHasAccess = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    if ( !$anonymousHasAccess )
    {
        // assognazione della policy
        $anonymousRole->appendPolicy( 'content', 'read', array( 'Class' => array( $ruoloClass->attribute( 'id' ) ) ) );
        $anonymousRole->store();
    }
}

if ( $http->hasPostVariable( 'AggiungiRuolo' ) )
{
    // Oggetto contenitore di ruoli: ricavato da remoteID. Se non esiste veien creato in Media
    $remoteParentObject = OpenPaFunctionCollection::$remoteRoles;
    $parentObject = eZContentObject::fetchByRemoteID( $remoteParentObject );    
    if ( !$parentObject instanceof eZContentObject )
    {
        $shortDescription = '<?xml version="1.0" encoding="utf-8"?><section xmlns:image="http://ez.no/namespaces/ezpublish3/image/" xmlns:xhtml="http://ez.no/namespaces/ezpublish3/xhtml/" xmlns:custom="http://ez.no/namespaces/ezpublish3/custom/"><section><paragraph>Elenco dei ruoli e delle relazioni tra personale e strutture</paragraph></section></section>';
        $attributeList = array( 'name' => 'Ruoli','short_description' => $shortDescription );
        $admin = eZUser::fetchByName( 'admin' );
        $params = array();
        $params['creator_id'] = $admin->attribute( 'contentobject_id' );
        $params['remote_id'] = $remoteParentObject;
        $params['class_identifier'] = 'folder';
        $params['parent_node_id'] = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'MediaRootNode' );
        $params['attributes'] = $attributeList;
        $parentObject = eZContentFunctions::createAndPublishObject( $params );
    }
    
    if ( !$parentObject instanceof eZContentObject )
    {
        return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
    }
    
    $struttura = $http->hasPostVariable( 'Struttura' ) ? $http->postVariable( 'Struttura' ) : null;
    $dipendente = $http->hasPostVariable( 'Dipendente' ) ? $http->postVariable( 'Dipendente' ) : null;
    $ruolo = $http->hasPostVariable( 'Ruolo' ) ? $http->postVariable( 'Ruolo' ) : null;
    
    // inserimento del nuovo ruolo
    if( $dipendente && $ruolo )
    {        
        $attributeList = array( 'titolo' => $ruolo,
                                'struttura_di_riferimento' => $struttura,
                                'utente' => $dipendente);
        $user = eZUser::currentUser();
        $params = array();
        $params['creator_id'] = $user->attribute( 'contentobject_id' );        
        $params['class_identifier'] = $ruoloClass->attribute( 'identifier' );
        $params['parent_node_id'] = $parentObject->attribute( 'main_node_id' );
        $params['attributes'] = $attributeList;
        $newRuolo = eZContentFunctions::createAndPublishObject( $params );
        if( !$newRuolo instanceof eZContentObject )
        {
            $tpl->setVariable( 'error', 'Errore nel creare il nuovo ruolo' );
        }
        else
        {
            // svuoto le cache dei relazionati (e non capisco perché non loo fa ez...)
            if ( $struttura ) eZContentCacheManager::clearContentCacheIfNeeded( (int)$struttura );
            eZContentCacheManager::clearContentCacheIfNeeded( (int)$dipendente );            
            return $module->redirectTo( '/openpa/roles' );
        }
    }
    else
    {
        $tpl->setVariable( 'error', 'Inserisci tutti i valori' );
    }
    
}

$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/ruoli.tpl' );
$Result['path'] = array( array( 'text' => 'Gestione Ruoli Dipendenti' ,
                                'url' => false ) );
