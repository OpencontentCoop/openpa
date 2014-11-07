<?php
/** @var eZModule $module */
$module = $Params['Module'];
$identifier = $Params['HandlerIdentifier'];
$data = array();

try
{
    header( 'HTTP/1.1 200 OK' );
    $data = OpenPADataHandler::runHandler( $identifier, $Params );
}
catch ( Exception $e )
{
    header( 'HTTP/1.1 500 Internal Server Error' );
    $data = array( 'error' => $e->getMessage() );
}

header('Content-Type: application/json');
echo json_encode( $data );
eZExecution::cleanExit();