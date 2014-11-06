<?php
/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['Identifier'];
$data = array();

try
{
    header( 'HTTP/1.1 200 OK' );
    $data = OpenPADataHandler::runHandler( $identifier, $module );
}
catch ( Exception $e )
{
    header( 'HTTP/1.1 500 Internal Server Error' );
    $data = array( 'error' => $e->getMessage() );
}

header('Content-Type: application/json');
echo json_encode( $data );
eZExecution::cleanExit();