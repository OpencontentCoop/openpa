<?php

// Schedula indicizzazione
$cli->output( 'Schedulo indicizzazione di tutti i contenuti' );

$def = eZContentObject::definition();
$conds = array( 'status' => eZContentObject::STATUS_PUBLISHED );
$count = eZPersistentObject::count( $def, $conds, 'id' );
$length = 50;
$limit = array( 'offset' => 0 , 'length' => $length );
$time = time() - $count;
do
{
    eZContentObject::clearCache();
    $objects = eZPersistentObject::fetchObjectList( $def, null, $conds, null, $limit );
    foreach ( $objects as $object )
    {
        $time++;
        $rowPending = array(
            'action'        => 'index_object',
            'created'       => $time,
            'param'         => $object->attribute( 'id' )
        );

        $pendingItem = new eZPendingActions( $rowPending );
        $pendingItem->store();
    }
    $limit['offset'] += $length;

} while ( count( $objects ) == $length );