<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Controllo di sincronizzazione Amministrazione Tasparente\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

OpenPALog::setOutputLevel( OpenPALog::WARNING );

OpenPALog::notice( 'Controllo consistenza classi e oggetti Amministrazione Tasparente' );

try
{
    $errorClassCount = 0;
    $errorTreeCount = 0;
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
    
    // sincronizzazaione classi
    $classiTrasparenza = array(
        'conferimento_incarico',
        'consulenza',
        'nota_trasparenza',
        'pagina_trasparenza',
        'responsabile_trasparenza',
        'trasparenza',
        'tasso_assenza',
        'dipendente',
        //'incarico',
        'sovvenzione_contributo',
        'organo_politico'
    );
    
    foreach( $classiTrasparenza as $identifier )
    {
        try
        {
            $tools = new OpenPAClassTools( $identifier );
            if ( !$tools->isValid() )
            {
                if ( $tools->getData()->hasError || $tools->getData()->hasExtraAttributes )
                    OpenPALog::error( "La classe $identifier necessita aggiornamento: controlla i campi interessati" );
                elseif ( $tools->getData()->extraAttributes )
                {
                    foreach( $tools->getData()->extraAttributes as $attribute )
                    {
                        $detail = $tools->getData()->extraDetails[$attribute->Identifier];
                        OpenPALog::error( "La classe $identifier contiene attributi aggiuntivi valorizzati: controlla i campi interessati" );
                        break;
                    }
                }
                else
                    OpenPALog::warning( "La classe $identifier necessita aggiornamento" );
            }
        }
        catch( Exception $e )
        {
            OpenPALog::error( $e->getMessage() );
            $errorClassCount++;
        }
    }

    $treeNode = OpenPAINI::variable('NetworkSettings', 'SyncTrasparenzaRemoteUrl');
    $treeUrl = $treeNode . '/list/offset/0/limit/1000';
    
    $apiNode = OpenPAApiNode::fromLink( $treeNode );
    if ( !eZContentObject::fetchByRemoteID( $apiNode->metadata['objectRemoteId'] ) )
    {
        OpenPALog::warning( "Non esiste il nodo radice!" );
        $errorTreeCount++;
    }
    
    $dataTree = json_decode( OpenPABase::getDataByURL( $treeUrl ), true );
    if ( $dataTree )
    {
        foreach( $dataTree['childrenNodes'] as $item )
        {
            $item = new OpenPAApiChildNode( $item );
            $apiNode = $item->getApiNode();
            try
            {
                if ( !eZContentObject::fetchByRemoteID( $apiNode->metadata['objectRemoteId'] ) )
                {
                    OpenPALog::warning( "Non esiste il nodo {$apiNode->metadata['objectName']}" );
                    $errorTreeCount++;
                }
                foreach( $item->getChildren() as $child )
                {
                    $apiNodeChild = $child->getApiNode();
                    if ( !eZContentObject::fetchByRemoteID( $apiNodeChild->metadata['objectRemoteId'] ) )
                    {
                        OpenPALog::warning( "Non esiste il nodo {$apiNodeChild->metadata['objectName']}" );
                        $errorTreeCount++;
                    }
                }
            }
            catch( Exception $e )
            {
                OpenPALog::warning( $item->objectName . ': ' . $e->getMessage() );
                $errorTreeCount++;
            }
        }
    }
    
    if ( $errorTreeCount == 0 && $errorClassCount == 0)
    {
        OpenPALog::notice( 'Tutto ok, puoi procedere con la sincronizzazione' );
    }
    if ( $errorClassCount > 0)
    {
        OpenPALog::notice( "Con la sincronizzazione le classi saranno aggiornate" );
    }
    if ( $errorTreeCount > 0 )
    {
        OpenPALog::error( "Ci sono problemi con l'alberatura. Controlla i remote_id" );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{    
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
