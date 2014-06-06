<?php

/*
$usa_valore = INI:UsaValore
$ignora = INI:Ignora
$valore_considerato = $valore_attributo = INI:DateTime
$valore_calcolato = (published + INI:ScadeDopoTotSecondi)

IF $ignora 

    IF $ignora == 'attributo' 
        $valore_considerato = $valore_calcolato
    
    ELSEIF $ignora == 'secondi' 
        $valore_considerato = $valore_attributo

ELSEIF $usa_valore 

    IF $usa_valore == 'maggiore'
        
        IF $valore_attributo > $valore_calcolato
            $valore_considerato = $valore_attributo
        ELSE
            $valore_considerato = $valore_calcolato
     
    ELSEIF $usa_valore == 'minore'
        
        IF $valore_attributo < $valore_calcolato
            $valore_considerato = $valore_attributo
        ELSE
            $valore_considerato = $valore_calcolato


IF $valore_considerato > 0 AND $valore_considerato < current_timestamp()
    
    Sposta oggetto in sezione INI:ToSection

ELSE

    Non fare nulla
*/    


eZExtension::activateExtensions();

$cli = eZCLI::instance();
$cli->setUseStyles( true );
$cli->setIsQuiet( $isQuiet );

// Login con admin
$user = eZUser::fetchByName( 'admin' );
if ( $user )
{
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
    //$cli->output( "Eseguo lo script da utente {$user->attribute( 'contentobject' )->attribute( 'name' )}" );
}
else
{    
    throw new InvalidArgumentException( "Non esiste un utente admin" ); 
}
$loggedUser = eZUser::currentUser();
$cli->output( "Si sta eseguendo l'agente con l'utente " . $loggedUser->attribute( 'contentobject' )->attribute( 'name' )  );


// Lettura dei file INI
$ini = eZINI::instance( 'openpa.ini' );
$Classes = $ini->variable( 'ChangeSection','ClassList' );
$rootNodeIDList = $ini->variable( 'ChangeSection','RootNodeList' );
$DataTime =  $ini->variable( 'ChangeSection','DataTime' );
$SectionIDs =  $ini->variable( 'ChangeSection','ToSection' );
$SectionDefault =  $ini->hasVariable( 'ChangeSection','ToSectionDefault' ) ? $ini->variable( 'ChangeSection','ToSectionDefault' ) : 0;
$ScadenzaSecondi = $ini->hasVariable( 'ChangeSection','ScadeDopoTotSecondi' ) ? $ini->variable( 'ChangeSection','ScadeDopoTotSecondi' ) : 0;
$UsaValore = $ini->hasVariable( 'ChangeSection','UsaValore' ) ? $ini->variable( 'ChangeSection','UsaValore' ) : false;
$Ignora = $ini->hasVariable( 'ChangeSection','Ignora' ) ? $ini->variable( 'ChangeSection','Ignora' ) : false;
$ScadenzaDefault = $ini->hasVariable( 'ChangeSection','ScadeDopoTotSecondiDefault' ) ? $ini->variable( 'ChangeSection','ScadeDopoTotSecondiDefault' ) : 0;
$currrentDate = time();

$countClasses = count( $rootNodeIDList );
$i = 0;
$clones = array();   

