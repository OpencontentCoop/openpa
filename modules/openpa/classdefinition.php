<?php

$module = $Params['Module'];
$id = $Params['ID'];

try
{
    $tools = new OpenPAClassTools( $id );
    $result = $tools->getLocale();        
    
    // carico gli attributi
    $result->attribute( 'data_map' );
    
    // carico i gruppi di appartenenza
    $result->fetchGroupList();
    
    // carico tutti i gruppi
    $result->fetchAllGroups();
}
catch( Exception $e )
{
   $result = array( 'error' => $e->getMessage() ); 
}

header('Content-Type: application/json');
echo json_encode( $result );    
eZExecution::cleanExit();
