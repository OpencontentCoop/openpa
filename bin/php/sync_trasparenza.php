<?php
require 'autoload.php';

$script = eZScript::instance( array( 'description' => ( "OpenPA Sincronizzazione Amministrazione Tasparente\n\n" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );
try
{
    $user = eZUser::fetchByName( 'admin' );
    eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
    
    $siteaccess = eZSiteAccess::current();
    if ( stripos( $siteaccess['name'], 'prototipo' ) !== false )
    {
        throw new Exception( 'Script non eseguibile sul prototipo' );        
    }
        
    // sincronizzazaione classi
    $classiTrasparenza = array(
        //'conferimento_incarico',
        //'consulenza',
        'nota_trasparenza',
        'pagina_trasparenza',
        //'responsabile_trasparenza',
        'trasparenza',
        //'tasso_assenza',
        //'dipendente',
        //'incarico',
        //'sovvenzione_contributo',
        //'organo_politico'
    );
    
    foreach( $classiTrasparenza as $identifier )
    {
        OpenPALog::warning( 'Sincronizzo classe ' . $identifier );
        $tools = new OpenPAClassTools( $identifier, true ); // creo se non esiste
        $tools->sync( true, true ); // forzo e rimuovo attributi in più
    }
   
    //@todo mettere in un openpa.ini
    $treeNode = 'http://openpa.opencontent.it/api/opendata/v1/content/node/966';
    $treeUrl = $treeNode . '/list/offset/0/limit/1000';
    
    OpenPAObjectTools::syncObjectFormRemoteApiNode( OpenPAApiNode::fromLink( $treeNode ) );
    
    $dataTree = json_decode( eZHTTPTool::getDataByURL( $treeUrl ), true );
    if ( $dataTree )
    {
        foreach( $dataTree['childrenNodes'] as $item )
        {
            $item = new OpenPAApiChildNode( $item );            
            try
            {
                OpenPAObjectTools::syncObjectFormRemoteApiChildNode( $item );
                foreach( $item->getChildren() as $child )
                {
                    if ( $child->classIdentifier == 'pagina_trasparenza' )
                    {
                        OpenPAObjectTools::syncObjectFormRemoteApiChildNode( $child );    
                    }
                }
            }
            catch( Exception $e )
            {
                OpenPALog::error( $item->objectName . ': ' . $e->getMessage() );
            }
        }
    }
    
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