foreach( $rootNodeIDList as $class => $nodeID )
{    
    if ( $nodeID = 'RootNode' )
    {
        $nodeID = eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' );
    }
    $i++;
    $rootNode = eZContentObjectTreeNode::fetch( $nodeID );

    if ( !$rootNode instanceof eZContentObjectTreeNode )
    {
        $cli->error( "$nodeID non trovato" );
        continue;
    }
    
    $cli->output( $i . '/' . $countClasses . ' - classe: ' . $class . ', subtree: ' . $rootNode->attribute( 'node_id' ) . ', ', false );
    
    $usaValore = false;
    if ( isset( $UsaValore[$class] ) )
    {
        if ( $UsaValore[$class] == 'maggiore' || $UsaValore[$class] == 'minore' )
        {
            $usaValore = $UsaValore[$class];
        }
        else
        {
            throw new Exception( "Valore UsaValore errato: " . $UsaValore[$class] . ". Valori ammessi: maggiore minore" );
        }
    }
    
    $ignora = false;
    if ( isset( $Ignora[$class] ) )
    {
        if ( $Ignora[$class] == 'attributo' || $Ignora[$class] == 'secondi' )
        {
            $ignora = $Ignora[$class];
        }
        else
        {
            throw new Exception( "Valore Ignora errato: " . $Ignora[$class] . ". Valori ammessi: attributo secondi" );
        }
    }
    
    if ( isset( $ScadenzaSecondi[$class] ) )
    {
        $scadeDopoTotSecondi = $ScadenzaSecondi[$class];
    }
    else
    {
        $scadeDopoTotSecondi = $ScadenzaDefault;
    }
    
    $secondiLeggibile = intval( $scadeDopoTotSecondi / 60 / 60 / 24 / 365 );
    
    $unpublishDateAttribute = $DataTime[$class];
    
    $cli->output( "attributo: $unpublishDateAttribute, secondi $scadeDopoTotSecondi ($secondiLeggibile anni)" );
    if ( $usaValore )
    {
        $cli->output( "Usa il valore $usaValore" );
    }
    if ( $ignora )
    {
        $cli->output( "Ignora $ignora" );
    }    
    
	if ( !$unpublishDateAttribute )
    {
        $cli->error( 'Attributo non trovato' );
        continue;
    }
    
    $toSection = 0;
    
    if ( isset( $SectionIDs[$class] ) )
    {
        $toSection = $SectionIDs[$class];
    }
    else
    {
        $toSection = $SectionDefault;
    }
    
    if ( !is_numeric( $toSection ) )
    {                    
        $sectionObject = eZSection::fetchByIdentifier( $toSection, false );
    }
    else
    {
        $sectionObject = eZSection::fetch( $toSection, false );
    }
        
    if ( is_array( $sectionObject ) && !empty( $sectionObject ) )
    {                        
        $toSection = $sectionObject['id'];
    }
    else
    {
        $cli->error( "Section $toSection non trovata" );
        continue;
    }    

	$NodeArray = $rootNode->subTree( array( 'ClassFilterType' => 'include',
                                            'ClassFilterArray' => array( $class ),
                                            'LoadDataMap' => false,
                                            'Limitation' => array(),
                                            'AttributeFilter' => array( array( 'section', '!=', $toSection ) )
                                            )
                                    );
    $count = count( $NodeArray );
    if ( $count == 0 )
    {
        continue;
    }
    $output = new ezcConsoleOutput();
    $progressBarOptions = array( 'emptyChar' => ' ', 'barChar'  => '=' );
    if ( $isQuiet )
    {
        $progressBarOptions['minVerbosity'] = 10;    
    }
    $progressBar = new ezcConsoleProgressbar( $output, intval( $count ), $progressBarOptions );
    $progressBar->start();
    
    foreach ( $NodeArray as $Node )
    {
        $progressBar->advance();
        $Object = $Node->attribute( 'object' );
        $objectID = $Object->attribute( 'id' );

        // Debug su un nodo
        //if ( $Node->attribute( 'node_id' ) != 668195 ){continue;}
        //else
        //{
        //    var_dump( OscuraAttiHandler::isPrivacyClonedObject( $Object ) );
        //    var_dump( $Object->attribute( 'remote_id' ) );
        //    die();
        //    
        //}

        $attributes = $Object->fetchAttributesByIdentifier( array( $unpublishDateAttribute ) );
        $dateAttribute = array_shift( $attributes );

        if ( is_null( $dateAttribute ) )
        {
            $cli->error( 'Attributo non trovato' );
            continue;
        }
        
        $date = $dateAttribute->content();
        $AttributeRetractDate = $date->attribute( 'timestamp' );
        $IniRetractDate = $Object->attribute( 'published' ) + $scadeDopoTotSecondi;

        if ( $AttributeRetractDate > 0 ) 
        {
            // fine giornata
            $ObjectRetractDate = mktime( 23, 59, 59, date("n", $AttributeRetractDate), date("j", $AttributeRetractDate), date("Y", $AttributeRetractDate) );
        }
        else
        {            
            $ObjectRetractDate = $IniRetractDate;
        }        
        
        if ( !$ignora )
        {
            if ( $usaValore && $usaValore == 'maggiore' )
            {
                if ( $AttributeRetractDate > $IniRetractDate )
                {
                    $ObjectRetractDate = $AttributeRetractDate;
                }
                else
                {
                    $ObjectRetractDate = $IniRetractDate;
                }
            }
            
            if ( $usaValore && $usaValore == 'minore' )
            {
                if ( $AttributeRetractDate < $IniRetractDate )
                {
                    $ObjectRetractDate = $AttributeRetractDate;
                }
                else
                {
                    $ObjectRetractDate = $IniRetractDate;
                }
            }
        }
        elseif ( $ignora == 'attributo' )
        {
            $ObjectRetractDate = $IniRetractDate;
        }
        elseif ( $ignora == 'secondi' )
        {
            $ObjectRetractDate = $AttributeRetractDate;
        }
        
        if ( $ObjectRetractDate > 0 &&
             $ObjectRetractDate < $currrentDate &&
             $Node->attribute( 'object' )->attribute( 'section_id' ) !== $toSection &&
             $toSection !== 0 )
        {            
        	$isClone = false;
            if ( class_exists( 'OscuraAttiHandler' ) )
            {
                if ( OscuraAttiHandler::isPrivacyClonedObject( $Object ) )
                {                
                    $clones[$class][] = $Object->attribute( 'main_node_id' );
                    $isClone = true;
                }
                elseif ( $clone = OscuraAttiHandler::hasPrivacyClonedObject( $Object ) )
                {
                    $clones[$class][] = $clone->attribute( 'main_node_id' );
                }
            }
            
            if ( !$isClone )
            {                
                if ( eZOperationHandler::operationIsAvailable( 'content_updatesection' ) )
                {
                    $operationResult = eZOperationHandler::execute( 'content',
                                                                    'updatesection',
                                                                    array( 'node_id'             => $Node->attribute( 'node_id' ),
                                                                           'selected_section_id' => $toSection ),
                                                                    null,
                                                                    true );
            
                }
                else
                {
                    eZContentOperationCollection::updateSection( $Node->attribute( 'node_id' ), $toSection );
                }
                $cli->output( '*' );
                eZContentCacheManager::clearContentCacheIfNeeded( $objectID );
                
                //add index pending            
                eZDB::instance()->query( "INSERT INTO ezpending_actions( action, param ) VALUES ( 'index_object', '$objectID' )" );
            }
            
            eZContentObject::clearCache( $objectID );
            $Object->resetDataMap();
        }
    }
        
    $progressBar->finish();
    $memoryMax = memory_get_peak_usage(); // Result is in bytes
    $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
    $cli->output( ' Memoria usata: '.$memoryMax.'M' );
}

if ( count( $clones ) > 0 )
{
    $cli->output();        
            
    foreach( $clones as $class => $cloneNodesID )
    {                
        foreach( $cloneNodesID as $cloneNodeID )
        {
            eZContentObjectTreeNode::removeSubtrees( array( $cloneNodeID ), true );
            $memoryMax = memory_get_peak_usage(); // Result is in bytes
            $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes        
            $cli->output( "Sposto nel cestino l'oggetto $class clone #$cloneNodeID (" .$memoryMax.'M)' );
        }
    }        
    
} 