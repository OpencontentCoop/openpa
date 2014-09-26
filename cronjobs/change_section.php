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

$cli = eZCLI::instance();
$cli->setUseStyles( true );
$cli->setIsQuiet( $isQuiet );

$user = eZUser::fetchByName( 'admin' );
if ( $user )
{
    eZUser::setCurrentlyLoggedInUser( $user, $user->attribute( 'contentobject_id' ) );
}
else
{    
    throw new InvalidArgumentException( "Non esiste un utente admin" ); 
}
$loggedUser = eZUser::currentUser();
$cli->output( "Si sta eseguendo l'agente con l'utente " . $loggedUser->attribute( 'contentobject' )->attribute( 'name' )  );

try
{
    $sectionTool = new OpenPASectionTools();
    if ( !$isQuiet )
    {
        $sectionTool->setLog( true );
    }
    $sectionTool->changeAllSubTreeSection();
    if ( !$isQuiet )
    {
        $result = $sectionTool->result();
        foreach( $result as $classIdentifier => $nodeIds )
        {
            if ( !empty( $nodeIds ) )
                $cli->output( "Eseguito cambio sezione per i nodi di classe $classIdentifier: " . implode( ', ', $nodeIds ) );
        }
    }
}
catch ( Exception $e )
{
    $cli->error( $e->getMessage() );
}

