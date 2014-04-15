<?php

$module = $Params['Module'];
$tpl = eZTemplate::factory();
$Result = array();
$Result['content'] = $tpl->fetch( 'design:openpa/classlist.tpl' );
$Result['path'] = array( array( 'text' => 'OpenPA Classi' ,
                                'url' => false ) );
